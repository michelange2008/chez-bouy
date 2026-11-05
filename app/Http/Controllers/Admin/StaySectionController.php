<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveStaySectionRequest;
use App\Models\Photo;
use App\Models\PhotoLocation;
use App\Models\StaySection;
use App\StayMarkdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaySectionController extends Controller
{
    public function preview(Request $request, StayMarkdown $markdown): JsonResponse
    {
        $data = $request->validate(['body' => ['present', 'nullable', 'string', 'max:50000']]);

        return response()->json(['html' => $markdown->render($data['body'])])->header('Cache-Control', 'private, no-store');
    }

    public function index(): View
    {
        return view('admin.stay.index', ['pages' => StaySection::EDITABLE_PAGES]);
    }

    public function show(string $page): View
    {
        return view('admin.stay.show', [
            'page' => $page,
            'sections' => StaySection::query()->where('page', $page)->with('photos.translations')->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function create(string $page): View
    {
        $section = new StaySection;
        $section->page = $page;

        return $this->form($section);
    }

    public function edit(StaySection $section): View
    {
        return $this->form($section);
    }

    private function form(StaySection $section): View
    {
        $section->load('photos.translations');

        return view('admin.stay.form', [
            'section' => $section,
            'photos' => Photo::query()->with('translations')->orderByDesc('id')->get(),
        ]);
    }

    public function store(SaveStaySectionRequest $request, string $page): RedirectResponse
    {
        $section = new StaySection;
        $section->page = $page;

        return $this->save($request, $section);
    }

    public function update(SaveStaySectionRequest $request, StaySection $section): RedirectResponse
    {
        return $this->save($request, $section);
    }

    private function save(SaveStaySectionRequest $request, StaySection $section): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($section, $data): void {
            PhotoLocation::query()->where('route_name', $section->pageRoute())->lockForUpdate()->firstOrFail();
            if (! $section->exists) {
                $section->position = (StaySection::query()->where('page', $section->page)->max('position') ?? 0) + 1;
            }
            $section->published = $data['published'];
            $section->translations = $data['translations'];
            $section->save();
            foreach (['fr', 'en', 'it'] as $locale) {
                $photos = [];
                foreach (array_values($data['photos'][$locale] ?? []) as $position => $photoId) {
                    $photos[$photoId] = ['position' => $position];
                }
                $section->photosForLocale($locale)->sync($photos);
            }
        });

        if (! empty($data['action'])) {
            return redirect()->route('admin.photos.create', ['section' => $section->id, 'section_locale' => $data['action']]);
        }

        return redirect()->route('admin.stay.show', $section->page)->with('status', __('sections.saved'));
    }

    public function move(Request $request, StaySection $section): RedirectResponse
    {
        $data = $request->validate(['direction' => ['required', 'in:up,down']]);
        DB::transaction(function () use ($section, $data): void {
            PhotoLocation::query()->where('route_name', $section->pageRoute())->lockForUpdate()->firstOrFail();
            $sections = StaySection::query()->where('page', $section->page)->orderBy('position')->orderBy('id')->lockForUpdate()->get();
            $index = $sections->search(fn (StaySection $item): bool => $item->id === $section->id);
            abort_if($index === false, 404);
            $target = $index + ($data['direction'] === 'up' ? -1 : 1);
            if ($target < 0 || $target >= $sections->count()) {
                return;
            }
            $items = $sections->all();
            [$items[$index], $items[$target]] = [$items[$target], $items[$index]];
            foreach ($items as $position => $item) {
                $item->position = $position + 1;
                $item->save();
            }
        });

        return redirect()->route('admin.stay.show', $section->page);
    }

    public function destroy(StaySection $section): RedirectResponse
    {
        $page = $section->page;
        $section->delete();

        return redirect()->route('admin.stay.show', $page)->with('status', __('sections.deleted'));
    }
}
