@extends('layouts.public')
@section('title', 'Alan — Développeur full-stack PHP / Laravel')
@section('description', 'Portfolio d’Alan, développeur full-stack PHP et Laravel spécialisé dans les applications accessibles et maintenables.')
@section('content')
<section class="hero" id="top" aria-labelledby="hero-title" data-hero>
    <div class="hero__veil" aria-hidden="true"></div><div class="hero__glow" aria-hidden="true"></div>
    <label class="hero__lamp switch"><span>Phares</span><input class="switch__input" type="checkbox" role="switch" checked data-hero-lamp aria-label="Phares : zone éclairée qui suit le curseur"><span class="switch__track"></span></label>
    <div class="container hero__grid">
        <div class="hero__content">
            <p class="eyebrow" data-reveal style="--i: 1">Alan — Développeur full-stack</p>
            <h1 id="hero-title" data-reveal style="--i: 2">Développeur full-stack <span class="stroke">PHP / Laravel</span></h1>
            <p class="lede" data-reveal style="--i: 3">Je transforme des besoins métier en applications web accessibles, maintenables et mesurables.</p>
            <div class="actions" data-reveal style="--i: 4">
                <a class="button" href="#projets"><span>Découvrir mes projets</span><span class="button__arrow" aria-hidden="true">→</span></a>
                <a class="button button--ghost" href="#contact"><span>Écrire un message</span></a>
            </div>
            <p class="scroll-cue mono" data-reveal style="--i: 5" aria-hidden="true"><i></i>Faites défiler</p>
        </div>
        <div class="hero__gauge" data-reveal style="--i: 3">
            @include('partials.gauge', ['class' => 'gauge--hero', 'idle' => true, 'label' => 'Compteur de vitesse qui accélère pendant le défilement'])
        </div>
    </div>
</section>

<div class="marquee" aria-hidden="true">
    <div class="marquee__track">
        @foreach (range(1, 2) as $loop_index)
            <span>PHP</span><span>Laravel 13</span><span>PostgreSQL</span><span>Redis</span><span>Vue 3</span><span>Inertia</span><span>Three.js</span><span>Docker</span>
        @endforeach
    </div>
</div>

<section class="section" id="positionnement" aria-labelledby="positioning-title">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>Positionnement</p>
        <h2 id="positioning-title" class="statement" data-scroll-reveal>Full-stack <b>PHP/Laravel</b>, avec un profil de pilotage de projet technique.</h2>
        <div class="trio">
            <article class="card card--lift" data-scroll-reveal><span class="number">01</span><h3>Comprendre le métier</h3><p>Cadrer le besoin avant d’écrire la première ligne.</p></article>
            <article class="card card--lift" data-scroll-reveal><span class="number">02</span><h3>Construire proprement</h3><p>Architecture modulaire, tests et analyse statique dès le départ.</p></article>
            <article class="card card--lift" data-scroll-reveal><span class="number">03</span><h3>Livrer et mesurer</h3><p>Des livraisons continues, vérifiées et documentées.</p></article>
        </div>
    </div>
</section>

<section class="section" id="projets" aria-labelledby="projects-title" style="padding-bottom: 0">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>Projets en vedette</p>
        <h2 id="projects-title" data-scroll-reveal>Projets sélectionnés</h2>
        <div class="state" aria-live="polite" style="margin-top: var(--space-6)"><h3>Les études de cas arrivent</h3><p>Les projets vérifiés seront publiés ici. Le contenu essentiel reste disponible sans JavaScript.</p></div>
        <div class="stack" aria-hidden="true">
            @foreach ([1, 2, 3] as $n)
                <article class="stack__card" style="--n: {{ $n - 1 }}">
                    <div class="stack__body">
                        <div><span class="number">0{{ $n }}</span><h3>Étude de cas à venir</h3><div class="stack__lines"><div class="skeleton" style="width: 80%"></div><div class="skeleton" style="width: 55%"></div></div></div>
                        <span class="tag">BIENTÔT</span>
                    </div>
                    <div class="stack__visual"><b>0{{ $n }}</b></div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="proof" id="preuves" aria-labelledby="proof-title">
    <div class="proof__steps" aria-hidden="true"><div></div><div></div><div></div></div>
    <div class="proof__stick"><div class="container proof__grid">
        <div>
            <p class="eyebrow">Preuves et mesures</p>
            <h2 id="proof-title">Montée en régime.</h2>
            <div class="proof__list">
                <div class="proof__item"><h3>Accessible</h3><p>Clavier, contrastes, mouvement réduit : vérifiés, pas supposés.</p></div>
                <div class="proof__item"><h3>Maintenable</h3><p>Modules isolés, tests et analyse statique à chaque livraison.</p></div>
                <div class="proof__item"><h3>Mesurable</h3><p>Les indicateurs vérifiés seront publiés ici dès qu’ils existent.</p></div>
            </div>
        </div>
        @include('partials.gauge', ['class' => 'gauge--proof', 'label' => 'Compteur de vitesse qui monte pendant le défilement'])
    </div></div>
</section>

<section class="section" id="apercu" aria-labelledby="three-preview-title">
    <div class="container three-preview" data-three-preview data-state="poster">
        <div class="three-preview__copy" data-scroll-reveal>
            <p class="eyebrow">Aperçu 3D facultatif</p>
            <h2 id="three-preview-title">Espace développeur en 3D</h2>
            <p class="lede">Une visualisation légère enrichit l’expérience. Le portfolio et ses liens restent entièrement utilisables sans WebGL ni JavaScript.</p>
            <button class="button" type="button" data-three-preview-load><span>Charger l’aperçu 3D</span><span class="button__arrow" aria-hidden="true">→</span></button>
            <p class="error" role="status" data-three-preview-status hidden>L’aperçu 3D n’est pas disponible sur cet appareil. Le contenu HTML reste accessible.</p>
            <p style="margin-top: var(--space-4)"><a href="#projets">Consulter les projets au format HTML</a></p>
        </div>
        <div class="three-preview__visual" role="img" aria-label="Illustration statique d’un espace de travail" data-scroll-reveal>
            <div class="three-preview__poster" aria-hidden="true"><span class="three-preview__desk"></span><span class="three-preview__screen"></span></div>
            <div class="three-preview__canvas" data-three-preview-canvas></div>
            <div class="three-preview__overlay" aria-hidden="true"><span class="three-preview__badge">EXPÉRIENCE OPTIONNELLE</span></div>
        </div>
    </div>
</section>

<section class="section" id="forces" aria-labelledby="strengths-title" style="padding-top: 0">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>Points forts techniques</p>
        <h2 id="strengths-title" data-scroll-reveal>Ce que je garantis.</h2>
        <div class="grid-4">
            <article class="card card--lift" data-scroll-reveal><span class="number">01</span><h3>Architecture modulaire</h3><p>Chaque module possède ses règles, ses routes et ses tests.</p></article>
            <article class="card card--lift" data-scroll-reveal><span class="number">02</span><h3>Qualité automatisée</h3><p>PHPUnit, Pint, Larastan, ESLint et Playwright en CI.</p></article>
            <article class="card card--lift" data-scroll-reveal><span class="number">03</span><h3>Sécurité par défaut</h3><p>Authentification, validation, limitation de débit et CSRF.</p></article>
            <article class="card card--lift" data-scroll-reveal><span class="number">04</span><h3>Accessibilité d’abord</h3><p>HTML sémantique, focus visible, contenu sans JavaScript.</p></article>
        </div>
    </div>
</section>

<section class="section" id="contact" aria-labelledby="contact-title" style="padding-top: 0">
    <div class="container contact">
        <div data-scroll-reveal><p class="eyebrow">Contact</p><h2 id="contact-title">Échangeons.</h2></div>
        <p class="lede" data-scroll-reveal>Les coordonnées publiques seront ajoutées après validation éditoriale. Le formulaire de contact sera le canal public.</p>
    </div>
</section>
@endsection
