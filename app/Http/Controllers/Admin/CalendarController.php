<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCalendarEventRequest;
use App\InfomaniakCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    private const MONTHS_PER_PAGE = 6;

    public function __construct(private InfomaniakCalendar $calendar) {}

    public function index(Request $request): Response
    {
        $month = $request->validate(['month' => ['sometimes', 'date_format:Y-m']])['month']
            ?? now(InfomaniakCalendar::TIMEZONE)->format('Y-m');
        $month = CarbonImmutable::createFromFormat('!Y-m', $month, InfomaniakCalendar::TIMEZONE);
        $configured = $this->calendar->configured();
        $nextPage = $month->addMonths(self::MONTHS_PER_PAGE);
        $previousPage = $month->subMonths(self::MONTHS_PER_PAGE);
        $months = [];
        for ($cursor = $month; $cursor < $nextPage; $cursor = $cursor->addMonth()) {
            $events = [];
            if ($configured) {
                foreach ($this->calendar->events($cursor) as $event) {
                    $start = InfomaniakCalendar::displayDate($event, 'start')->locale(app()->getLocale());
                    $end = InfomaniakCalendar::displayDate($event, 'end')->locale(app()->getLocale());
                    $lastDay = ! ($event['fullday'] ?? false) && $end > $start ? $end->subMicrosecond() : $end;
                    if ($start < $cursor->addMonth() && $lastDay >= $cursor) {
                        $events[] = [...$event, 'start' => $start, 'end' => $end];
                    }
                }
            }
            $months[$cursor->format('Y-m')] = ['date' => $cursor->locale(app()->getLocale()), 'events' => $events];
        }

        return response()->view('admin.calendar', compact('months', 'previousPage', 'nextPage', 'configured'))->header('Cache-Control', 'private, no-store');
    }

    public function create(): Response
    {
        abort_unless($this->calendar->configured(), 503, __('calendar.setup'));

        return $this->form([]);
    }

    public function edit(string $event): Response
    {
        return $this->form($this->calendar->event($event));
    }

    private function form(array $event): Response
    {
        foreach (['start', 'end'] as $field) {
            if (isset($event[$field])) {
                $event[$field] = InfomaniakCalendar::displayDate($event, $field)
                    ->format(($event['fullday'] ?? false) ? 'Y-m-d' : 'Y-m-d\TH:i');
            }
        }

        return response()->view('admin.calendar.form', compact('event'))->header('Cache-Control', 'private, no-store');
    }

    public function store(SaveCalendarEventRequest $request): RedirectResponse
    {
        $this->calendar->save($request->validated());

        return $this->saved();
    }

    public function update(SaveCalendarEventRequest $request, string $event): RedirectResponse
    {
        $this->calendar->save($request->validated(), $event);

        return $this->saved();
    }

    private function saved(): RedirectResponse
    {
        return redirect()->route('admin.calendar')
            ->with('status', __('calendar.saved'));
    }

    public function destroy(string $event): RedirectResponse
    {
        $this->calendar->delete($event);

        return redirect()->route('admin.calendar')->with('status', __('calendar.deleted'));
    }
}
