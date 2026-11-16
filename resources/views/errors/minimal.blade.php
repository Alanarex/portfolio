@php
    // Les vues d'erreur n'ont pas de contrôleur : la langue vient de l'URL (/fr, /en), sinon du cookie de choix puis du navigateur.
    $errorLocale = in_array(request()->segment(1), ['fr', 'en'], true)
        ? request()->segment(1)
        : \App\Support\PreferredLocale::fromRequest(request());
    app()->setLocale($errorLocale);
    $errorCode = trim($__env->yieldContent('code'));
    $alternate = $errorLocale === 'fr' ? 'en' : 'fr';
    $homeUrl = url('/'.$errorLocale);
    $hasAssets = file_exists(public_path('build/manifest.json'));
@endphp
<!DOCTYPE html>
<html lang="{{ $errorLocale }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#faf6f6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b090a" media="(prefers-color-scheme: dark)">
    <title>{{ __('portfolio.http_errors.'.$errorCode.'.title') }} — {{ __('portfolio.brand_fallback') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/light/favicon-32x32.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/dark/favicon-32x32.png') }}" media="(prefers-color-scheme: dark)">
    <script>
        (function () {
            var root = document.documentElement;
            root.classList.remove('no-js');
            try {
                var theme = localStorage.getItem('portfolio-theme');
                if (theme !== 'dark' && theme !== 'light') {
                    theme = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                root.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
    @if ($hasAssets)
        @vite(['resources/css/public.css'])
    @endif
</head>
<body class="public-portfolio">
    <a href="#main-content" class="skip-link">{{ __('portfolio.skip') }}</a>
    <header class="site-header">
        <div class="container site-header__inner">
            <a class="brand" href="{{ $homeUrl }}" aria-label="{{ __('portfolio.brand_fallback') }} — {{ __('portfolio.nav.home') }}">
                <img class="brand__logo logo-for-light" src="{{ asset('assets/brand/logo-light-96.webp') }}" width="36" height="36" alt="">
                <img class="brand__logo logo-for-dark" src="{{ asset('assets/brand/logo-dark-96.webp') }}" width="36" height="36" alt="">
                <span class="brand__name">{{ __('portfolio.brand_fallback') }}</span>
            </a>
            <div class="site-tools">
                <a class="lang-switch" href="{{ url('/'.$alternate) }}" hreflang="{{ $alternate }}" lang="{{ $alternate }}" aria-label="{{ __('portfolio.language_switch') }}">
                    <span @if ($errorLocale === 'fr') aria-current="true" @endif>FR</span><span @if ($errorLocale === 'en') aria-current="true" @endif>EN</span>
                </a>
            </div>
        </div>
    </header>

    <main id="main-content" class="container error-page" tabindex="-1">
        <p class="eyebrow">{{ __('portfolio.http_errors.error_label') }} {{ $errorCode }}</p>
        <p class="error-page__code" aria-hidden="true">{{ $errorCode }}</p>
        <h1>{{ __('portfolio.http_errors.'.$errorCode.'.title') }}</h1>
        <p>{{ __('portfolio.http_errors.'.$errorCode.'.message') }}</p>
        <div class="error-page__actions">
            <a class="button" href="{{ $homeUrl }}"><span>{{ __('portfolio.http_errors.back_home') }}</span><span class="button__arrow" aria-hidden="true">→</span></a>
            <a class="button button--secondary" href="{{ $homeUrl }}#contact"><span>{{ __('portfolio.http_errors.contact') }}</span></a>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p>© {{ date('Y') }} {{ __('portfolio.brand_fallback') }} · {{ __('portfolio.footer.rights') }}</p>
        </div>
    </footer>
</body>
</html>
