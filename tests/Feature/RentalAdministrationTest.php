<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
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

    public function test_finished_stays_are_only_listed_under_past_stays(): void
    {
        $this->travelTo(Carbon::parse('2027-02-19 00:30', 'Europe/Paris'));
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $finished = Rental::factory()->create([...self::DATES, 'departure' => '2027-02-18']);
        $endsToday = Rental::factory()->create(self::DATES);
        $upcoming = Rental::factory()->create(['arrival' => '2027-02-20', 'departure' => '2027-02-27']);
        $listUrl = route('admin.rentals');
        $pastUrl = route('admin.rentals', ['past' => 1]);
        $editUrl = fn (Rental $rental): string => route('admin.rentals.edit', $rental);

        $this->get($listUrl)->assertOk()->assertSee($pastUrl)
            ->assertSee($editUrl($endsToday))->assertSee($editUrl($upcoming))->assertDontSee($editUrl($finished));
        $this->get($pastUrl)->assertOk()->assertSeeText(__('rentals.past_stays'))
            ->assertSee($editUrl($finished))->assertDontSee($editUrl($endsToday))->assertDontSee($editUrl($upcoming));
    }

    public function test_admin_can_save_remarks_from_the_rental_list(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create(['remarks' => null, 'type' => 'rental']);
        $listUrl = route('admin.rentals');
        $remarksUrl = route('admin.rentals.remarks', $rental);

        $this->get($listUrl)->assertOk()->assertSee($remarksUrl);
        $this->from($listUrl)->patch($remarksUrl, ['remarks' => 'Arrivée <tardive>', 'type' => 'exchange'])
            ->assertValid()->assertRedirect($listUrl.'#rental-remarks-'.$rental->id);

        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'remarks' => 'Arrivée <tardive>', 'type' => 'rental']);
        $this->get($listUrl)->assertOk()->assertSee('Arrivée <tardive>')->assertDontSee('<tardive>', false);
        Http::assertNothingSent();
    }

    public function test_inline_remarks_reject_invalid_input(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create(['remarks' => 'Message initial']);

        $this->from(route('admin.rentals'))->patch(route('admin.rentals.remarks', $rental), ['remarks' => str_repeat('a', 10001)])
            ->assertSessionHasErrorsIn('rentalRemarks'.$rental->id, ['remarks']);

        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'remarks' => 'Message initial']);
    }

    public function test_admin_can_edit_and_clear_payments_inline(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create(['balance_amount' => '500.00']);
        $depositUrl = route('admin.rentals.payment', [$rental, 'deposit']);
        $balanceUrl = route('admin.rentals.payment', [$rental, 'balance']);

        $this->get(route('admin.rentals'))->assertOk()->assertSee($depositUrl)->assertSee($balanceUrl);
        $this->post($depositUrl, ['_method' => 'PATCH', 'deposit_date' => '2027-01-12', 'deposit_amount' => '320.50'], ['Accept' => 'application/json'])
            ->assertOk()->assertJson(['payment' => ['date' => '12/01/2027', 'complete' => true], 'total' => null]);
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id, 'deposit_date' => '2027-01-12 00:00:00', 'deposit_amount' => '320.50', 'balance_amount' => '500.00',
        ]);

        $this->patchJson($balanceUrl, ['balance_date' => '2027-02-10'])
            ->assertOk()->assertJson(['payment' => ['complete' => true], 'total' => 'mismatch']);

        $this->patchJson($balanceUrl, ['balance_amount' => '379.50'])
            ->assertOk()->assertJson(['total' => 'matched']);

        $this->patchJson($balanceUrl, ['balance_date' => '', 'balance_amount' => ''])
            ->assertOk()->assertJson(['payment' => ['date' => '—', 'amount' => '—', 'complete' => false], 'total' => null]);

        $this->patchJson($depositUrl, ['deposit_date' => 'invalid', 'deposit_amount' => '-10'])
            ->assertUnprocessable()->assertJsonValidationErrors(['deposit_date', 'deposit_amount']);
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id, 'deposit_date' => '2027-01-12 00:00:00', 'deposit_amount' => '320.50', 'balance_amount' => null,
        ]);

        $exchange = Rental::factory()->create(['type' => 'exchange', 'amount' => null]);
        $this->patchJson(route('admin.rentals.payment', [$exchange, 'deposit']), ['deposit_amount' => '20'])
            ->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_admin_can_change_a_checklist_value_from_the_rental_list(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create(['guide_sent' => true]);
        $url = route('admin.rentals.checklist', [$rental, 'contract_sent']);

        $this->get(route('admin.rentals'))->assertOk()->assertSee($url);
        $this->post($url, ['_method' => 'PATCH', 'value' => '1'], ['Accept' => 'application/json'])
            ->assertOk()->assertJson(['value' => true, 'label' => __('users.yes')]);
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'contract_sent' => true, 'guide_sent' => true]);

        $this->patchJson($url, ['value' => false])
            ->assertOk()->assertJson(['value' => false, 'label' => __('users.no')]);
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'contract_sent' => false]);
        $this->patchJson(route('admin.rentals.checklist', [$rental, 'concierge_notified']), ['value' => true])
            ->assertOk()->assertJson(['value' => true]);
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'concierge_notified' => true, 'mail_envoye' => false]);
        Http::assertNothingSent();
    }

    public function test_checklist_updates_require_an_admin_and_a_visible_boolean_field(): void
    {
        $rental = Rental::factory()->create(['type' => 'exchange', 'amount' => null]);
        $url = route('admin.rentals.checklist', [$rental, 'guide_sent']);

        $this->actingAs(User::factory()->create(['role' => 'locataire']))
            ->patchJson($url, ['value' => true])->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->patchJson($url, ['value' => 'invalid'])->assertUnprocessable();
        $this->patchJson(route('admin.rentals.checklist', [$rental, 'amount']), ['value' => true])->assertNotFound();
        $list = $this->get(route('admin.rentals'))->assertOk()->assertSee($url);
        foreach (['contract_sent', 'contract_signed'] as $field) {
            $contractUrl = route('admin.rentals.checklist', [$rental, $field]);
            $list->assertDontSee($contractUrl);
            $this->patchJson($contractUrl, ['value' => true])->assertNotFound();
        }
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id, 'guide_sent' => false, 'contract_sent' => false, 'contract_signed' => false,
        ]);
    }

    public function test_admin_can_send_the_access_email_to_the_linked_guest(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create();
        $listUrl = route('admin.rentals');
        $sendUrl = route('admin.rentals.send-access-email', $rental);
        config(['mail.default' => 'array']);
        Event::fake([MessageSent::class]);

        $this->get($listUrl)->assertOk()->assertSee($sendUrl);
        Event::assertNotDispatched(MessageSent::class);

        $this->from($listUrl)->post($sendUrl, ['email' => 'other@example.com'])
            ->assertRedirect($listUrl)->assertSessionHas('status', __('rentals.access_email_sent'));
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'mail_envoye' => true]);

        Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === $rental->user->email
            && $event->message->getSubject() === 'Chez Bouy - accéder à votre espace'
            && str_contains($event->message->getHtmlBody(), '<strong>espace privé</strong>')
            && str_contains($event->message->getHtmlBody(), 'https://chezbouy.cumulus.org'));
    }

    public function test_access_email_requires_a_linked_guest_and_an_admin(): void
    {
        $rental = Rental::factory()->create(['user_id' => null, 'guest_name' => 'Ancien locataire']);
        $sendUrl = route('admin.rentals.send-access-email', $rental);
        config(['mail.default' => 'array']);
        Event::fake([MessageSent::class]);

        $this->actingAs(User::factory()->create(['role' => 'locataire']))
            ->post($sendUrl)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('admin.rentals'))->post($sendUrl)->assertSessionHasErrors('access_email');

        Event::assertNotDispatched(MessageSent::class);
    }

    public function test_access_email_uses_the_guest_profile_language(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $guest = User::factory()->create(['locale' => 'it']);
        $rental = Rental::factory()->for($guest, 'user')->create();
        config(['mail.default' => 'array']);
        Event::fake([MessageSent::class]);

        $this->from(route('admin.rentals'))->post(route('admin.rentals.send-access-email', $rental))
            ->assertRedirect(route('admin.rentals'))->assertSessionHas('status', 'Courriel d’accès envoyé.');

        Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getSubject() === 'Chez Bouy - accedete alla vostra area riservata'
            && str_contains($event->message->getHtmlBody(), '<html lang="it">')
            && str_contains($event->message->getHtmlBody(), '<strong>area riservata</strong>'));
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
        $this->get(route('admin.rentals.create'))->assertOk()->assertSee($guest->email)
            ->assertSee('name="type"', false)->assertSee('name="amount"', false)
            ->assertDontSee('name="deposit_date"', false)->assertDontSee('name="remarks"', false);
        $this->post(route('admin.rentals.store'), [
            ...self::DATES, 'type' => 'rental', 'user_id' => $guest->id,
            'amount' => '820.50', 'deposit_amount' => '320.50', 'contract_signed' => 1, 'remarks' => 'Texte ignoré',
        ])
            ->assertValid()->assertRedirect(route('admin.rentals'));

        $rental = Rental::sole();
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id, 'user_id' => $guest->id, 'calendar_event_id' => 123,
            'amount' => '820.50', 'deposit_date' => null, 'deposit_amount' => null, 'balance_date' => null, 'balance_amount' => null,
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
            ->assertSee('name="arrival"', false)->assertSee('name="departure"', false)
            ->assertDontSee('name="user_id"', false)->assertSee('name="type"', false)
            ->assertSee('name="amount"', false)->assertDontSee('name="deposit_date"', false)
            ->assertDontSee('name="contract_signed"', false)->assertDontSee('name="remarks"', false);

        $rental->update([
            'deposit_amount' => '320.50', 'contract_signed' => true,
            'remarks' => 'Arrivée le soir',
        ]);

        $count = Http::recorded()->count();
        $this->patch(route('admin.rentals.update', $rental), [...self::DATES, 'type' => 'rental', 'amount' => '900.00'])
            ->assertValid()->assertRedirect(route('admin.rentals'));
        Http::assertSentCount($count);
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'type' => 'rental', 'amount' => '900.00']);

        $this->patch(route('admin.rentals.update', $rental), [
            ...self::DATES, 'departure' => '2027-02-20', 'type' => 'exchange', 'amount' => '1',
            'deposit_amount' => '1', 'contract_signed' => 0, 'remarks' => 'Texte ignoré',
        ])->assertValid()->assertRedirect(route('admin.rentals'));
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id, 'calendar_event_id' => 123, 'departure' => '2027-02-20 00:00:00',
            'type' => 'exchange', 'amount' => null, 'deposit_amount' => '320.50',
            'contract_signed' => true, 'remarks' => 'Arrivée le soir',
        ]);
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

    public function test_creating_a_stay_warns_when_dates_overlap_and_keeps_the_entered_dates(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $existingStay = Rental::factory()->create(self::DATES);
        $createUrl = route('admin.rentals.create');

        $this->followingRedirects()->from($createUrl)->post(route('admin.rentals.store'), [
            'arrival' => '2027-02-18', 'departure' => '2027-02-21', 'type' => 'family', 'user_id' => $existingStay->user_id,
        ])->assertOk()
            ->assertSeeText(__('rentals.overlapping_stay', ['arrival' => '12/02/2027', 'departure' => '19/02/2027']))
            ->assertSee('value="2027-02-18"', false)
            ->assertSee('value="2027-02-21"', false);
        $this->assertDatabaseCount('rentals', 1);
        Http::assertNothingSent();
    }

    public function test_creating_a_stay_allows_arrival_on_the_previous_stay_departure_date(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $existingStay = Rental::factory()->create(self::DATES);
        Http::fake([self::API => Http::response(['data' => ['id' => 123]], 201)]);

        $this->post(route('admin.rentals.store'), [
            'arrival' => self::DATES['departure'], 'departure' => '2027-02-21', 'type' => 'family', 'user_id' => $existingStay->user_id,
        ])->assertValid()->assertRedirect(route('admin.rentals'));

        $this->assertDatabaseCount('rentals', 2);
    }

    public function test_create_form_only_lists_active_users(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $activeGuest = User::factory()->create(['is_active' => true]);
        $inactiveGuest = User::factory()->create(['is_active' => false]);

        $this->get(route('admin.rentals.create'))->assertOk()
            ->assertSeeText($activeGuest->email)
            ->assertDontSeeText($inactiveGuest->email);
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
            ...self::DATES, 'type' => 'family', 'user_id' => 'new', 'new_email' => $other->email,
        ])->assertInvalid(['new_email', 'new_name']);
        $this->patch(route('admin.rentals.update', $rental), [
            ...self::DATES, 'departure' => '2027-02-11', 'user_id' => $other->id,
        ])->assertInvalid(['departure', 'user_id']);

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
            ...self::DATES, 'departure' => '2027-02-20', 'type' => 'rental', 'amount' => '820.50',
        ])->assertInvalid('calendar');
        $this->delete(route('admin.rentals.destroy', $rental))->assertInvalid('calendar');
        $this->assertDatabaseHas('rentals', ['id' => $rental->id, 'departure' => '2027-02-19 00:00:00', 'type' => 'rental', 'contract_signed' => false]);
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
            ['PATCH', route('admin.rentals.remarks', $rental)],
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
