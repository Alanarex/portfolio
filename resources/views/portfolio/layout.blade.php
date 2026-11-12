<!DOCTYPE html>
<html lang="{{ $locale }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">
    <meta name="theme-color" content="#faf6f6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b090a" media="(prefers-color-scheme: dark)">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings?->siteName ?? __('portfolio.brand_fallback') }}">
    <meta property="og:locale" content="{{ $locale === 'fr' ? 'fr_FR' : 'en_GB' }}">
    <meta property="og:locale:alternate" content="{{ $locale === 'fr' ? 'en_GB' : 'fr_FR' }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:url" content="{{ $meta['canonical'] }}">
    <meta property="og:image" content="{{ asset('assets/brand/portrait-hero-640.webp') }}">
    <meta property="og:image:alt" content="{{ __('portfolio.hero.portrait_alt') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    <meta name="twitter:image" content="{{ asset('assets/brand/portrait-hero-640.webp') }}">
    @php
        $frUrl = $locale === 'fr' ? $meta['canonical'] : $alternateUrl;
        $enUrl = $locale === 'en' ? $meta['canonical'] : $alternateUrl;
    @endphp
    {{-- Tab icon follows the browser/OS colour scheme; favicon.ico is the fallback. --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/light/favicon-32x32.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/light/favicon-16x16.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/dark/favicon-32x32.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/dark/favicon-16x16.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ $meta['canonical'] }}">
    <link rel="alternate" hreflang="fr" href="{{ $frUrl }}">
    <link rel="alternate" hreflang="en" href="{{ $enUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $frUrl }}">
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script>
        (function () {
            var root = document.documentElement;
            root.classList.remove('no-js');
            root.classList.add('js');
            try {
                var theme = localStorage.getItem('portfolio-theme');
                if (theme !== 'dark' && theme !== 'light') {
                    theme = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                root.setAttribute('data-theme', theme);
                if (sessionStorage.getItem('loaded') || matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    root.classList.add('no-loader');
                }
                // Arrivée après un changement de langue : le rideau se retire pour révéler la page.
                var wipe = sessionStorage.getItem('portfolio-wipe');
                if (wipe) {
                    sessionStorage.removeItem('portfolio-wipe');
                    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        root.classList.add('lang-entering');
                        root.setAttribute('data-wipe', wipe);
                    }
                }
            } catch (e) {
                root.classList.add('no-loader');
            }
        })();
    </script>
    @vite(['resources/css/public.css', 'resources/js/public.ts'])
</head>
@php
    $brandName = $profile?->displayName ?? $settings?->siteName ?? __('portfolio.brand_fallback');
    $homeUrl = route('portfolio.home', ['locale' => $locale]);
    $socialLinks = $settings?->socialLinks ?? [];
@endphp
<body class="public-portfolio" data-analytics-driver="null">
    <a href="#main-content" class="skip-link">{{ __('portfolio.skip') }}</a>
    <div class="lang-wipe" aria-hidden="true"><span></span></div>
    <script>
        (function () {
            var label = document.documentElement.getAttribute('data-wipe');
            if (label) {
                document.querySelector('.lang-wipe span').textContent = label;
            }
        })();
    </script>
    @include('portfolio.partials.gauge-defs')
    <div class="scroll-progress" aria-hidden="true"></div>

    @if (($page ?? 'home') === 'home')
        <div class="loader theme-dark" role="status" aria-label="{{ __('portfolio.loading') }}">
            <div class="loader__inner">
                <img class="loader__logo" src="{{ asset('assets/brand/logo-dark-96.webp') }}" width="56" height="56" alt="">
                @include('portfolio.partials.gauge', ['class' => 'gauge--loader'])
                <div class="loader__meter" aria-hidden="true"><i></i></div>
            </div>
        </div>
    @endif

    <header class="site-header">
        <div class="container site-header__inner">
            <a class="brand" href="{{ $homeUrl }}" aria-label="{{ $brandName }} — {{ __('portfolio.nav.home') }}">
                <img class="brand__logo logo-for-light" src="{{ asset('assets/brand/logo-light-96.webp') }}" width="36" height="36" alt="">
                <img class="brand__logo logo-for-dark" src="{{ asset('assets/brand/logo-dark-96.webp') }}" width="36" height="36" alt="">
                <span class="brand__name">{{ $brandName }}</span>
            </a>

            <nav class="site-nav" id="site-nav" aria-label="{{ __('portfolio.navigation') }}">
                <a href="{{ $homeUrl }}#projets">{{ __('portfolio.nav.projects') }}</a>
                <a href="{{ $homeUrl }}#experience">{{ __('portfolio.nav.experience') }}</a>
                <a href="{{ $homeUrl }}#competences">{{ __('portfolio.nav.skills') }}</a>
                <a href="{{ $homeUrl }}#contact">{{ __('portfolio.nav.contact') }}</a>
            </nav>

            <div class="site-tools">
                <a class="lang-switch" href="{{ $alternateUrl }}" hreflang="{{ $alternateLocale }}" lang="{{ $alternateLocale }}" aria-label="{{ __('portfolio.language_switch') }}">
                    <span @if ($locale === 'fr') aria-current="true" @endif>FR</span><span @if ($locale === 'en') aria-current="true" @endif>EN</span>
                </a>
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="{{ __('portfolio.theme.label') }}" data-label-dark="{{ __('portfolio.theme.to_dark') }}" data-label-light="{{ __('portfolio.theme.to_light') }}">
                    <svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.5" fill="currentColor"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
                    <svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7Z" fill="currentColor"/></svg>
                </button>
                <a class="button button--small site-cta" href="{{ $homeUrl }}#contact"><span>{{ __('portfolio.hero.contact_cta') }}</span><span class="button__arrow" aria-hidden="true">→</span></a>
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p>© {{ date('Y') }} {{ $brandName }} · {{ __('portfolio.footer.rights') }}</p>
            <nav class="site-footer__links" aria-label="{{ __('portfolio.footer.label') }}">
                <a href="{{ $homeUrl }}">{{ __('portfolio.nav.home') }}</a>
                <a href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}">{{ __('portfolio.nav.projects') }}</a>
                @foreach ($socialLinks as $platform => $url)
                    <a href="{{ $url }}" rel="noopener noreferrer">{{ ucfirst($platform) }}</a>
                @endforeach
                <a href="{{ route('portfolio.privacy', ['locale' => $locale]) }}">{{ __('portfolio.footer.privacy') }}</a>
                <a href="{{ route('portfolio.legal', ['locale' => $locale]) }}">{{ __('portfolio.footer.legal') }}</a>
            </nav>
        </div>
    </footer>
</body>
</html>
