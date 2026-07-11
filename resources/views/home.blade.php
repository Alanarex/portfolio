<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portfolio d’Alan, développeur full-stack PHP et Laravel.">
    <title>Alan — Développeur full-stack PHP / Laravel</title>
    @vite(['resources/css/app.css', 'resources/js/three-preview/index.ts'])
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
    <section class="three-preview" aria-labelledby="three-preview-title" data-three-preview data-state="poster">
        <div class="three-preview__copy">
            <p class="eyebrow">Aperçu facultatif</p>
            <h2 id="three-preview-title">Espace développeur en 3D</h2>
            <p>Une visualisation légère sera proposée ici. Le portfolio et ses liens restent entièrement utilisables sans WebGL ni JavaScript.</p>
            <button type="button" data-three-preview-load>Charger l’aperçu 3D</button>
            <p data-three-preview-status hidden>L’aperçu 3D n’est pas disponible sur cet appareil. Le contenu ci-dessous reste accessible.</p>
            <a href="#projets">Consulter les projets au format HTML</a>
        </div>
        <div class="three-preview__visual" aria-label="Illustration statique d’un espace de travail">
            <div class="three-preview__poster" aria-hidden="true">
                <span class="three-preview__desk"></span>
                <span class="three-preview__screen"></span>
            </div>
            <div class="three-preview__canvas" data-three-preview-canvas></div>
        </div>
    </section>
    <section id="contact" aria-labelledby="contact-title">
        <h2 id="contact-title">Contact</h2>
        <p>Les coordonnées publiques seront ajoutées après validation éditoriale.</p>
    </section>
</main>
<footer><p>© {{ date('Y') }} Alan. Portfolio rendu côté serveur.</p></footer>
</body>
</html>
