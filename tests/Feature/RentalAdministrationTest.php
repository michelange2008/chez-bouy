<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RentalAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api.infomaniak.com/1/calendar/pim/event';

    private const DATES = ['arrival' => '2027-02-12', 'departure' => '2027-02-19'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Http::preventStrayRequests();
        config(['services.infomaniak.calendar_id' => '17', 'services.infomaniak.token' => 'test-token']);
    }

    public function test_admin_manages_a_paid_stay_and_its_linked_calendar_event(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $guest = User::factory()->create(['name' => 'Robert', 'first_name' => 'Claude']);
        $event = ['id' => 123, 'calendar_id' => 17, 'title' => 'Location · Claude Robert',
            'fullday' => true, 'start' => '2027-02-12', 'end' => '2027-02-20'];
        Http::fake([
            self::API.'?*' => Http::response(['data' => [$event]]),
            self::API.'/123*' => Http::response(['data' => $event]),
            self::API => Http::response(['data' => $event], 201),
        ]);

        $this->get(route('admin'))->assertOk()->assertSee(route('admin.rentals'));
        $this->get(route('admin.rentals.create'))->assertOk()->assertSee($guest->email);
        $fields = [...self::DATES, 'type' => 'rental', 'amount' => '820.50'];
        $this->post(route('admin.rentals.store'), [...$fields, 'user_id' => $guest->id])
            ->assertValid()->assertRedirect(route('admin.rentals'));

        $rental = Rental::sole();
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id, 'user_id' => $guest->id, 'calendar_event_id' => 123,
            'deposit_date' => null, 'deposit_amount' => null, 'balance_date' => null, 'balance_amount' => null,
            'remarks' => null, ...array_fill_keys(Rental::CHECKLIST, false),
        ]);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request['title'] === 'Location · Claude Robert' && $request['calendar_id'] === '17'
            && $request['color'] === '#d40000' && $request['fullday'] === true && $request['private'] === true
            && $request['start'] === '2027-02-12 00:00:00' && $request['end'] === '2027-02-20 00:00:00');

        $this->get(route('admin.rentals'))->assertOk()
            ->assertSeeText('Claude Robert')->assertSee(route('admin.users.edit', $guest))
            ->assertSee(route('admin.calendar', ['month' => '2027-02']).'#calendar-event-123-2027-02');
        $this->get(route('admin.calendar', ['month' => '2027-02']))->assertOk()
            ->assertSee('id="calendar-event-123-2027-02"', false)->assertSee(route('admin.rentals.edit', $rental));
        $this->get(route('admin.rentals.edit', $rental))->assertOk()
            ->assertSeeText('Claude Robert')->assertDontSee('name="user_id"', false);

        $count = Http::recorded()->count();
        $this->patch(route('admin.rentals.update', $rental), [
            ...$fields, 'deposit_date' => '2027-01-12', 'deposit_amount' => '320.50',
            'balance_date' => '2027-02-12', 'balance_amount' => '500.00',
            'contract_signed' => 1, 'brings_linen' => 1, 'remarks' => 'Arrivée le soir',
        ])->assertValid()->assertRedirect(route('admin.rentals'));

        Http::assertSentCount($count);
        $this->get(route('admin.rentals'))->assertOk()->assertSeeText('Arrivée le soir')->assertSeeText('12/01/2027');
        $this->patch(route('admin.rentals.update', $rental), [
            ...$fields, 'departure' => '2027-02-20', 'type' => 'exchange',
        ])->assertValid()->assertRedirect(route('admin.rentals'));
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'calendar_event_id' => 123, 'type' => 'exchange', 'amount' => null]);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT' && $request['id'] === 123
            && $request['color'] === '#aa00d4' && $request['title'] === 'Échange · Claude Robert' && $request['end'] === '2027-02-21 00:00:00');

        $this->delete(route('admin.calendar.destroy', 123))->assertRedirect(route('admin.rentals'));
        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'DELETE');
        $this->delete(route('admin.rentals.destroy', $rental))->assertRedirect(route('admin.rentals'));
        Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE'
            && $request->url() === self::API.'/123?calendar_id=17');
        $this->assertModelMissing($rental);
        $this->assertModelExists($guest);
    }

    public function test_admin_can_add_a_user_inline_for_a_free_family_stay(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Http::fake([self::API => Http::response(['data' => ['id' => 123]], 201)]);
        $this->post(route('admin.rentals.store'), [
            ...self::DATES, 'type' => 'family', 'user_id' => 'new',
            'new_name' => 'Robert', 'new_first_name' => 'Claude', 'new_email' => 'Claude@Example.com',
        ])->assertValid()->assertRedirect(route('admin.rentals'));

        $rental = Rental::sole();
        $this->assertSame('claude@example.com', $rental->user->email);
        $this->assertSame('locataire', $rental->user->role);
        $this->assertNull($rental->amount);
        $this->assertSame('family', $rental->type);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request['color'] === '#55d400');
    }

    public function test_invalid_fields_and_guest_changes_do_not_reach_the_calendar(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create(['calendar_event_id' => 123]);
        $other = User::factory()->create();

        $this->post(route('admin.rentals.store'), [
            ...self::DATES, 'departure' => '2027-02-11', 'type' => 'rental', 'user_id' => $other->id,
        ])->assertInvalid(['departure', 'amount']);
        $this->post(route('admin.rentals.store'), [
            ...self::DATES, 'type' => 'rental', 'user_id' => $other->id, 'amount' => '0',
        ])->assertInvalid('amount');
        $this->post(route('admin.rentals.store'), [
            ...self::DATES, 'type' => 'family', 'user_id' => 'new', 'new_email' => $other->email,
        ])->assertInvalid(['new_email', 'new_name']);
        $this->patch(route('admin.rentals.update', $rental), [
            ...self::DATES, 'type' => 'family', 'user_id' => $other->id,
        ])->assertInvalid('user_id');

        $this->assertTrue($rental->fresh()->user->is($rental->user));
        Http::assertNothingSent();
    }

    public function test_calendar_failure_rolls_back_the_stay_and_any_new_user(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Http::fake([self::API.'*' => Http::response([], 503)]);
        $this->post(route('admin.rentals.store'), [
            ...self::DATES, 'type' => 'family', 'user_id' => 'new', 'new_name' => 'Robert', 'new_email' => 'claude@example.com',
        ])->assertInvalid('calendar');
        $this->assertDatabaseCount('rentals', 0);
        $this->assertDatabaseMissing('users', ['email' => 'claude@example.com']);

        $rental = Rental::factory()->create(['calendar_event_id' => 123, ...self::DATES]);
        $this->patch(route('admin.rentals.update', $rental), [
            ...self::DATES, 'type' => 'family', 'contract_signed' => 1,
        ])->assertInvalid('calendar');
        $this->delete(route('admin.rentals.destroy', $rental))->assertInvalid('calendar');
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'type' => 'rental', 'contract_signed' => false]);
    }

    public function test_a_stay_remains_manageable_after_the_user_and_remote_event_are_removed(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $guest = User::factory()->create(['name' => 'Robert', 'first_name' => 'Claude']);
        $rental = Rental::factory()->for($guest)->create(['calendar_event_id' => 123]);
        $guest->delete();
        Http::fake([self::API.'/123' => Http::response([], 404)]);

        $this->get(route('admin.rentals'))->assertOk()->assertSeeText('Claude Robert')
            ->assertDontSee(route('admin.users.edit', $guest));
        $this->delete(route('admin.rentals.destroy', $rental))->assertValid()->assertRedirect(route('admin.rentals'));
        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'DELETE');
        $this->assertModelMissing($rental);
    }

    public function test_rental_routes_require_admin_access(): void
    {
        $rental = Rental::factory()->create();
        $routes = [
            ['GET', route('admin.rentals')], ['GET', route('admin.rentals.create')],
            ['POST', route('admin.rentals.store')], ['GET', route('admin.rentals.edit', $rental)],
            ['PATCH', route('admin.rentals.update', $rental)], ['DELETE', route('admin.rentals.destroy', $rental)],
        ];
        foreach ($routes as [$method, $url]) {
            $this->call($method, $url)->assertRedirect(route('login'));
        }

        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach ($routes as [$method, $url]) {
            $this->call($method, $url)->assertForbidden();
        }
        Http::assertNothingSent();
    }
}
