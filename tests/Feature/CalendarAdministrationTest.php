<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CalendarAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api.infomaniak.com/1/calendar/pim/event';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Http::preventStrayRequests();
        config(['services.infomaniak.calendar_id' => '17', 'services.infomaniak.token' => 'private-test-token']);
    }

    public function test_calendar_routes_require_an_active_admin(): void
    {
        $requests = [
            ['GET', '/admin/calendrier'], ['GET', '/admin/calendrier/ajouter'],
            ['POST', '/admin/calendrier'], ['GET', '/admin/calendrier/9/modifier'],
            ['PATCH', '/admin/calendrier/9'], ['DELETE', '/admin/calendrier/9'],
        ];
        foreach ($requests as [$method, $path]) {
            $this->call($method, $path)->assertRedirect(route('login'));
        }

        foreach ([['locataire', true], ['admin', false]] as [$role, $active]) {
            $this->actingAs(User::factory()->create(['role' => $role, 'is_active' => $active]));
            foreach ($requests as [$method, $path]) {
                $this->call($method, $path)->assertForbidden();
            }
        }
        Http::assertNothingSent();
    }

    public function test_admin_can_list_create_edit_and_delete_in_the_configured_calendar(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['id' => 9, 'calendar_id' => 17, 'title' => 'Séjour', 'start' => '2026-11-11T12:00:00+01:00',
            'end' => '2026-11-12T12:00:00+01:00', 'description' => 'Existing details', 'color' => '#219abc'];
        Http::fake([
            self::API.'?*' => Http::response(['data' => [$data]]),
            self::API.'/9*' => Http::response(['data' => $data]),
            self::API => Http::response(['data' => $data], 201),
        ]);

        $this->get('/admin/calendrier?month=2026-11')->assertOk()->assertSee('Séjour')->assertSee('background-color: #219abc')->assertDontSee('private-test-token');
        $this->get('/admin/calendrier/ajouter')->assertOk()
            ->assertSee('name="fullday" value="1" checked', false)->assertSee('type="date"', false);
        $fields = ['title' => 'Arrivée', 'start' => '2026-11-21T18:00', 'end' => '2026-11-21T19:00', 'color' => '#943ab2'];
        $this->post('/admin/calendrier', $fields)->assertRedirect(route('admin.calendar'));
        Http::assertSent(fn (Request $r): bool => $r->method() === 'POST' && $r['color'] === '#943ab2' && $r['calendar_id'] === '17' && $r['title'] === 'Arrivée' && $r['start'] === '2026-11-21 18:00:00');
        $this->get('/admin/calendrier/9/modifier')->assertOk()->assertDontSee('name="fullday" value="1" checked', false)->assertSee('2026-11-11T12:00')->assertSee('value="#219abc"', false);
        $this->patch('/admin/calendrier/9', $fields)->assertRedirect(route('admin.calendar'));
        Http::assertSent(fn (Request $r): bool => $r->method() === 'PUT' && $r['color'] === '#943ab2' && $r['description'] === 'Existing details' && $r['title'] === 'Arrivée'
            && $r['id'] === 9 && $r['calendar_id'] === 17 && $r['parent_updated'] === false && $r['imip_request'] === false
            && $r['attendees'] === [] && $r['alarms'] === [] && $r['attachments'] === [] && $r['rrule'] === '');
        $this->delete('/admin/calendrier/9')->assertRedirect();
        Http::assertSent(fn (Request $r): bool => $r->method() === 'DELETE' && $r->url() === self::API.'/9?calendar_id=17');
    }

    public function test_six_consecutive_months_include_empty_months_and_overlapping_stays(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $stay = ['id' => 7, 'calendar_id' => 17, 'title' => 'Séjour à cheval', 'fullday' => true,
            'start' => '2026-10-30', 'end' => '2026-12-02'];
        $timed = ['id' => 8, 'calendar_id' => 17, 'title' => 'Départ à minuit',
            'start' => '2026-12-31T20:00:00+01:00', 'end' => '2027-01-01T00:00:00+01:00'];
        Http::fake([self::API.'?*' => Http::response(['data' => [$stay, $timed]])]);

        $this->travelTo(now('UTC')->setDate(2026, 10, 31)->setTime(23, 30));
        $page = $this->get(route('admin.calendar'))->assertOk();
        $page->assertSeeTextInOrder(['Novembre 2026', 'Séjour à cheval', 'Décembre 2026', 'Séjour à cheval',
            'Départ à minuit', 'Janvier 2027', __('calendar.empty'), 'Février 2027', 'Mars 2027', 'Avril 2027'])
            ->assertDontSeeText('Mai 2027')
            ->assertSee(route('admin.calendar', ['month' => '2026-05']))
            ->assertSee(route('admin.calendar', ['month' => '2027-05']));
        $this->assertSame(1, substr_count(strip_tags($page->getContent()), 'Départ à minuit'));
        Http::assertSentCount(6);
        foreach (['2026-11', '2026-12', '2027-01', '2027-02', '2027-03', '2027-04'] as $month) {
            $start = CarbonImmutable::parse($month.'-01');
            Http::assertSent(fn (Request $request): bool => $request['from'] === $start->toDateTimeString()
                && $request['to'] === $start->endOfMonth()->toDateTimeString());
        }
    }

    public function test_clearing_the_color_restores_the_calendar_default(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Http::fake([self::API.'/9' => Http::response(['data' => ['calendar_id' => 17, 'color' => '#112233']])]);

        $this->patch('/admin/calendrier/9', ['title' => 'Séjour', 'fullday' => '1', 'start' => '2026-11-21', 'color' => ''])
            ->assertRedirect();
        Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
            && array_key_exists('color', $request->data()) && $request['color'] === null);
    }

    public function test_invalid_colors_are_rejected_before_contacting_infomaniak(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/calendrier', ['title' => 'Séjour', 'fullday' => '1', 'start' => '2026-11-21', 'color' => 'red;'])
            ->assertInvalid('color');
        Http::assertNothingSent();
    }

    public function test_an_admin_can_book_a_single_day_without_an_end_or_time(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Http::fake([self::API => Http::response([], 201)]);

        $this->post('/admin/calendrier', ['title' => 'Séjour', 'fullday' => '1', 'start' => '2027-03-28'])
            ->assertRedirect(route('admin.calendar'));

        Http::assertSent(fn (Request $request): bool => $request['fullday'] === true
            && $request['start'] === '2027-03-28 00:00:00' && $request['end'] === '2027-03-29 00:00:00');
    }

    public function test_an_all_day_stay_displays_and_saves_the_inclusive_last_day(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $event = ['id' => 9, 'calendar_id' => 17, 'title' => 'Séjour', 'fullday' => true,
            'start' => '2026-12-30', 'end' => '2027-01-02'];
        Http::fake([
            self::API.'?*' => Http::response(['data' => [$event]]),
            self::API.'/9' => Http::response(['data' => $event]),
        ]);

        $this->get('/admin/calendrier?month=2026-12')->assertOk()->assertSee('01/01/2027')->assertDontSee('02/01/2027');
        $this->get('/admin/calendrier/9/modifier')->assertOk()->assertSee('type="date"', false)->assertSee('value="2027-01-01"', false);
        $this->patch('/admin/calendrier/9', ['title' => 'Séjour', 'fullday' => '1', 'start' => '2026-12-30', 'end' => '2027-01-03'])
            ->assertRedirect();
        Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
            && $request['fullday'] === true && $request['end'] === '2027-01-04 00:00:00');

        $this->patch('/admin/calendrier/9', ['title' => 'Séjour', 'fullday' => '0', 'start' => '2026-12-30T10:00', 'end' => '2026-12-30T11:00'])
            ->assertRedirect();
        Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
            && $request['fullday'] === false && $request['end'] === '2026-12-30 11:00:00');
    }

    public function test_an_all_day_stay_cannot_end_before_it_starts(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/calendrier', ['title' => 'Séjour', 'fullday' => '1', 'start' => '2027-01-02', 'end' => '2027-01-01'])
            ->assertInvalid('end');
        Http::assertNothingSent();
    }

    public function test_an_event_from_another_calendar_cannot_be_deleted_or_edited(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Http::fake([self::API.'/9' => Http::response(['data' => ['calendar_id' => 42]])]);
        $this->delete('/admin/calendrier/9')->assertNotFound();
        $this->patch('/admin/calendrier/9', ['title' => 'Test', 'start' => '2026-11-01T09:00', 'end' => '2026-11-01T10:00'])->assertNotFound();
        Http::assertNotSent(fn (Request $r): bool => $r->method() !== 'GET');
    }

    public function test_invalid_dates_are_rejected_before_contacting_infomaniak(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/calendrier', ['title' => 'Test', 'start' => '2026-11-01T10:00', 'end' => '2026-11-01T09:00'])
            ->assertInvalid('end');
        Http::assertNothingSent();
    }

    public function test_missing_configuration_and_provider_failure_are_visible(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Http::fake([self::API.'?*' => Http::response([], 401)]);
        $this->get('/admin/calendrier')->assertStatus(502);
        config(['services.infomaniak.token' => null]);
        $this->get('/admin/calendrier')->assertOk()->assertSee(__('calendar.setup'));
    }
}
