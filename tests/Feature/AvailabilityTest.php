<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api.infomaniak.com/1/calendar/pim/event?*';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Http::preventStrayRequests();
        config(['services.infomaniak.calendar_id' => '17', 'services.infomaniak.token' => 'test-token']);
    }

    public function test_public_home_links_to_availability(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee(route('availability'))
            ->assertSeeText('Vérifier les disponibilités');

        Http::assertNothingSent();
    }

    public function test_public_calendar_displays_boundaries_and_overlaps_without_event_details(): void
    {
        $event = static fn (string $start, string $end, int $calendarId = 17): array => [
            'start' => $start, 'end' => $end, 'fullday' => true, 'calendar_id' => $calendarId,
            'title' => 'Private guest name', 'description' => 'Private notes',
        ];
        Http::fake([self::API => Http::response(['data' => [
            $event('2026-10-30', '2026-11-03'),
            $event('2026-11-04', '2026-11-07'),
            $event('2026-11-06', '2026-11-09'),
            $event('2026-11-10', '2026-11-11'),
            $event('2026-11-11', '2026-11-15', 99),
            $event('2026-11-29', '2026-12-04'),
        ]])]);

        $response = $this->get(route('availability', ['month' => '2026-11']))
            ->assertOk()
            ->assertDontSee(route('availability', ['month' => '2026-10']))
            ->assertSee(route('availability', ['month' => '2026-12']))
            ->assertDontSee('Private guest name')
            ->assertDontSee('Private notes');

        foreach ([1 => 'occupied', 2 => 'departure', 3 => 'available', 4 => 'arrival', 5 => 'occupied',
            6 => 'occupied', 8 => 'departure', 10 => 'occupied', 11 => 'available', 30 => 'occupied'] as $day => $status) {
            $this->assertMatchesRegularExpression(
                '/datetime="2026-11-'.sprintf('%02d', $day).'"[^>]*data-availability="'.$status.'"/',
                $response->getContent(),
            );
        }
        Http::assertSentCount(1);
    }

    public function test_calendar_failure_does_not_display_false_availability(): void
    {
        Http::fake([self::API => Http::response([], 500)]);

        $this->get(route('availability'))->assertServiceUnavailable()
            ->assertSeeText(__('availability.unavailable'))
            ->assertDontSee('data-availability=');
    }

    public function test_invalid_month_is_rejected_without_calling_infomaniak(): void
    {
        $this->get(route('availability', ['month' => '2026-13']))->assertNotFound();

        Http::assertNothingSent();
    }
}
