<?php

namespace App\Http\Controllers;

use App\InfomaniakCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AvailabilityController extends Controller
{
    private const MORNING = 1;

    private const AFTERNOON = 2;

    private const FULL_DAY = self::MORNING | self::AFTERNOON;

    public function __invoke(Request $request, InfomaniakCalendar $calendar): Response
    {
        $monthInput = $request->query('month', now(InfomaniakCalendar::TIMEZONE)->format('Y-m'));
        abort_if(Validator::make(['month' => $monthInput], ['month' => 'required|date_format:Y-m'])->fails(), 404);

        $month = CarbonImmutable::createFromFormat('!Y-m', $monthInput, InfomaniakCalendar::TIMEZONE)
            ->locale(app()->getLocale());
        $weekdayStart = $month->startOfWeek(CarbonImmutable::MONDAY);

        try {
            $occupancy = $this->occupancy($calendar->events($month), $month);
        } catch (HttpExceptionInterface) {
            return response()->view('availability', compact('month', 'weekdayStart'), 503);
        }

        $days = [];
        $weekdayEnd = $month->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);
        for ($day = $weekdayStart; $day <= $weekdayEnd; $day = $day->addDay()) {
            $days[] = $day->month !== $month->month ? null : [
                'date' => $day,
                'status' => match ($occupancy[$day->day] ?? 0) {
                    self::MORNING => 'departure',
                    self::AFTERNOON => 'arrival',
                    self::FULL_DAY => 'occupied',
                    default => 'available',
                },
            ];
        }

        $weeks = array_chunk($days, 7);

        return response()->view('availability', compact('month', 'weekdayStart', 'weeks'));
    }

    /** @return array<int, int> */
    private function occupancy(array $events, CarbonImmutable $month): array
    {
        $occupancy = [];
        $lastOfMonth = $month->endOfMonth()->startOfDay();

        foreach ($events as $event) {
            $start = InfomaniakCalendar::displayDate($event, 'start');
            $end = InfomaniakCalendar::displayDate($event, 'end');
            if (! ($event['fullday'] ?? false) && $end > $start) {
                $end = $end->subMicrosecond();
            }
            $first = $start->startOfDay();
            $last = $end->startOfDay();

            for ($day = max($month, $first); $day <= min($lastOfMonth, $last); $day = $day->addDay()) {
                $occupied = match (true) {
                    $first->eq($last) => self::FULL_DAY,
                    $day->eq($first) => self::AFTERNOON,
                    $day->eq($last) => self::MORNING,
                    default => self::FULL_DAY,
                };
                $occupancy[$day->day] = ($occupancy[$day->day] ?? 0) | $occupied;
            }
        }

        return $occupancy;
    }
}
