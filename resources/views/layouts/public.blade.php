<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Portfolio professionnel d’Alan, développeur full-stack PHP et Laravel.')">
    <meta name="theme-color" content="#07101f">
    <title>@yield('title', 'Alan — Développeur full-stack')</title>
    @vite(['resources/css/app.css', 'resources/js/three-preview/index.ts'])
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu</a>
<header class="site-header"><div class="site-header__inner">
    <a class="brand" href="{{ route('home') }}" aria-label="Alan, accueil">Alan<span class="brand__mark">.</span></a>
    <nav class="site-nav" aria-label="Navigation principale"><a href="#projets">Projets</a><a href="#contact">Contact</a><a href="{{ route('login') }}">Administration</a></nav>
</div></header>
<main id="main">@yield('content')</main>
<footer class="site-footer"><div class="site-footer__inner"><p>© {{ date('Y') }} Alan. Portfolio rendu côté serveur.</p></div></footer>
</body>
</html>
