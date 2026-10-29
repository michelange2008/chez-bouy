<form class="language-switcher" action="{{ route('locale.update') }}" method="post"
      aria-label="{{ __('site.language') }}">
    @csrf
    @foreach(['fr' => 'Français', 'en' => 'English', 'it' => 'Italiano'] as $locale => $label)
        <button type="submit"
                name="locale"
                value="{{ $locale }}"
                lang="{{ $locale }}"
                aria-label="{{ $label }}"
                title="{{ $label }}"
                aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}">
            {{ strtoupper($locale) }}
        </button>
    @endforeach
</form>
