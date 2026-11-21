<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('rentals.access_email.subject', [], $locale) }}</title>
</head>
<body>
    <p>{{ __('rentals.access_email.greeting', [], $locale) }}</p>
    <p>{{ __('rentals.access_email.welcome', [], $locale) }}</p>
    <p>{{ __('rentals.access_email.website', [], $locale) }}<br>
        <a href="https://chezbouy.cumulus.org">https://chezbouy.cumulus.org</a></p>
    <p>{{ __('rentals.access_email.select', [], $locale) }}
        <strong>{{ __('rentals.access_email.private_area', [], $locale) }}</strong>
        {{ __('rentals.access_email.sign_in', [], $locale) }}</p>
    <p>{{ __('rentals.access_email.booklet', [], $locale) }}</p>
    <p>{{ __('rentals.access_email.contact', [], $locale) }}</p>
    <p>{{ __('rentals.access_email.farewell', [], $locale) }}</p>
    <p>Michel et Dominique</p>
</body>
</html>
