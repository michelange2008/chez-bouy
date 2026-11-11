<?php

namespace App;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class InfomaniakCalendar
{
    public const TIMEZONE = 'Europe/Paris';

    public const COLOR_PATTERN = '/\A#[0-9a-fA-F]{6}\z/';

    public function configured(): bool
    {
        return filled(config('services.infomaniak.calendar_id')) && filled(config('services.infomaniak.token'));
    }

    public function events(CarbonImmutable $month): array
    {
        $events = $this->request('GET', '', ['query' => [
            'calendar_id' => config('services.infomaniak.calendar_id'),
            'from' => $month->startOfMonth()->toDateTimeString(),
            'to' => $month->endOfMonth()->toDateTimeString(),
        ]]);

        return collect($events)->filter(fn (array $event): bool => $this->belongsToCalendar($event))
            ->sortBy('start')->values()->all();
    }

    public function event(string $id): array
    {
        $event = $this->request('GET', '/'.$id);
        abort_unless($this->belongsToCalendar($event), 404);

        return $event;
    }

    private function belongsToCalendar(array $event): bool
    {
        return (string) ($event['calendar_id'] ?? '') === (string) config('services.infomaniak.calendar_id');
    }

    public static function displayDate(array $event, string $field): CarbonImmutable
    {
        if ($event['fullday'] ?? false) {
            $date = CarbonImmutable::parse(substr($event[$field], 0, 10), self::TIMEZONE);

            return $field === 'end' ? $date->subDay() : $date;
        }

        return CarbonImmutable::parse($event[$field], $event['timezone_'.$field] ?? self::TIMEZONE)
            ->setTimezone(self::TIMEZONE);
    }

    /** @param array{title: string, start: string, end?: ?string, color?: ?string, fullday?: bool|int|string} $fields */
    public function save(array $fields, ?string $id = null): void
    {
        $existing = $id === null ? [] : $this->event($id);
        $fullDay = (bool) ($fields['fullday'] ?? false);
        $start = CarbonImmutable::parse($fields['start'], self::TIMEZONE);
        $end = CarbonImmutable::parse($fields['end'] ?? $fields['start'], self::TIMEZONE);
        $payload = Arr::only($existing, ['id', 'description', 'freebusy', 'type', 'private', 'location',
            'color', 'attendees', 'alarms', 'attachments', 'rrule', 'meet_room_url', 'bookable_resource_id']);
        $payload = array_merge([
            'type' => 'event', 'freebusy' => 'busy', 'private' => true,
        ], $payload, [
            'calendar_id' => config('services.infomaniak.calendar_id'),
            'title' => $fields['title'],
            'fullday' => $fullDay,
            'start' => $start->toDateTimeString(),
            'end' => ($fullDay ? $end->addDay() : $end)->toDateTimeString(),
            'timezone_start' => self::TIMEZONE,
            'timezone_end' => self::TIMEZONE,
            'notifyAttendees' => false,
        ]);

        if ($id !== null) {
            $payload['id'] = (int) $id;
            $payload['calendar_id'] = $existing['calendar_id'];
            $payload['description'] ??= '';
            $payload['freebusy'] ??= 'busy';
            $payload['type'] ??= 'event';
            $payload['private'] ??= false;
            $payload['attendees'] ??= [];
            $payload['alarms'] ??= [];
            $payload['attachments'] ??= [];
            $payload['rrule'] ??= '';
            $payload['meet_room_url'] ??= '';
            $payload['parent_updated'] = false;
            $payload['imip_request'] = false;
            if (empty($payload['location'])) {
                unset($payload['location']);
            }
        }

        if (array_key_exists('color', $fields)) {
            $payload['color'] = $fields['color'];
        }

        $this->request($id === null ? 'POST' : 'PUT', $id === null ? '' : '/'.$id, ['json' => $payload]);
    }

    public function delete(string $id): void
    {
        $this->event($id);
        $this->request('DELETE', '/'.$id, ['query' => ['calendar_id' => config('services.infomaniak.calendar_id')]]);
    }

    private function request(string $method, string $path, array $options = []): array
    {
        abort_unless($this->configured(), 503, __('calendar.setup'));

        try {
            $response = Http::withToken(config('services.infomaniak.token'))->acceptJson()
                ->withoutRedirecting()->connectTimeout(3)->timeout(10)
                ->send($method, 'https://api.infomaniak.com/1/calendar/pim/event'.$path, $options);
        } catch (ConnectionException) {
            abort(503, __('calendar.unavailable'));
        }

        abort_if($response->notFound(), 404);
        abort_unless($response->successful() && $response->json('result') !== 'error', 502, __('calendar.unavailable'));
        $data = $response->json('data');
        if ($method === 'GET') {
            abort_unless(is_array($data), 502, __('calendar.unavailable'));

            return $data;
        }

        return [];
    }
}
