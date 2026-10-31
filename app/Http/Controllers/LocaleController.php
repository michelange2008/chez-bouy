<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');
        abort_unless(in_array($locale, array_keys(config('languages.supported')), true), 422);
        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: route('home'));
    }
}
