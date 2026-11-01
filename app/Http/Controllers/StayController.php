<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StayController extends Controller
{
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
        $id = config('stay.welcome_guides.'.$locale);
        if (! $id) {
            return null;
        }
        $guide = File::query()->whereKey($id)->where('locale', $locale)->first();

        return $guide && Storage::disk('local')->exists($guide->filename) ? $guide : null;
    }
}
