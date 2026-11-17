<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Photo;
use App\Models\StaySection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StayController extends Controller
{
    public function show(Request $request): View
    {
        $page = substr($request->route()->getName(), strlen('stay.'));
        abort_unless(in_array($page, StaySection::PAGES, true), 404);
        $sections = StaySection::query()->where('page', $page)->where('published', true)
            ->with('photos.translations')
            ->orderBy('position')->orderBy('id')->get();
        foreach ($sections as $section) {
            $section->setRelation('photos', $section->displayPhotosForLocale(app()->getLocale())->where('published', true));
        }
        $availablePhotoIds = $sections->flatMap->photos->unique('id')
            ->filter(fn (Photo $photo): bool => Storage::disk('local')->exists($photo->filename))->pluck('id')->all();

        return view('space.'.$page, compact('sections', 'availablePhotoIds'));
    }

    public function image(Photo $photo): StreamedResponse
    {
        abort_unless($photo->published, 404);
        $sections = $photo->sections()->where('published', true)->whereIn('page', StaySection::PAGES)->with('photos')->get();
        abort_unless($sections->contains(fn (StaySection $section): bool => $section->displayPhotosForLocale(app()->getLocale())->contains('id', $photo->id)), 404);
        abort_unless(Storage::disk('local')->exists($photo->filename), 404);

        return Storage::disk('local')->response($photo->filename, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function index(): View
    {
        return view('space', ['welcomeGuideAvailable' => $this->welcomeGuide() !== null]);
    }

    public function downloadGuide(): StreamedResponse
    {
        $guide = $this->welcomeGuide();
        abort_if($guide === null, 404);

        $extension = pathinfo($guide->filename, PATHINFO_EXTENSION);
        $name = 'guide-accueil-'.app()->getLocale().($extension !== '' ? '.'.$extension : '');

        return Storage::disk('local')->download($guide->filename, $name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function welcomeGuide(): ?File
    {
        $locale = app()->getLocale();
        $guide = File::query()
            ->where('locale', $locale)
            ->where('purpose', File::WELCOME_GUIDE)
            ->first();

        return $guide && Storage::disk('local')->exists($guide->filename) ? $guide : null;
    }
}
