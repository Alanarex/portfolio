@extends('layouts.public')
@section('no_loader', '1')
@section('title', 'Mentions légales — Alan')
@section('description', 'Éditeur, hébergeur, données personnelles et crédits du portfolio d’Alan.')
@section('content')
@php($legal = config('legal'))
<article class="legal container" aria-labelledby="legal-title">
    <header>
        <p class="eyebrow" data-reveal style="--i: 0">Informations légales</p>
        <h1 id="legal-title" data-reveal style="--i: 1">Mentions légales</h1>
        @if (! empty($legal['updated_at']))
            <p class="hint" data-reveal style="--i: 2">Dernière mise à jour : {{ $legal['updated_at'] }}</p>
        @endif
    </header>

    <section aria-labelledby="legal-publisher">
        <h2 id="legal-publisher">Éditeur du site</h2>
        <dl class="legal__list">
            @if (! empty($legal['publisher_name']))
                <div class="legal__item"><dt class="mono">Nom</dt><dd>{{ $legal['publisher_name'] }}</dd></div>
            @endif
            <div class="legal__item"><dt class="mono">Statut</dt><dd>{{ $legal['publisher_status'] }}</dd></div>
            @if (! empty($legal['director']))
                <div class="legal__item"><dt class="mono">Directeur de la publication</dt><dd>{{ $legal['director'] }}</dd></div>
            @endif
            @if (! empty($legal['publisher_email']))
                <div class="legal__item"><dt class="mono">Contact</dt><dd><a href="mailto:{{ $legal['publisher_email'] }}">{{ $legal['publisher_email'] }}</a></dd></div>
            @endif
        </dl>
        <p>L’adresse postale et le numéro de téléphone de l’éditeur ne sont pas publiés ; ils sont communiqués à l’hébergeur, qui peut les transmettre à l’autorité judiciaire sur demande.</p>
    </section>

    <section aria-labelledby="legal-host">
        <h2 id="legal-host">Hébergement</h2>
        <dl class="legal__list">
            <div class="legal__item"><dt class="mono">Hébergeur</dt><dd>{{ $legal['host_name'] }}</dd></div>
            <div class="legal__item"><dt class="mono">Adresse</dt><dd>{{ $legal['host_address'] }}</dd></div>
            <div class="legal__item"><dt class="mono">Site</dt><dd><a href="{{ $legal['host_url'] }}" rel="noopener">{{ $legal['host_url'] }}</a></dd></div>
        </dl>
    </section>

    <section aria-labelledby="legal-data">
        <h2 id="legal-data">Données personnelles et cookies</h2>
        <p>Le site public n’utilise aucun outil de mesure d’audience, aucun cookie publicitaire et aucun traceur tiers. Les polices sont hébergées sur le site lui-même.</p>
        <p>Un cookie de session technique est utilisé uniquement pour l’espace d’administration privé ; il n’est pas déposé pour les visiteurs du portfolio.</p>
        <p>Pour toute question relative à vos données, ou pour exercer vos droits d’accès, de rectification et de suppression, utilisez le contact de l’éditeur ci-dessus.</p>
    </section>

    <section aria-labelledby="legal-ip">
        <h2 id="legal-ip">Propriété intellectuelle et crédits</h2>
        <p>Le contenu éditorial et le code du site sont la propriété de leur auteur, sauf mention contraire. Toute reproduction sans autorisation est interdite.</p>
        <ul class="legal__credits">
            <li>Modèle 3D : « <a href="https://sketchfab.com/3d-models/mercedes-amg-gt-roadster-30255e1d942a455e970e7c776998f695" rel="noopener">Mercedes Amg GT roadster</a> » par <a href="https://sketchfab.com/Resinnnn" rel="noopener">RES1N</a>, licence <a href="http://creativecommons.org/licenses/by/4.0/" rel="noopener">CC BY 4.0</a>, modifié (allégé, couleurs changées).</li>
            <li>Polices Inter, Space Grotesk et JetBrains Mono : licence SIL Open Font License 1.1.</li>
            <li>Three.js et Lenis : licence MIT.</li>
        </ul>
        <p>Les marques citées appartiennent à leurs propriétaires respectifs ; leur présence n’indique aucun lien avec eux.</p>
    </section>

    <p><a href="{{ route('home') }}">← Retour à l’accueil</a></p>
</article>
@endsection
