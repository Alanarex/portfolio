@extends('portfolio.layout')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $title = $profile?->professionalTitles[0] ?? __('portfolio.hero.title_fallback');
    // Mise en valeur (contour) de la partie technologique du titre : « PHP / Laravel ».
    $techPosition = mb_stripos($title, 'PHP');
    $titleLead = $techPosition !== false && $techPosition > 0 ? trim(mb_substr($title, 0, $techPosition)) : $title;
    $titleTech = $techPosition !== false && $techPosition > 0 ? trim(mb_substr($title, $techPosition)) : null;

    $skillNames = collect($skills?->categories ?? [])->flatMap(fn (array $category): array => $category['skills'] ?? [])->pluck('name')->filter()->unique()->values();
    $marqueeNames = $skillNames->take(16);
    $experiences = $career?->experiences ?? [];
    $education = $career?->education ?? [];
    $certifications = $career?->certifications ?? [];
    $languages = $career?->languages ?? [];
    $proofItems = array_slice($verifiedMetrics, 0, 3);
    $threeDEnabled = ($settings?->featureFlags['three_d'] ?? true) !== false;
    $activityEnabled = ($settings?->featureFlags['activity'] ?? false) === true;
    $home = route('portfolio.home', ['locale' => $locale]);

    // Étapes du parcours : études et expériences, de la plus ancienne à la plus récente.
    $milestones = collect($experiences)
        ->map(fn (array $item): array => [
            'kind' => 'experience',
            'year' => (int) ($item['start']['year'] ?? 0),
            'period' => ($item['start']['year'] ?? '').' — '.(($item['is_current'] ?? false) ? __('portfolio.three_d.now') : ($item['end']['year'] ?? '')),
            'title' => $item['role'] ?? '',
            'org' => $item['organization'] ?? '',
            'summary' => $item['summary'] ?? null,
            'highlights' => array_slice($item['highlights'] ?? [], 0, 3),
        ])
        ->concat(collect($education)->map(fn (array $item): array => [
            'kind' => 'education',
            'year' => (int) ($item['start']['year'] ?? 0),
            'period' => trim(($item['start']['year'] ?? '').' — '.($item['end']['year'] ?? ''), ' —'),
            'title' => $item['program'] ?? '',
            'org' => $item['institution'] ?? '',
            'summary' => null,
            'highlights' => [],
        ]))
        ->sortBy([['year', 'asc'], ['kind', 'asc']])
        ->values()
        ->take(-6)
        ->values();

    // Carte des compétences : un arrêt par domaine (7 au maximum), répartis le long d'un tracé sinueux.
    $categories = array_values($skills?->categories ?? []);
    $anchors = [[70, 420], [220, 300], [370, 390], [520, 230], [670, 330], [820, 160], [940, 110]];
    $mapCount = min(count($categories), count($anchors));
    $mapStops = [];
    for ($i = 0; $i < $mapCount; $i++) {
        $index = $mapCount === 1 ? 3 : (int) round($i * (count($anchors) - 1) / ($mapCount - 1));
        $mapStops[] = $anchors[$index];
    }
    $mapPath = $mapStops === [] ? '' : 'M'.$mapStops[0][0].' '.$mapStops[0][1];
    for ($i = 1; $i < count($mapStops); $i++) {
        [$x0, $y0] = $mapStops[$i - 1];
        [$x1, $y1] = $mapStops[$i];
        $half = ($x1 - $x0) / 2;
        $mapPath .= ' C'.($x0 + $half).' '.$y0.' '.($x1 - $half).' '.$y1.' '.$x1.' '.$y1;
    }

    // Niveau CECRL (A1 à C2) converti en 1 à 6 pour la jauge des langues.
    $cefrLevels = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
@endphp

@section('content')
<section class="hero" id="top" aria-labelledby="hero-title" data-hero>
    <div class="hero__veil" aria-hidden="true"></div><div class="hero__glow" aria-hidden="true"></div>
    <label class="hero__lamp switch"><span>{{ __('portfolio.hero.lamp') }}</span><input class="switch__input" type="checkbox" role="switch" checked data-hero-lamp aria-label="{{ __('portfolio.hero.lamp_label') }}"><span class="switch__track"></span></label>
    <div class="container hero__grid">
        <div class="hero__content">
            <p class="eyebrow" data-reveal style="--i: 1">{{ __('portfolio.hero.eyebrow') }}</p>
            <h1 id="hero-title" data-reveal style="--i: 2">{{ $titleLead }}@if ($titleTech) <span class="stroke">{{ $titleTech }}</span>@endif</h1>
            <p class="lede" data-reveal style="--i: 3">{{ $profile?->summary ?? __('portfolio.hero.summary_fallback') }}</p>
            <div class="actions" data-reveal style="--i: 4">
                <a class="button" href="#projets"><span>{{ __('portfolio.hero.projects_cta') }}</span><span class="button__arrow" aria-hidden="true">→</span></a>
                <a class="button button--ghost" href="#contact"><span>{{ __('portfolio.hero.contact_cta') }}</span></a>
                @if ($cvAvailable)
                    <a class="button button--ghost" href="{{ route('portfolio.cv.download', ['locale' => $locale]) }}"><span>{{ __('portfolio.hero.cv_cta') }}</span></a>
                @endif
            </div>
            <p class="scroll-cue mono" data-reveal style="--i: 5" aria-hidden="true"><i></i>{{ __('portfolio.hero.scroll') }}</p>
        </div>
        <div class="hero__gauge" data-reveal style="--i: 3">
            @include('portfolio.partials.gauge', ['class' => 'gauge--hero', 'idle' => true, 'label' => __('portfolio.hero.gauge_label')])
        </div>
    </div>
</section>

@if ($marqueeNames->isNotEmpty())
    <div class="marquee" aria-hidden="true">
        <div class="marquee__track">
            @foreach (range(1, 2) as $group)
                <div class="marquee__group">@foreach (range(1, 2) as $repeat)@foreach ($marqueeNames as $name)<span>{{ $name }}</span>@endforeach @endforeach</div>
            @endforeach
        </div>
    </div>
@endif

<section class="section" id="positionnement" aria-labelledby="positioning-title">
    <div class="container about">
        <div>
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.positioning_kicker') }}</p>
            <h2 id="positioning-title" class="statement" data-scroll-reveal>{{ __('portfolio.sections.positioning') }}</h2>
            @if ($profile)
                <p class="lede about__text" data-scroll-reveal>{{ $profile->biography }}</p>
                @if ($profile->professionalTitles !== [])
                    <ul class="tag-row" aria-label="{{ __('portfolio.sections.positioning') }}" data-scroll-reveal>
                        @foreach ($profile->professionalTitles as $professionalTitle)
                            <li class="chip">{{ $professionalTitle }}</li>
                        @endforeach
                    </ul>
                @endif
            @else
                <p class="state">{{ __('portfolio.profile.unavailable') }}</p>
            @endif
        </div>
        <aside class="about__card card" data-scroll-reveal aria-label="{{ __('portfolio.hero.portrait_alt') }}">
            <picture>
                <source type="image/webp" srcset="{{ asset('assets/brand/portrait-hero-320.webp') }} 320w, {{ asset('assets/brand/portrait-hero-640.webp') }} 640w" sizes="(max-width: 760px) 70vw, 320px">
                <img src="{{ asset('assets/brand/portrait-hero-640.webp') }}" width="640" height="800" alt="{{ __('portfolio.hero.portrait_alt') }}" loading="lazy" decoding="async">
            </picture>
            @if ($profile)
                <dl class="facts">
                    @if ($profile->location)<div><dt class="mono">{{ __('portfolio.profile.location') }}</dt><dd>{{ $profile->location }}</dd></div>@endif
                    @if ($profile->availability)<div><dt class="mono">{{ __('portfolio.profile.availability') }}</dt><dd>{{ $profile->availability }}</dd></div>@endif
                    <div><dt class="mono">{{ __('portfolio.profile.projects_count') }}</dt><dd>{{ $projectsCount }}</dd></div>
                </dl>
            @endif
        </aside>
    </div>
</section>

@if (($posts ?? []) !== [])
    <section id="actualites" class="section" aria-labelledby="posts-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.posts_kicker') }}</p>
            <h2 id="posts-title" data-scroll-reveal>{{ __('portfolio.sections.posts') }}</h2>
            <ol class="posts">
                @foreach ($posts as $post)
                    <li>
                        <article class="card post" aria-labelledby="post-{{ $post->id }}-date" data-scroll-reveal>
                            <p class="mono"><time id="post-{{ $post->id }}-date" datetime="{{ $post->publishedAt->format(DATE_ATOM) }}">{{ Carbon::instance($post->publishedAt)->locale(app()->getLocale())->isoFormat('LL') }}</time></p>
                            {{-- Plain text only: escaped, line breaks kept. --}}
                            <p>{!! nl2br(e($post->body), false) !!}</p>
                            @if ($post->linkUrl)
                                <p><a href="{{ $post->linkUrl }}" rel="noopener noreferrer">{{ $post->linkLabel ?? $post->linkUrl }}</a></p>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif

<section class="section" id="projets" aria-labelledby="projects-title" style="padding-bottom: 0">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.projects_intro') }}</p>
                <h2 id="projects-title" data-scroll-reveal>{{ __('portfolio.sections.projects') }}</h2>
            </div>
            <a class="button button--ghost" href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}"><span>{{ __('portfolio.sections.all_projects') }}</span><span class="button__arrow" aria-hidden="true">→</span></a>
        </div>
        @if ($featuredProjects !== [])
            <div class="stack">
                @foreach (array_slice($featuredProjects, 0, 5) as $project)
                    @include('portfolio.partials.project-stack', ['project' => $project, 'index' => $loop->index])
                @endforeach
            </div>
        @else
            <div class="state" aria-live="polite"><p>{{ __('portfolio.projects.featured_empty') }}</p></div>
        @endif
    </div>
</section>

@if ($proofItems !== [])
    <section class="section proof" id="preuves" aria-labelledby="proof-title">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.metrics_intro') }}</p>
            <h2 id="proof-title" data-scroll-reveal>{{ __('portfolio.sections.metrics') }}</h2>
            <ol class="proof__list">
                @foreach ($proofItems as $metric)
                    <li class="proof__item card" data-scroll-reveal>
                        <span class="proof__index mono">{{ __('portfolio.proof.item', ['n' => str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)]) }}</span>
                        {{-- Chiffres mis en valeur : le texte est échappé avant l'ajout des balises. --}}
                        <p class="proof__text">{!! preg_replace('/\d+(?:[.,]\d+)?(?:\s?(?:ms|%|secondes?|seconds?))?/u', '<b>$0</b>', e($metric)) !!}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif

@if ($milestones->isNotEmpty())
    <section class="journey theme-dark" id="experience" aria-labelledby="journey-title" style="--n: {{ $milestones->count() }}" @if ($threeDEnabled) data-three-preview data-state="poster" @endif>
        <div class="journey__stick">
            <div class="journey__stage" data-three-preview-canvas>
                <div class="journey__poster" aria-hidden="true"><span class="three-preview__road"></span></div>
            </div>
            <div class="journey__scrim" aria-hidden="true"></div>
            <div class="container journey__ui">
                <div class="journey__copy">
                    <p class="eyebrow">{{ __('portfolio.sections.experience_kicker') }}</p>
                    <h2 id="journey-title">{{ __('portfolio.sections.experience') }}</h2>
                    @if ($threeDEnabled)
                        <p class="lede journey__lede">{{ __('portfolio.three_d.intro') }}</p>
                        <button class="button" type="button" data-three-preview-load><span>{{ __('portfolio.three_d.load') }}</span><span class="button__arrow" aria-hidden="true">→</span></button>
                        <p class="error" role="status" data-three-preview-status hidden>{{ __('portfolio.three_d.unavailable') }}</p>
                    @endif
                </div>
                <ol class="journey__steps" aria-label="{{ __('portfolio.three_d.steps') }}">
                    @foreach ($milestones as $milestone)
                        <li @class(['journey__step', 'is-active' => $loop->first]) data-step-index="{{ $loop->index }}" data-sign-card="{{ json_encode([
                            'kind' => __('portfolio.three_d.kind_'.$milestone['kind']),
                            'period' => $milestone['period'],
                            'title' => $milestone['title'],
                            'org' => $milestone['org'],
                            'summary' => $milestone['summary'] ?? '',
                            'highlights' => $milestone['highlights'],
                            'counter' => __('portfolio.three_d.counter', ['n' => $loop->iteration, 'total' => $loop->count]),
                        ], JSON_UNESCAPED_UNICODE) }}">
                            <p class="journey__period mono">{{ $milestone['period'] }} <span class="journey__count">· {{ __('portfolio.three_d.counter', ['n' => $loop->iteration, 'total' => $loop->count]) }}</span></p>
                            <h3>{{ $milestone['title'] }}</h3>
                            @if ($milestone['org'])<p class="journey__org">{{ $milestone['org'] }}</p>@endif
                            @if (! empty($milestone['summary']))<p class="journey__summary">{{ $milestone['summary'] }}</p>@endif
                            @if ($milestone['highlights'] !== [])
                                <ul class="checklist">
                                    @foreach ($milestone['highlights'] as $highlight)<li>{{ $highlight }}</li>@endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ol>
                @if ($threeDEnabled)
                    <div class="journey__hud" aria-hidden="true">
                        <span class="journey__hud-count mono" data-journey-count>{{ __('portfolio.three_d.counter', ['n' => 1, 'total' => $milestones->count()]) }}</span>
                        <ol class="journey__dots">
                            @foreach ($milestones as $milestone)<li @class(['is-active' => $loop->first]) data-journey-dot></li>@endforeach
                        </ol>
                    </div>
                    <p class="journey__drag" aria-hidden="true"><span class="journey__drag-icon">↔</span> {{ __('portfolio.three_d.drag') }}</p>
                    <p class="journey__credit hint">{!! __('portfolio.three_d.credit', [
                        'model' => '<a href="https://sketchfab.com/3d-models/mercedes-amg-gt-roadster-30255e1d942a455e970e7c776998f695" rel="noopener noreferrer">« Mercedes Amg GT roadster »</a>',
                        'author' => '<a href="https://sketchfab.com/Resinnnn" rel="noopener noreferrer">RES1N</a>',
                        'licence' => '<a href="http://creativecommons.org/licenses/by/4.0/" rel="noopener noreferrer">CC BY 4.0</a>',
                    ]) !!}</p>
                @endif
            </div>
        </div>
    </section>
@endif

@if ($categories !== [])
    <section class="skillmap theme-dark" id="competences" aria-labelledby="skills-title" data-skillmap style="--n: {{ $mapCount }}">
        <div class="skillmap__stick">
            <div class="container skillmap__inner">
                <header class="skillmap__head">
                    <p class="eyebrow">{{ __('portfolio.sections.strengths') }}</p>
                    <h2 id="skills-title">{{ __('portfolio.sections.skills') }}</h2>
                    <p class="lede">{{ __('portfolio.sections.skills_intro') }}</p>
                </header>
                <div class="map">
                    <svg viewBox="0 0 1000 520" role="group" aria-label="{{ __('portfolio.map.label', ['count' => $mapCount]) }}">
                        <defs>
                            <pattern id="map-grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" fill="none" stroke="#1b1718"/></pattern>
                            <radialGradient id="map-beam" cx="0" cy=".5" r="1"><stop offset="0" stop-color="#fff" stop-opacity=".5"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
                        </defs>
                        <rect width="1000" height="520" fill="url(#map-grid)"/>
                        <g class="map__relief" fill="none"><ellipse cx="180" cy="140" rx="150" ry="90"/><ellipse cx="180" cy="140" rx="100" ry="58"/><ellipse cx="820" cy="400" rx="170" ry="100"/><ellipse cx="820" cy="400" rx="110" ry="62"/><ellipse cx="560" cy="120" rx="90" ry="50"/></g>
                        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path class="map__kerb" d="{{ $mapPath }}"/>
                            <path class="map__asphalt" d="{{ $mapPath }}"/>
                            <path class="map__lane" d="{{ $mapPath }}"/>
                            <path class="map__draw" pathLength="1" d="{{ $mapPath }}" data-map-path/>
                        </g>
                        <g class="map__car" style="offset-path: path('{{ $mapPath }}')"><path d="M10 -5 L70 -20 L70 20 L10 5Z" fill="url(#map-beam)" opacity=".7"/><rect x="-14" y="-8" width="28" height="16" rx="5" fill="#f5f3f4"/><rect x="-6" y="-5" width="12" height="10" rx="3" fill="#161a1d"/><rect x="-15" y="-7" width="3" height="4" fill="#e5383b"/><rect x="-15" y="3" width="3" height="4" fill="#e5383b"/></g>
                        @foreach ($mapStops as $i => [$x, $y])
                            @php
                                // Étiquette au-dessus d'un sommet du tracé, en dessous d'un creux : les voisines ne se chevauchent pas.
                                $above = $y < ($mapStops[$i - 1][1] ?? PHP_INT_MAX) && $y < ($mapStops[$i + 1][1] ?? PHP_INT_MAX);
                                $anchor = $i === 0 ? 'start' : ($i === $mapCount - 1 && $mapCount > 1 ? 'end' : 'middle');
                                $labelX = $i === 0 ? $x - 30 : ($i === $mapCount - 1 && $mapCount > 1 ? $x + 30 : $x);
                                $labelY = $above ? $y - 50 : $y + 56;
                            @endphp
                            <a @class(['map__stop', 'is-active' => $i === 0]) href="#competences" data-map-stop="{{ $i }}" data-x="{{ $x }}" data-y="{{ $y }}" aria-label="{{ __('portfolio.map.stop', ['n' => $i + 1, 'label' => $categories[$i]['name']]) }}"><circle class="map__pulse" cx="{{ $x }}" cy="{{ $y }}" r="22"/><circle class="map__ring" cx="{{ $x }}" cy="{{ $y }}" r="22"/><text class="map__num" x="{{ $x }}" y="{{ $y + 7 }}" text-anchor="middle">{{ $i + 1 }}</text><text class="map__label" x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="{{ $anchor }}">{{ $categories[$i]['name'] }}</text><text class="map__tag" x="{{ $labelX }}" y="{{ $labelY + 18 }}" text-anchor="{{ $anchor }}">{{ $i === 0 ? __('portfolio.map.start') : ($i === $mapCount - 1 ? __('portfolio.map.end') : __('portfolio.map.step', ['n' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)])) }}</text></a>
                        @endforeach
                    </svg>
                </div>
                <ol class="skillmap__panels" aria-label="{{ __('portfolio.map.list') }}">
                    @foreach ($categories as $category)
                        <li @class(['skillmap__panel', 'is-active' => $loop->first]) data-map-panel="{{ $loop->index }}" data-num="{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}">
                            <p class="mono skillmap__meta">0{{ $loop->iteration }} · {{ __('portfolio.map.count', ['count' => count($category['skills'] ?? [])]) }}</p>
                            <h3>{{ $category['name'] }}</h3>
                            <ul class="tag-row">
                                @foreach ($category['skills'] ?? [] as $skill)<li class="chip" style="--i: {{ $loop->index }}">{{ $skill['name'] }}</li>@endforeach
                            </ul>
                            @php
                                $categorySkillKeys = collect($category['skills'] ?? [])->pluck('key')->all();
                                $relatedProjects = collect($allProjects)->filter(fn ($project): bool => array_intersect($project->skillKeys, $categorySkillKeys) !== [])->values();
                            @endphp
                            @if ($relatedProjects->isNotEmpty())
                                <p class="skillmap__related"><span class="mono">{{ __('portfolio.map.projects') }}</span>
                                    @foreach ($relatedProjects as $related)<a href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $related->slug]) }}">{{ $related->title }}</a>@endforeach
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
@endif

@if ($activityEnabled && (($githubActivity ?? null) || ($contributionGrid ?? null)))
    <section class="section" id="activite" aria-labelledby="activity-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.activity.kicker') }}</p>
            <h2 id="activity-title" data-scroll-reveal>{{ __('portfolio.sections.activity') }}</h2>
            @if ($contributionGrid ?? null)
                @php
                    $grid = $contributionGrid;
                    $sourceLabels = ['github' => 'GitHub', 'gitlab' => 'GitLab'];
                    $sourceSummary = collect($grid->totals)->map(fn (int $count, string $source): string => ($sourceLabels[$source] ?? $source).' '.$count)->implode(', ');
                    $previousMonth = null;
                @endphp
                <figure class="contrib" data-scroll-reveal>
                    <figcaption>
                        <h3 class="activity__title">{{ __('portfolio.activity.grid.title') }}</h3>
                        <p class="mono contrib__total">{{ __('portfolio.activity.grid.total', ['count' => number_format($grid->total, 0, ',', ' ')]) }}</p>
                    </figcaption>
                    <div class="contrib__scroll">
                        <div class="contrib__grid" role="img" aria-label="{{ __('portfolio.activity.grid.aria', ['count' => $grid->total, 'sources' => $sourceSummary]) }}" style="--weeks: {{ count($grid->weeks) }}">
                            @foreach ($grid->weeks as $week)
                                @php
                                    $month = substr($week[0]['date'], 0, 7);
                                    $label = $month !== $previousMonth ? Carbon::parse($week[0]['date'])->locale(app()->getLocale())->isoFormat('MMM') : '';
                                    $previousMonth = $month;
                                @endphp
                                <span class="contrib__month" aria-hidden="true" style="grid-column: {{ $loop->iteration }}">{{ $label }}</span>
                                @foreach ($week as $day)
                                    <span class="contrib__cell" data-level="{{ $day['level'] }}" style="grid-column: {{ $loop->parent->iteration }}; grid-row: {{ $loop->iteration + 1 }}" title="{{ trans_choice('portfolio.activity.grid.day', $day['count'], ['count' => $day['count'], 'date' => Carbon::parse($day['date'])->locale(app()->getLocale())->isoFormat('LL')]) }}"></span>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <p class="contrib__legend hint" aria-hidden="true">{{ __('portfolio.activity.grid.less') }} @foreach ([0, 1, 2, 3, 4] as $level)<span class="contrib__cell" data-level="{{ $level }}"></span>@endforeach {{ __('portfolio.activity.grid.more') }}</p>
                    <details class="contrib__details">
                        <summary>{{ __('portfolio.activity.grid.by_source') }}</summary>
                        <table>
                            <thead><tr><th scope="col">{{ __('portfolio.activity.grid.month') }}</th><th scope="col">{{ __('portfolio.activity.grid.contributions') }}</th></tr></thead>
                            <tbody>@foreach ($grid->months as $row)<tr><th scope="row">{{ Carbon::parse($row['month'].'-01')->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}</th><td>{{ $row['count'] }}</td></tr>@endforeach</tbody>
                        </table>
                    </details>
                    <p class="hint">{{ __('portfolio.activity.grid.note') }}</p>
                </figure>
            @endif
            <div class="activity">
                @if ($githubActivity && $githubActivity->repositories !== [])
                    <div>
                        <h3 class="activity__title">{{ __('portfolio.activity.repositories') }}</h3>
                        <ul class="repos">
                            @foreach ($githubActivity->repositories as $repository)
                                <li class="card card--lift" data-scroll-reveal>
                                    <a class="repos__name" href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $repository['project_slug']]) }}">{{ $repository['name'] }}</a>
                                    @if ($repository['description'])<p>{{ $repository['description'] }}</p>@endif
                                    <p class="repos__meta"><a href="{{ $repository['url'] }}" rel="noopener noreferrer">GitHub ↗</a></p>
                                    <p class="mono repos__meta">
                                        @if ($repository['language']){{ $repository['language'] }} · @endif
                                        @if ($repository['pushed_at']){{ __('portfolio.activity.updated', ['date' => Carbon::parse($repository['pushed_at'])->locale(app()->getLocale())->isoFormat('LL')]) }}@endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($githubActivity && $githubActivity->events !== [])
                    <div>
                        <h3 class="activity__title">{{ __('portfolio.activity.recent') }}</h3>
                        <ol class="events">
                            @foreach ($githubActivity->events as $event)
                                <li>
                                    <a href="{{ $event['url'] }}" rel="noopener noreferrer">{{ trans_choice('portfolio.activity.events.'.$event['type'], $event['count'], ['repo' => $event['repo'], 'title' => $event['title'] ?? '', 'count' => $event['count']]) }}</a>
                                    <time class="mono" datetime="{{ $event['at'] }}">{{ Carbon::parse($event['at'])->locale(app()->getLocale())->diffForHumans() }}</time>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>
            @if ($githubActivity)<p class="hint">{{ __('portfolio.activity.public_only', ['user' => $githubActivity->username]) }}</p>@endif
        </div>
    </section>
@endif

@if ($certifications !== [] || $languages !== [])
    <section class="section" id="formation" aria-labelledby="education-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.education_kicker') }}</p>
            <h2 id="education-title" data-scroll-reveal>{{ __('portfolio.sections.education') }}</h2>
            <div class="credentials">
                @if ($certifications !== [])
                    <div>
                        <h3 class="credentials__title mono" data-scroll-reveal>{{ __('portfolio.sections.certifications') }}</h3>
                        <ul class="plates">
                            @foreach ($certifications as $certification)
                                <li class="plate" data-scroll-reveal>
                                    <span class="plate__year mono">{{ $certification['issued']['year'] ?? '' }}</span>
                                    <span class="plate__name">{{ $certification['name'] }}</span>
                                    @if ($certification['issuer'])<span class="plate__issuer mono">{{ $certification['issuer'] }}</span>@endif
                                    @if (str_contains(strtoupper((string) $certification['name']), 'TOEIC') && is_numeric($certification['result'] ?? null))
                                        @include('portfolio.partials.score', ['label' => 'TOEIC', 'value' => (int) $certification['result'], 'max' => 990])
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($languages !== [])
                    <div>
                        <h3 class="credentials__title mono" data-scroll-reveal>{{ __('portfolio.sections.languages') }}</h3>
                        <ul class="levels">
                            @foreach ($languages as $language)
                                @php
                                    $label = (string) ($language['proficiency_label'] ?? '');
                                    $cefr = strtoupper((string) ($language['cefr_level'] ?? ''));
                                    // Langue maternelle ou bilingue : jauge pleine, même sans niveau CECRL.
                                    $level = $cefrLevels[$cefr] ?? (preg_match('/maternelle|native|bilingu/iu', $label) === 1 ? 6 : null);
                                @endphp
                                <li class="level" data-scroll-reveal>
                                    <div class="level__head">
                                        <span class="level__name">{{ $language['name'] }}</span>
                                        <span class="level__label mono">{{ $label }}@if ($cefr !== '' && strcasecmp($cefr, $label) !== 0) · {{ $cefr }}@endif</span>
                                    </div>
                                    @if ($level)
                                        <div class="level__meter" role="img" aria-label="{{ $language['name'] }} : {{ $label }}">
                                            @foreach (range(1, 6) as $tick)<i @class(['is-on' => $tick <= $level])></i>@endforeach
                                        </div>
                                    @endif
                                    @php
                                        $evidence = (string) ($language['evidence'] ?? '');
                                        $toeic = preg_match('/TOEIC\D{0,12}(\d{3})(?:\s*(?:\/|sur|on|out of)\s*(\d{3}))?/iu', $evidence, $score) === 1 ? ['value' => (int) $score[1], 'max' => (int) ($score[2] ?? 990)] : null;
                                    @endphp
                                    @if ($toeic)
                                        @include('portfolio.partials.score', ['label' => 'TOEIC', 'value' => $toeic['value'], 'max' => $toeic['max']])
                                    @elseif ($evidence !== '')
                                        <p class="level__evidence mono">{{ $evidence }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

@if ($scene !== null)
    @php
        $roomManifest = $scene->toArray();
        $roomManifest['strings'] = [
            'loading' => __('portfolio.room.loading'),
            'unavailable' => __('portfolio.room.unavailable'),
            'close' => __('portfolio.room.close'),
            'greet' => __('portfolio.room.greet'),
            'lowPower' => __('portfolio.room.low_power'),
        ];
    @endphp
    <section class="section room theme-dark" id="atelier" aria-labelledby="room-title" data-room data-state="poster" data-room-label="{{ __('portfolio.room.canvas_label') }}" data-room-dialog-label="{{ __('portfolio.room.dialog_label') }}">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.room.eyebrow') }}</p>
            <h2 id="room-title" data-scroll-reveal>{{ __('portfolio.room.title') }}</h2>
            <p class="lede" data-scroll-reveal>{{ __('portfolio.room.intro') }}</p>
            <div class="room__stage" data-room-stage>
                <div class="room__poster" data-room-poster>
                    @if ($scene->poster)
                        <img src="{{ $scene->poster }}" alt="{{ __('portfolio.room.poster_alt') }}" width="1280" height="720" loading="lazy" decoding="async" class="room__poster-image">
                    @else
                        <span class="room__poster-art" aria-hidden="true"></span>
                    @endif
                    @if ($scene->mobileFallback)
                        <img src="{{ $scene->mobileFallback }}" alt="" width="720" height="720" loading="lazy" decoding="async" class="room__poster-image room__poster-image--mobile" aria-hidden="true">
                    @endif
                </div>
                <div class="room__canvas" data-room-canvas></div>
                <div class="room__bubble" data-room-bubble role="status" aria-live="polite" hidden><span data-room-bubble-text></span><button type="button" aria-label="{{ __('portfolio.room.close') }}">×</button></div>
                <div class="room__controls" data-room-controls hidden>
                    <button class="button button--ghost" type="button" data-room-greet>{{ __('portfolio.room.greet') }}</button>
                    <button class="button button--ghost" type="button" data-room-enter>{{ __('portfolio.room.enter') }}</button>
                    <button class="button button--ghost" type="button" data-room-skip>{{ __('portfolio.room.skip') }}</button>
                </div>
            </div>
            <p class="room__status" data-room-status role="status" hidden></p>
            <button class="button room__load" type="button" data-room-load hidden>{{ __('portfolio.room.load') }}</button>
            <nav class="room__links" aria-label="{{ __('portfolio.room.hotspots_label') }}">
                <ul>
                    @foreach ($scene->hotspots as $hotspot)
                        <li>
                            <a href="{{ $hotspot['href'] }}" data-room-hotspot="{{ $hotspot['key'] }}">
                                <strong>{{ $hotspot['label'] }}</strong>
                                <span>{{ $hotspot['description'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <script type="application/json" data-room-manifest>{!! json_encode($roomManifest, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        </div>
    </section>
@endif

<section class="section" id="interets" aria-labelledby="interests-title" style="padding-top: 0">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.interests_kicker') }}</p>
        <h2 id="interests-title" data-scroll-reveal>{{ __('portfolio.sections.interests') }}</h2>
        <p class="lede" data-scroll-reveal>{{ __('portfolio.sections.interests_intro') }}</p>
        <ol class="strips">
            @foreach (['aviation', 'cars', 'fishing'] as $interest)
                <li class="strip" data-scroll-reveal data-live>
                    <span class="strip__code mono">{{ __('portfolio.interests.'.$interest.'_code') }}</span>
                    <div class="strip__body"><h3>{{ __('portfolio.interests.'.$interest) }}</h3><p>{{ __('portfolio.interests.'.$interest.'_text') }}</p></div>
                    <span class="strip__ref mono" aria-hidden="true">LOG 0{{ $loop->iteration }}</span>
                    <div class="strip__stage" aria-hidden="true">@include('portfolio.partials.interest-art', ['interest' => $interest])</div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="section" id="contact" aria-labelledby="contact-title" style="padding-top: 0">
    <div class="container contact">
        <div>
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.nav.contact') }}</p>
            <h2 id="contact-title" data-scroll-reveal>{{ __('portfolio.sections.contact') }}</h2>
            <p class="lede" data-scroll-reveal>{{ __('portfolio.sections.contact_intro') }}</p>
            @if ($settings?->email || $settings?->phone || ($settings?->socialLinks ?? []) !== [])
                <div class="actions contact__channels" data-scroll-reveal>
                    {{-- Email and phone never appear in clear in the HTML: resources/js/public.ts decodes them on click. --}}
                    @if ($settings?->email)<button type="button" class="button button--ghost" data-contact-reveal="{{ \App\Support\ContactObfuscator::encode('mailto:'.$settings->email, $settings->email) }}"><span>{{ __('portfolio.contact.show_email') }}</span></button>@endif
                    @if ($settings?->phone)<button type="button" class="button button--ghost" data-contact-reveal="{{ \App\Support\ContactObfuscator::encode('tel:'.preg_replace('/\\s+/', '', $settings->phone), $settings->phone) }}"><span>{{ __('portfolio.contact.show_phone') }}</span></button>@endif
                    @foreach ($settings?->socialLinks ?? [] as $platform => $url)
                        <a class="button button--ghost" href="{{ $url }}" rel="noopener noreferrer"><span>{{ ucfirst($platform) }}</span></a>
                    @endforeach
                </div>
            @endif
        </div>
        <div data-scroll-reveal>
            @if (session('contact_success'))
                <p class="alert alert--success" role="status">{{ session('contact_success') }}</p>
            @endif
            @if ($settings?->contactFormEnabled)
                <form class="contact-form" method="post" novalidate data-contact-form data-msg-name="{{ __('portfolio.contact.errors.name_required') }}" data-msg-email-required="{{ __('portfolio.contact.errors.email_required') }}" data-msg-email-invalid="{{ __('portfolio.contact.errors.email_invalid') }}" data-msg-message="{{ __('portfolio.contact.errors.message_required') }}" data-msg-message-min="{{ __('portfolio.contact.errors.message_min', ['min' => 10]) }}" action="{{ route('portfolio.contact.submit', ['locale' => $locale]) }}">
                    @csrf
                    <div class="field @error('name') field--error @enderror">
                        <input class="field__input" id="contact-name" name="name" aria-required="true" value="{{ old('name') }}" placeholder=" " required maxlength="120" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                        <label class="field__label" for="contact-name">{{ __('portfolio.contact.name') }} <span class="field__required" aria-hidden="true">*</span></label>
                        @error('name')<p id="contact-name-error" class="error" role="alert">{{ $message }}</p>@enderror
                        <p id="contact-name-error-js" class="error" role="alert" data-error-for="contact-name" hidden></p>
                    </div>
                    <div class="field @error('email') field--error @enderror">
                        <input class="field__input" id="contact-email" name="email" aria-required="true" type="email" value="{{ old('email') }}" placeholder=" " required maxlength="255" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                        <label class="field__label" for="contact-email">{{ __('portfolio.contact.email') }} <span class="field__required" aria-hidden="true">*</span></label>
                        @error('email')<p id="contact-email-error" class="error" role="alert">{{ $message }}</p>@enderror
                        <p id="contact-email-error-js" class="error" role="alert" data-error-for="contact-email" hidden></p>
                    </div>
                    <div class="field @error('subject') field--error @enderror">
                        <input class="field__input" id="contact-subject" name="subject" value="{{ old('subject') }}" placeholder=" " maxlength="160" @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                        <label class="field__label" for="contact-subject">{{ __('portfolio.contact.subject') }}</label>
                        @error('subject')<p id="contact-subject-error" class="error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field @error('message') field--error @enderror">
                        <textarea class="field__input field__input--area" id="contact-message" name="message" aria-required="true" placeholder=" " required minlength="10" maxlength="5000" rows="6" @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
                        <label class="field__label" for="contact-message">{{ __('portfolio.contact.message') }} <span class="field__required" aria-hidden="true">*</span></label>
                        @error('message')<p id="contact-message-error" class="error" role="alert">{{ $message }}</p>@enderror
                        <p id="contact-message-error-js" class="error" role="alert" data-error-for="contact-message" hidden></p>
                    </div>
                    <div class="contact-honeypot" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <p class="hint contact-form__required">{{ __('portfolio.contact.required_note') }}</p>
                    <button class="button" type="submit"><span>{{ __('portfolio.contact.submit') }}</span><span class="button__arrow" aria-hidden="true">→</span></button>
                    <p class="hint">{{ __('portfolio.contact.privacy') }}</p>
                </form>
            @else
                <p class="state">{{ __('portfolio.empty.contact') }}</p>
            @endif
        </div>
    </div>
</section>
@endsection
