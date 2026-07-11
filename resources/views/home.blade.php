@extends('layouts.public')
@section('title', 'Alan — Développeur full-stack PHP / Laravel')
@section('description', 'Portfolio d’Alan, développeur full-stack PHP et Laravel spécialisé dans les applications accessibles et maintenables.')
@section('content')
<section class="hero" aria-labelledby="hero-title"><div class="hero__content">
    <p class="eyebrow">Portfolio Platform v2</p><h1 id="hero-title">Développeur full-stack PHP / Laravel</h1>
    <p class="lede">Je transforme des besoins métier en applications web accessibles, maintenables et mesurables.</p>
    <a class="button" href="#projets">Découvrir mes projets</a>
</div></section>
<section id="projets" aria-labelledby="projects-title">
    <p class="eyebrow">Travaux récents</p><h2 id="projects-title">Projets sélectionnés</h2>
    <div class="state" aria-live="polite"><h3>Les études de cas arrivent</h3><p>Les projets vérifiés seront publiés ici. Le contenu essentiel reste disponible sans JavaScript.</p></div>
</section>
<section class="three-preview" aria-labelledby="three-preview-title" data-three-preview data-state="poster">
    <div class="three-preview__copy"><p class="eyebrow">Aperçu facultatif</p><h2 id="three-preview-title">Espace développeur en 3D</h2>
        <p class="lede">Une visualisation légère enrichit l’expérience. Le portfolio et ses liens restent entièrement utilisables sans WebGL ni JavaScript.</p>
        <button type="button" data-three-preview-load>Charger l’aperçu 3D</button>
        <p class="error" role="status" data-three-preview-status hidden>L’aperçu 3D n’est pas disponible sur cet appareil. Le contenu HTML reste accessible.</p>
        <p><a href="#projets">Consulter les projets au format HTML</a></p>
    </div>
    <div class="three-preview__visual" role="img" aria-label="Illustration statique d’un espace de travail">
        <div class="three-preview__poster" aria-hidden="true"><span class="three-preview__desk"></span><span class="three-preview__screen"></span></div>
        <div class="three-preview__canvas" data-three-preview-canvas></div>
        <div class="three-preview__overlay" aria-hidden="true"><span class="three-preview__badge">Expérience optionnelle</span></div>
    </div>
</section>
<section id="contact" aria-labelledby="contact-title"><p class="eyebrow">Échangeons</p><h2 id="contact-title">Contact</h2><p class="lede">Les coordonnées publiques seront ajoutées après validation éditoriale.</p></section>
@endsection
