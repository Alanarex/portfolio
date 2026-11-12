<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Portfolio professionnel d’Alan, développeur full-stack PHP et Laravel.')">
    <meta name="theme-color" content="#0b090a">
    <title>@yield('title', 'Alan — Développeur full-stack')</title>
    <script>
        document.documentElement.classList.add('js');
        try {
            if (sessionStorage.getItem('loaded') || matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('no-loader');
            }
        } catch (e) { document.documentElement.classList.add('no-loader'); }
    </script>
    @vite(['resources/css/app.css', 'resources/js/site.ts', 'resources/js/three-preview/index.ts'])
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu</a>
@include('partials.gauge-defs')
<div class="scroll-progress" aria-hidden="true"></div>
@sectionMissing('no_loader')
<div class="loader" role="status" aria-label="Chargement de la page">
    <div class="loader__inner">
        <span class="brand" aria-hidden="true">Alan<span class="brand__mark"></span></span>
        @include('partials.gauge', ['class' => 'gauge--loader'])
        <div class="loader__meter" aria-hidden="true"><i></i></div>
    </div>
</div>
@endif
<header class="site-header"><div class="container site-header__inner">
    <a class="brand" href="{{ route('home') }}" aria-label="Alan, accueil">Alan<span class="brand__mark"></span></a>
    <nav class="site-nav" aria-label="Navigation principale">
        <a href="{{ route('home') }}#projets">Projets</a><a href="{{ route('home') }}#preuves">Preuves</a><a href="{{ route('home') }}#apercu">Aperçu 3D</a><a href="{{ route('home') }}#contact">Contact</a>
        <a class="button button--small" href="{{ route('home') }}#contact"><span>Me contacter</span><span class="button__arrow" aria-hidden="true">→</span></a>
    </nav>
</div></header>
<main id="main">@yield('content')</main>
<footer class="site-footer"><div class="container site-footer__inner">
    <p>© {{ date('Y') }} Alan</p>
    <a href="{{ route('login') }}">Espace privé</a>
</div></footer>
</body>
</html>
