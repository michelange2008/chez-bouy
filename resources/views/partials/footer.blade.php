@php($availabilityFooter = request()->routeIs('availability'))
<footer @class([
    'site-footer',
    'sticky bottom-0 z-20 bg-(--color-background) py-2! gap-2! items-start! [&_.site-footer-links]:gap-1!' => $availabilityFooter,
])>
    <div class="site-footer-brand">
        <strong @class(['flex h-9 items-center text-base/none' => $availabilityFooter])>Chez Bouy</strong>
        <p>{{ __('site.footer') }}</p>
    </div>
    <nav aria-label="{{ __('legal.footer_navigation') }}">
        <ul class="site-footer-links">
            <li><a @class(['button', 'h-9 leading-none!' => $availabilityFooter]) href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>{{ __('site.contact_title') }}</a></li>
            <li><a @class(['text-sm!' => $availabilityFooter]) href="{{ route('legal') }}" @if(request()->routeIs('legal')) aria-current="page" @endif>{{ __('legal.title') }}</a></li>
        </ul>
    </nav>
</footer>
