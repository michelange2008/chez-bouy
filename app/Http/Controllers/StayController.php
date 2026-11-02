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
        $guide = File::query()
            ->where('locale', $locale)
            ->where('purpose', File::WELCOME_GUIDE)
            ->first();

        return $guide && Storage::disk('local')->exists($guide->filename) ? $guide : null;
    }
}
