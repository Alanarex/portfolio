@extends('layouts.public')
@section('title', 'Alan — Développeur full-stack PHP / Laravel')
@section('description', 'Portfolio d’Alan, développeur full-stack PHP et Laravel spécialisé dans les applications accessibles et maintenables.')
@push('structured-data')
@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'url' => url('/'),
                'name' => 'Alan — Développeur full-stack',
                'inLanguage' => 'fr-FR',
            ],
            array_filter([
                '@type' => 'Person',
                '@id' => url('/').'#person',
                'name' => config('legal.publisher_name') ?: 'Alan',
                'jobTitle' => 'Développeur full-stack PHP / Laravel',
                'url' => url('/'),
                'knowsAbout' => ['PHP', 'Laravel', 'PostgreSQL', 'Vue.js', 'Docker', 'Three.js'],
            ]),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
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
        @foreach (range(1, 2) as $group)
            <div class="marquee__group">@foreach (range(1, 2) as $repeat)<span>PHP</span><span>Laravel 13</span><span>PostgreSQL</span><span>Redis</span><span>Vue 3</span><span>Inertia</span><span>Three.js</span><span>Docker</span>@endforeach</div>
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
            <h2 id="three-preview-title">Sur la route, en 3D</h2>
            <p class="lede">Une voiture sur une route, avec un panneau par section. Le portfolio reste entièrement utilisable sans WebGL ni JavaScript.</p>
            <button class="button" type="button" data-three-preview-load><span>Charger l’aperçu 3D</span><span class="button__arrow" aria-hidden="true">→</span></button>
            <p class="error" role="status" data-three-preview-status hidden>L’aperçu 3D n’est pas disponible sur cet appareil. Le contenu HTML reste accessible.</p>
            <p style="margin-top: var(--space-4)"><a href="#projets">Consulter les projets au format HTML</a></p>
            <p class="hint" style="margin-top: var(--space-4)">Modèle 3D : <a href="https://sketchfab.com/3d-models/mercedes-amg-gt-roadster-30255e1d942a455e970e7c776998f695" rel="noopener">« Mercedes Amg GT roadster »</a> par <a href="https://sketchfab.com/Resinnnn" rel="noopener">RES1N</a>, licence <a href="http://creativecommons.org/licenses/by/4.0/" rel="noopener">CC BY 4.0</a>, modifié (allégé, couleurs changées).</p>
        </div>
        <div class="three-preview__visual" role="img" aria-label="Illustration d’une voiture de sport roulant de nuit sur une route" data-scroll-reveal>
            <div class="three-preview__poster" aria-hidden="true"><span class="three-preview__road"></span></div>
            <div class="three-preview__canvas" data-three-preview-canvas></div>
            <div class="three-preview__overlay" aria-hidden="true"><span class="three-preview__badge">EXPÉRIENCE OPTIONNELLE</span></div>
        </div>
    </div>
</section>

<section class="section" id="plan" aria-labelledby="plan-title" style="padding-top: 0">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>Plan de route</p>
        <h2 id="plan-title" data-scroll-reveal>La route comme sommaire.</h2>
        <p class="lede" data-scroll-reveal>Suivez le trajet d’étape en étape : chaque arrêt est un lien vers une section de la page.</p>
        <div class="map" data-scroll-reveal>
            <svg viewBox="0 0 1000 520" role="group" aria-label="Carte du site avec quatre étapes">
                <defs>
                    <pattern id="map-grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" fill="none" stroke="#1b1718"/></pattern>
                    <radialGradient id="map-beam" cx="0" cy=".5" r="1"><stop offset="0" stop-color="#fff" stop-opacity=".5"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
                </defs>
                <rect width="1000" height="520" fill="url(#map-grid)"/>
                <g class="map__relief" fill="none"><ellipse cx="180" cy="140" rx="150" ry="90"/><ellipse cx="180" cy="140" rx="100" ry="58"/><ellipse cx="820" cy="400" rx="170" ry="100"/><ellipse cx="820" cy="400" rx="110" ry="62"/><ellipse cx="560" cy="120" rx="90" ry="50"/></g>
                <g fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path class="map__kerb" d="M70 430 C170 430 200 300 300 300 S430 390 520 390 S640 200 730 200 S860 100 930 100"/>
                    <path class="map__asphalt" d="M70 430 C170 430 200 300 300 300 S430 390 520 390 S640 200 730 200 S860 100 930 100"/>
                    <path class="map__lane" d="M70 430 C170 430 200 300 300 300 S430 390 520 390 S640 200 730 200 S860 100 930 100"/>
                    <path class="map__draw" pathLength="1" d="M70 430 C170 430 200 300 300 300 S430 390 520 390 S640 200 730 200 S860 100 930 100"/>
                </g>
                <g class="map__car"><path d="M10 -5 L70 -20 L70 20 L10 5Z" fill="url(#map-beam)" opacity=".7"/><rect x="-14" y="-8" width="28" height="16" rx="5" fill="#f5f3f4"/><rect x="-6" y="-5" width="12" height="10" rx="3" fill="#161a1d"/><rect x="-15" y="-7" width="3" height="4" fill="#e5383b"/><rect x="-15" y="3" width="3" height="4" fill="#e5383b"/></g>
            <a class="map__stop" href="#projets" aria-label="Étape 1, projets"><circle class="map__ring" cx="70" cy="430" r="22"/><text class="map__num" x="70" y="437" text-anchor="middle">1</text><text class="map__label" x="40" y="488" text-anchor="start">Projets</text><text class="map__tag" x="40" y="506" text-anchor="start">DÉPART</text></a>
            <a class="map__stop" href="#preuves" aria-label="Étape 2, preuves"><circle class="map__ring" cx="300" cy="300" r="22"/><text class="map__num" x="300" y="307" text-anchor="middle">2</text><text class="map__label" x="300" y="250" text-anchor="middle">Preuves</text><text class="map__tag" x="300" y="268" text-anchor="middle">ÉTAPE 02</text></a>
            <a class="map__stop" href="#apercu" aria-label="Étape 3, aperçu 3d"><circle class="map__ring" cx="730" cy="200" r="22"/><text class="map__num" x="730" y="207" text-anchor="middle">3</text><text class="map__label" x="730" y="150" text-anchor="middle">Aperçu 3D</text><text class="map__tag" x="730" y="168" text-anchor="middle">ÉTAPE 03</text></a>
            <a class="map__stop map__stop--end" href="#contact" aria-label="Étape 4, contact, arrivée"><circle class="map__ring" cx="930" cy="100" r="22"/><text class="map__num" x="930" y="107" text-anchor="middle">4</text><text class="map__label" x="930" y="160" text-anchor="middle">Contact</text><text class="map__tag" x="930" y="178" text-anchor="middle">ARRIVÉE</text></a>
            </svg>
        </div>
        <ol class="map__list" aria-label="Étapes du plan de route">
            <li><a class="map__link" href="#projets"><b class="map__index">01</b><span class="map__name">Projets</span></a></li>
            <li><a class="map__link" href="#preuves"><b class="map__index">02</b><span class="map__name">Preuves</span></a></li>
            <li><a class="map__link" href="#apercu"><b class="map__index">03</b><span class="map__name">Aperçu 3D</span></a></li>
            <li><a class="map__link" href="#contact"><b class="map__index">04</b><span class="map__name">Contact</span></a></li>
        </ol>
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
