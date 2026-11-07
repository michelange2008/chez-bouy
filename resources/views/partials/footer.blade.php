<footer class="site-footer">
    <div class="site-footer-brand">
        <strong>Chez Bouy</strong>
        <p>{{ __('site.footer') }}</p>
    </div>
    <nav aria-label="{{ __('legal.footer_navigation') }}">
        <ul class="site-footer-links">
            <li><a class="button" href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>{{ __('site.contact_title') }}</a></li>
            <li><a href="{{ route('legal') }}" @if(request()->routeIs('legal')) aria-current="page" @endif>{{ __('legal.title') }}</a></li>
        </ul>
    </nav>
</footer>
