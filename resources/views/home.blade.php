<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portfolio d’Alan, développeur full-stack PHP et Laravel.">
    <title>Alan — Développeur full-stack PHP / Laravel</title>
    @vite(['resources/css/app.css'])
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu</a>
<header class="site-header">
    <a class="brand" href="{{ route('home') }}">Alan</a>
    <nav aria-label="Navigation principale">
        <a href="#projets">Projets</a>
        <a href="#contact">Contact</a>
        <a href="{{ route('login') }}">Administration</a>
    </nav>
</header>
<main id="main">
    <section class="hero" aria-labelledby="hero-title">
        <p class="eyebrow">Portfolio Platform v2</p>
        <h1 id="hero-title">Développeur full-stack PHP / Laravel</h1>
        <p>Je transforme des besoins métier en applications web accessibles, maintenables et mesurables.</p>
        <a class="button" href="#projets">Découvrir mes projets</a>
    </section>
    <section id="projets" aria-labelledby="projects-title">
        <h2 id="projects-title">Projets sélectionnés</h2>
        <p>Les études de cas vérifiées seront publiées ici. Le contenu essentiel reste disponible sans JavaScript.</p>
    </section>
    <section id="contact" aria-labelledby="contact-title">
        <h2 id="contact-title">Contact</h2>
        <p>Les coordonnées publiques seront ajoutées après validation éditoriale.</p>
    </section>
</main>
<footer><p>© {{ date('Y') }} Alan. Portfolio rendu côté serveur.</p></footer>
</body>
</html>
