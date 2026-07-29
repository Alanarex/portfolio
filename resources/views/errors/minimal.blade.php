<!doctype html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'fr' }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — Portfolio</title>
    @vite(['resources/css/app.css'])
</head>
<body class="public-portfolio error-page">
    <main id="main-content">
        <img src="{{ asset('assets/brand/logo-light-96.webp') }}" width="72" height="72" alt="">
        <p class="eyebrow">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a class="button button-primary" href="{{ route('portfolio.home', ['locale' => app()->getLocale() === 'en' ? 'en' : 'fr']) }}">
            {{ app()->getLocale() === 'en' ? 'Back to the portfolio' : 'Retour au portfolio' }}
        </a>
    </main>
</body>
</html>
