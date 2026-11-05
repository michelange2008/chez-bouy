<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavePhotoRequest;
use App\Models\Photo;
use App\Models\PhotoLocation;
use App\Models\PhotoTranslation;
use App\Models\StaySection;
use App\PhotoImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PhotoController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['location' => ['nullable', 'integer', Rule::exists('photo_locations', 'id')->where('route_name', 'home')]]);
        $locationId = $data['location'] ?? null;
        $query = Photo::query()->with(['translations', 'locations', 'sections']);
        if ($locationId) {
            $query->join('photo_location', 'photos.id', '=', 'photo_location.photo_id')
                ->where('photo_location.photo_location_id', $locationId)
                ->select('photos.*')->orderBy('photo_location.position')->orderBy('photos.id');
        } else {
            $query->orderByDesc('photos.id');
        }

        return view('admin.photos', [
            'photos' => $query->paginate(12)->withQueryString(),
            'locations' => PhotoLocation::query()->where('route_name', 'home')->orderBy('name')->get(),
            'locationId' => $locationId,
        ]);
    }

    public function create(Request $request): View
    {
        $data = $request->validate(['section' => ['nullable', 'integer', 'exists:stay_sections,id'], 'section_locale' => ['required_with:section', 'nullable', 'in:fr,en,it']]);

        return $this->form(new Photo, isset($data['section']) ? (int) $data['section'] : null, $data['section_locale'] ?? null);
    }

    public function edit(Photo $photo): View
    {
        return $this->form($photo);
    }

    private function form(Photo $photo, ?int $sectionId = null, ?string $sectionLocale = null): View
    {
        $photo->load(['translations', 'locations']);

        return view('admin.photos.form', [
            'photo' => $photo,
            'sectionId' => $sectionId,
            'sectionLocale' => $sectionLocale,
            'translations' => $photo->translations->keyBy('locale'),
            'locations' => PhotoLocation::query()->where('route_name', 'home')->orderBy('name')->get(),
        ]);
    }

    public function store(SavePhotoRequest $request, PhotoImage $images): RedirectResponse
    {
        return $this->save($request, new Photo, $images);
    }

    public function update(SavePhotoRequest $request, Photo $photo, PhotoImage $images): RedirectResponse
    {
        return $this->save($request, $photo, $images);
    }

    private function save(SavePhotoRequest $request, Photo $photo, PhotoImage $images): RedirectResponse
    {
        $data = $request->validated();
        $oldFilename = $photo->filename;
        $newFilename = null;
        $originalFilename = null;
        if ($request->hasFile('image')) {
            $newFilename = $images->store($request->file('image'));
            $originalFilename = mb_substr($data['original_filename'] ?? $request->file('image')->getClientOriginalName(), 0, 255);
        }
        try {
            DB::transaction(function () use ($data, $photo, $newFilename, $originalFilename): void {
                if ($newFilename) {
                    $photo->filename = $newFilename;
                    $photo->original_filename = $originalFilename;
                }
                $photo->published = (bool) $data['published'];
                $photo->save();
                foreach (array_keys(config('languages.supported')) as $locale) {
                    $values = $data['translations'][$locale] ?? [];
                    if (($values['title'] ?? '') === '') {
                        $photo->translations()->where('locale', $locale)->delete();

                        continue;
                    }
                    $translation = $photo->translations()->where('locale', $locale)->first() ?? new PhotoTranslation;
                    $translation->locale = $locale;
                    $translation->title = $values['title'];
                    $translation->description = $values['description'] ?? null;
                    $photo->translations()->save($translation);
                }
                $associations = [];
                if (! empty($data['location_id'])) {
                    $associations[$data['location_id']] = isset($data['position']) ? ['position' => $data['position']] : [];
                }
                $photo->locations()->sync($associations);
                if (! empty($data['section_id'])) {
                    $section = StaySection::query()->lockForUpdate()->findOrFail($data['section_id']);
                    $position = ($section->photosForLocale($data['section_locale'])->max('photo_stay_section.position') ?? -1) + 1;
                    $section->photosForLocale($data['section_locale'])->syncWithoutDetaching([$photo->id => ['position' => $position]]);
                }
            });
        } catch (Throwable $exception) {
            if ($newFilename) {
                $images->delete($newFilename);
            }
            throw $exception;
        }
        if ($newFilename && $oldFilename) {
            $images->delete($oldFilename);
        }

        if (! empty($data['section_id'])) {
            return redirect()->route('admin.stay.edit', ['section' => $data['section_id'], 'section_locale' => $data['section_locale']])->with('status', __('photos.saved'));
        }

        return redirect()->route('admin.photos')->with('status', __('photos.saved'));
    }

    public function image(Request $request, Photo $photo): StreamedResponse
    {
        $filename = $photo->filename;
        $thumbnail = PhotoImage::thumbnailPath($filename);
        if ($request->boolean('thumbnail') && Storage::disk('local')->exists($thumbnail)) {
            $filename = $thumbnail;
        }
        abort_unless(Storage::disk('local')->exists($filename), 404);

        return Storage::disk('local')->response($filename, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Photo $photo, PhotoImage $images): RedirectResponse
    {
        $filename = $photo->filename;
        $photo->delete();
        $images->delete($filename);

        return redirect()->route('admin.photos')->with('status', __('photos.deleted'));
    }
}
