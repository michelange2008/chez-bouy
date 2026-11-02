<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HomeController extends Controller
{
    public function index(): View
    {
        $photos = Photo::query()->with('translations')
            ->join('photo_location', 'photos.id', '=', 'photo_location.photo_id')
            ->join('photo_locations', 'photo_locations.id', '=', 'photo_location.photo_location_id')
            ->where('photo_locations.route_name', 'home')
            ->where('photos.published', true)
            ->select('photos.*')
            ->orderBy('photo_location.position')->orderBy('photos.id')->get();
        $availablePhotoIds = $photos->filter(fn (Photo $photo): bool => Storage::disk('local')->exists($photo->filename))->modelKeys();

        return view('home', compact('photos', 'availablePhotoIds'));
    }

    public function image(Photo $photo): StreamedResponse
    {
        abort_unless($photo->published && $photo->locations()->where('route_name', 'home')->exists(), 404);
        abort_unless(Storage::disk('local')->exists($photo->filename), 404);

        return Storage::disk('local')->response($photo->filename, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
