<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $locale = $request->input('locale');
        abort_unless(in_array($locale, ['fr', 'en', 'it'], true), 422);
        $request->session()->put('locale', $locale);

        return redirect()->route('home');
    }
}
