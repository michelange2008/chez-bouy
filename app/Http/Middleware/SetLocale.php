<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale', 'fr'));
        $supportedLocales = array_keys(config('languages.supported'));
        App::setLocale(in_array($locale, $supportedLocales, true) ? $locale : 'fr');

        return $next($request);
    }
}
