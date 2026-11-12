{{--
    Gabarit d'étude de cas. Aucune route ne l'utilise encore : PORT-007/008 fournira les données.

    Contrat attendu pour $project (tableau) :
      title (string, requis), type, summary, status, role, period, stack (string[]),
      cover_url, sections (liste de {id, title, body, metric?, metric_label?}),
      gallery (liste de {url, alt}), live_url, source_url,
      previous / next ({title, url}).
    Toute clé absente masque simplement le bloc correspondant.
--}}
@extends('layouts.public')
@section('title', $project['title'].' — Étude de cas — Alan')
@section('description', $project['summary'] ?? 'Étude de cas d’un projet d’Alan, développeur full-stack PHP / Laravel.')
@section('content')
@php
    $sections = $project['sections'] ?? [];
    $gallery = $project['gallery'] ?? [];
@endphp
<article class="case container" aria-labelledby="case-title">
    <a class="case__back" href="{{ url('/') }}#projets">← Tous les projets</a>

    <header class="case__header">
        @if (! empty($project['type']))
            <p class="eyebrow" data-reveal style="--i: 0">Étude de cas · {{ $project['type'] }}</p>
        @endif
        <h1 id="case-title" data-reveal style="--i: 1">{{ $project['title'] }}</h1>
        @if (! empty($project['summary']))
            <p class="lede" data-reveal style="--i: 2">{{ $project['summary'] }}</p>
        @endif
    </header>

    @if (! empty($project['cover_url']))
        <figure class="case__cover" data-reveal style="--i: 3">
            <img src="{{ $project['cover_url'] }}" alt="{{ $project['cover_alt'] ?? '' }}" width="1600" height="900" loading="eager">
        </figure>
    @endif

    <dl class="case__meta" data-scroll-reveal>
        @foreach (['status' => 'Statut', 'role' => 'Rôle', 'period' => 'Période'] as $key => $label)
            @if (! empty($project[$key]))
                <div><dt class="mono">{{ $label }}</dt><dd>{{ $project[$key] }}</dd></div>
            @endif
        @endforeach
        @if (! empty($project['stack']))
            <div><dt class="mono">Stack</dt><dd>{{ implode(' · ', $project['stack']) }}</dd></div>
        @endif
    </dl>

    <div class="case__body">
        @if (count($sections) > 0)
            <nav class="case__toc" aria-label="Sommaire" data-case-toc>
                @foreach ($sections as $section)
                    <a href="#{{ $section['id'] }}">{{ $section['title'] }}</a>
                @endforeach
            </nav>
        @endif

        <div class="case__content">
            @foreach ($sections as $section)
                <section id="{{ $section['id'] }}" aria-labelledby="{{ $section['id'] }}-title" data-scroll-reveal>
                    <p class="mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                    <h2 id="{{ $section['id'] }}-title">{{ $section['title'] }}</h2>
                    @if (! empty($section['metric']))
                        <p class="case__metric" aria-label="{{ $section['metric_label'] ?? 'Résultat mesuré' }}">{{ $section['metric'] }}</p>
                    @endif
                    <p>{{ $section['body'] }}</p>
                </section>
            @endforeach

            @if (count($gallery) > 0)
                <section id="galerie" aria-labelledby="galerie-title" data-scroll-reveal>
                    <p class="mono">{{ str_pad((string) (count($sections) + 1), 2, '0', STR_PAD_LEFT) }}</p>
                    <h2 id="galerie-title">Galerie</h2>
                    <ul class="case__gallery">
                        @foreach ($gallery as $shot)
                            <li><img src="{{ $shot['url'] }}" alt="{{ $shot['alt'] ?? '' }}" loading="lazy" width="1280" height="800"></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (! empty($project['live_url']) || ! empty($project['source_url']))
                <div class="case__links">
                    @if (! empty($project['live_url']))
                        <a class="button" href="{{ $project['live_url'] }}" rel="noopener"><span>Voir le site</span><span class="button__arrow" aria-hidden="true">↗</span></a>
                    @endif
                    @if (! empty($project['source_url']))
                        <a class="button button--ghost" href="{{ $project['source_url'] }}" rel="noopener"><span>Code source</span></a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if (! empty($project['previous']) || ! empty($project['next']))
        <nav class="case__pager" aria-label="Autres études de cas">
            @if (! empty($project['previous']))
                <a class="card card--lift" href="{{ $project['previous']['url'] }}" rel="prev"><span class="mono">← Précédent</span><h3>{{ $project['previous']['title'] }}</h3></a>
            @endif
            @if (! empty($project['next']))
                <a class="card card--lift case__pager-next" href="{{ $project['next']['url'] }}" rel="next"><span class="mono">Suivant →</span><h3>{{ $project['next']['title'] }}</h3></a>
            @endif
        </nav>
    @endif
</article>
@endsection
