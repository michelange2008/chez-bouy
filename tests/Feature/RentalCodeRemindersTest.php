<?php

namespace Tests\Feature;

use App\Console\Commands\SendRentalCodeReminders;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RentalCodeRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2027-02-05 00:30', 'Europe/Paris'));
        $this->withoutVite();
    }

    #[TestWith(['2027-02-05', true])]
    #[TestWith(['2027-02-12', true])]
    #[TestWith(['2027-02-13', false])]
    #[TestWith(['2027-02-04', false])]
    public function test_reminder_appears_only_in_the_week_before_arrival(string $arrival, bool $visible): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Rental::factory()->create(['arrival' => $arrival, 'departure' => '2027-02-19']);

        $response = $this->get(route('admin.rentals'))->assertOk();

        if ($visible) {
            $response->assertSeeText(__('rentals.codes_to_send'));
        } else {
            $response->assertDontSeeText(__('rentals.codes_to_send'));
        }
    }

    public function test_reminder_is_hidden_when_codes_have_been_sent(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $rental = Rental::factory()->create(['arrival' => '2027-02-12', 'departure' => '2027-02-19']);

        $this->patchJson(route('admin.rentals.checklist', [$rental, 'codes_sent']), ['value' => true])
            ->assertOk()->assertJson(['value' => true]);

        $response = $this->get(route('admin.rentals'))->assertOk();

        $this->assertMatchesRegularExpression('/data-code-reminder\s+hidden\b/', $response->getContent());
    }

    public function test_email_is_sent_once_to_active_admins_for_stays_at_seven_days_with_codes_pending(): void
    {
        config(['mail.default' => 'array']);
        Event::fake([MessageSent::class]);
        $admin = User::factory()->create(['role' => 'admin', 'locale' => 'fr']);
        User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $arrival = ['arrival' => '2027-02-12', 'departure' => '2027-02-19'];
        $due = Rental::factory()->create($arrival);
        Rental::factory()->create([...$arrival, 'codes_sent' => true]);
        Rental::factory()->create([...$arrival, 'type' => 'family']);
        Rental::factory()->create([...$arrival, 'arrival' => '2027-02-11']);
        Rental::factory()->create([...$arrival, 'arrival' => '2027-02-13']);

        $this->artisan('rentals:remind-codes')->assertSuccessful();
        $this->artisan('rentals:remind-codes')->assertSuccessful();

        Event::assertDispatchedTimes(MessageSent::class, 1);
        Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === $admin->email
            && str_contains($event->message->getTextBody(), $due->guestName())
            && str_contains($event->message->getTextBody(), '12/02/2027')
            && str_contains($event->message->getTextBody(), route('admin.rentals')));
        $this->assertDatabaseHas('rentals', ['id' => $due->id, 'codes_sent' => false]);
    }

    public function test_webcron_is_disabled_without_a_configured_password(): void
    {
        config(['services.infomaniak.webcron_password' => null]);
        Artisan::shouldReceive('call')->never();

        $this->get(route('webcron.rental-code-reminders'))->assertNotFound();
    }

    public function test_webcron_rejects_missing_and_incorrect_credentials(): void
    {
        config(['services.infomaniak.webcron_password' => 'test-password']);
        Artisan::shouldReceive('call')->never();
        $url = route('webcron.rental-code-reminders');

        $this->get($url)->assertUnauthorized()->assertHeader('WWW-Authenticate', 'Basic realm="Chez Bouy Webcron"');
        $this->withBasicAuth('webcron', 'incorrect')->get($url)->assertUnauthorized();
    }

    #[TestWith([Command::SUCCESS, 204])]
    #[TestWith([Command::FAILURE, 500])]
    public function test_webcron_runs_only_the_reminder_command_and_reports_its_result(int $exitCode, int $status): void
    {
        config(['services.infomaniak.webcron_password' => 'test-password']);
        Artisan::shouldReceive('call')->once()->with(SendRentalCodeReminders::class)->andReturn($exitCode);

        $this->withBasicAuth('webcron', 'test-password')
            ->get(route('webcron.rental-code-reminders'))->assertStatus($status);
    }
}
