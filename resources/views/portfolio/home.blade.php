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

    $stops = array_values(array_filter([
        ['id' => 'projets', 'label' => __('portfolio.nav.projects')],
        $experiences !== [] ? ['id' => 'experience', 'label' => __('portfolio.nav.experience')] : null,
        $skillNames->isNotEmpty() ? ['id' => 'competences', 'label' => __('portfolio.nav.skills')] : null,
        $threeDEnabled ? ['id' => 'apercu', 'label' => __('portfolio.nav.three_d')] : null,
        ['id' => 'contact', 'label' => __('portfolio.nav.contact')],
    ]));
    // Positions des arrêts le long du tracé de la carte (de gauche à droite), selon leur nombre.
    $stopSpots = [
        ['x' => 70, 'y' => 430, 'lx' => 40, 'ly' => 488, 'anchor' => 'start'],
        ['x' => 300, 'y' => 300, 'lx' => 300, 'ly' => 250, 'anchor' => 'middle'],
        ['x' => 520, 'y' => 390, 'lx' => 520, 'ly' => 448, 'anchor' => 'middle'],
        ['x' => 730, 'y' => 200, 'lx' => 730, 'ly' => 150, 'anchor' => 'middle'],
        ['x' => 930, 'y' => 100, 'lx' => 930, 'ly' => 160, 'anchor' => 'middle'],
    ];
    $spotIndexes = match (count($stops)) {
        5 => [0, 1, 2, 3, 4],
        4 => [0, 1, 3, 4],
        3 => [0, 2, 4],
        default => [0, 4],
    };
    $mapPath = 'M70 430 C170 430 200 300 300 300 S430 390 520 390 S640 200 730 200 S860 100 930 100';
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
                    @include('portfolio.partials.project-card', ['project' => $project, 'index' => $loop->index])
                @endforeach
            </div>
        @else
            <div class="state" aria-live="polite"><p>{{ __('portfolio.projects.featured_empty') }}</p></div>
        @endif
    </div>
</section>

@if ($proofItems !== [])
    <section class="proof" id="preuves" aria-labelledby="proof-title">
        <div class="proof__steps" aria-hidden="true"><div></div><div></div><div></div></div>
        <div class="proof__stick"><div class="container proof__grid">
            <div>
                <p class="eyebrow">{{ __('portfolio.sections.metrics_intro') }}</p>
                <h2 id="proof-title">{{ __('portfolio.sections.metrics') }}</h2>
                <div class="proof__list">
                    @foreach ($proofItems as $metric)
                        <div class="proof__item"><span class="mono">0{{ $loop->iteration }}</span><p>{{ $metric }}</p></div>
                    @endforeach
                </div>
            </div>
            @include('portfolio.partials.gauge', ['class' => 'gauge--proof', 'label' => __('portfolio.hero.gauge_proof_label')])
        </div></div>
    </section>
@endif

@if ($threeDEnabled)
    <section class="journey theme-dark" id="apercu" aria-labelledby="three-preview-title" data-three-preview data-state="poster">
        <div class="journey__stick">
            <div class="journey__stage" data-three-preview-canvas>
                <div class="journey__poster" aria-hidden="true"><span class="three-preview__road"></span></div>
            </div>
            <div class="journey__scrim" aria-hidden="true"></div>
            <div class="container journey__ui">
                <div class="journey__copy">
                    <p class="eyebrow">{{ __('portfolio.three_d.eyebrow') }}</p>
                    <h2 id="three-preview-title">{{ __('portfolio.three_d.title') }}</h2>
                    <p class="lede">{{ __('portfolio.three_d.intro') }}</p>
                    <button class="button" type="button" data-three-preview-load><span>{{ __('portfolio.three_d.load') }}</span><span class="button__arrow" aria-hidden="true">→</span></button>
                    <p class="error" role="status" data-three-preview-status hidden>{{ __('portfolio.three_d.unavailable') }}</p>
                    <p class="journey__alt"><a href="#projets">{{ __('portfolio.three_d.html_alt') }}</a></p>
                </div>
                <ol class="journey__steps" aria-label="{{ __('portfolio.three_d.steps') }}">
                    @foreach (array_slice($stops, 0, 4) as $stop)
                        <li class="journey__step" data-step-index="{{ $loop->index }}"><a class="journey__link" href="#{{ $stop['id'] }}"><span class="journey__dot" aria-hidden="true"></span><span class="mono">0{{ $loop->iteration }}</span> {{ $stop['label'] }}</a></li>
                    @endforeach
                </ol>
                <p class="journey__drag" aria-hidden="true"><span class="journey__drag-icon">↔</span> {{ __('portfolio.three_d.drag') }}</p>
                <p class="journey__credit hint">{!! __('portfolio.three_d.credit', [
                    'model' => '<a href="https://sketchfab.com/3d-models/mercedes-amg-gt-roadster-30255e1d942a455e970e7c776998f695" rel="noopener">« Mercedes Amg GT roadster »</a>',
                    'author' => '<a href="https://sketchfab.com/Resinnnn" rel="noopener">RES1N</a>',
                    'licence' => '<a href="http://creativecommons.org/licenses/by/4.0/" rel="noopener">CC BY 4.0</a>',
                ]) !!}</p>
            </div>
        </div>
    </section>
@endif

<section class="section" id="plan" aria-labelledby="plan-title">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.map.eyebrow') }}</p>
        <h2 id="plan-title" data-scroll-reveal>{{ __('portfolio.map.title') }}</h2>
        <p class="lede" data-scroll-reveal>{{ __('portfolio.map.intro') }}</p>
        <div class="map theme-dark" data-scroll-reveal>
            <svg viewBox="0 0 1000 520" role="group" aria-label="{{ __('portfolio.map.label', ['count' => count($stops)]) }}">
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
                    <path class="map__draw" pathLength="1" d="{{ $mapPath }}"/>
                </g>
                <g class="map__car"><path d="M10 -5 L70 -20 L70 20 L10 5Z" fill="url(#map-beam)" opacity=".7"/><rect x="-14" y="-8" width="28" height="16" rx="5" fill="#f5f3f4"/><rect x="-6" y="-5" width="12" height="10" rx="3" fill="#161a1d"/><rect x="-15" y="-7" width="3" height="4" fill="#e5383b"/><rect x="-15" y="3" width="3" height="4" fill="#e5383b"/></g>
                @foreach ($stops as $stop)
                    @php($spot = $stopSpots[$spotIndexes[$loop->index]])
                    <a @class(['map__stop', 'map__stop--end' => $loop->last]) href="#{{ $stop['id'] }}" aria-label="{{ __('portfolio.map.stop', ['n' => $loop->iteration, 'label' => $stop['label']]) }}"><circle class="map__ring" cx="{{ $spot['x'] }}" cy="{{ $spot['y'] }}" r="22"/><text class="map__num" x="{{ $spot['x'] }}" y="{{ $spot['y'] + 7 }}" text-anchor="middle">{{ $loop->iteration }}</text><text class="map__label" x="{{ $spot['lx'] }}" y="{{ $spot['ly'] }}" text-anchor="{{ $spot['anchor'] }}">{{ $stop['label'] }}</text><text class="map__tag" x="{{ $spot['lx'] }}" y="{{ $spot['ly'] + 18 }}" text-anchor="{{ $spot['anchor'] }}">{{ $loop->first ? __('portfolio.map.start') : ($loop->last ? __('portfolio.map.end') : __('portfolio.map.step', ['n' => str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)])) }}</text></a>
                @endforeach
            </svg>
        </div>
        <ol class="map__list" aria-label="{{ __('portfolio.map.list') }}">
            @foreach ($stops as $stop)
                <li><a class="map__link" href="#{{ $stop['id'] }}"><b class="map__index">0{{ $loop->iteration }}</b><span class="map__name">{{ $stop['label'] }}</span></a></li>
            @endforeach
        </ol>
    </div>
</section>

@if ($experiences !== [])
    <section class="section" id="experience" aria-labelledby="experience-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.experience_kicker') }}</p>
            <h2 id="experience-title" data-scroll-reveal>{{ __('portfolio.sections.experience') }}</h2>
            <ol class="timeline">
                @foreach ($experiences as $experience)
                    <li class="timeline__item" data-scroll-reveal>
                        <p class="timeline__date mono">{{ $experience['start']['year'] }} — {{ ($experience['is_current'] ?? false) ? __('portfolio.projects.ongoing') : ($experience['end']['year'] ?? '') }}</p>
                        <div class="card timeline__card">
                            <h3>{{ $experience['role'] }}</h3>
                            <p class="timeline__org">{{ $experience['organization'] }}</p>
                            @if (! empty($experience['summary']))<p>{{ $experience['summary'] }}</p>@endif
                            @if (($experience['highlights'] ?? []) !== [])
                                <ul class="checklist">
                                    @foreach ($experience['highlights'] as $highlight)<li>{{ $highlight }}</li>@endforeach
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif

@if (($skills?->categories ?? []) !== [])
    <section class="section" id="competences" aria-labelledby="skills-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.strengths') }}</p>
            <h2 id="skills-title" data-scroll-reveal>{{ __('portfolio.sections.skills') }}</h2>
            <div class="skills">
                @foreach ($skills->categories as $category)
                    <article class="card card--lift" data-scroll-reveal>
                        <span class="number">0{{ $loop->iteration }}</span>
                        <h3>{{ $category['name'] }}</h3>
                        <ul class="tag-row">
                            @foreach ($category['skills'] ?? [] as $skill)<li class="chip">{{ $skill['name'] }}</li>@endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if ($activityEnabled && ($githubActivity ?? null))
    <section class="section" id="activite" aria-labelledby="activity-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.activity.kicker') }}</p>
            <h2 id="activity-title" data-scroll-reveal>{{ __('portfolio.sections.activity') }}</h2>
            <div class="activity">
                @if ($githubActivity->repositories !== [])
                    <div>
                        <h3 class="activity__title">{{ __('portfolio.activity.repositories') }}</h3>
                        <ul class="repos">
                            @foreach ($githubActivity->repositories as $repository)
                                <li class="card card--lift" data-scroll-reveal>
                                    <a class="repos__name" href="{{ $repository['url'] }}" rel="noopener noreferrer">{{ $repository['name'] }}</a>
                                    @if ($repository['description'])<p>{{ $repository['description'] }}</p>@endif
                                    <p class="mono repos__meta">
                                        @if ($repository['language']){{ $repository['language'] }} · @endif
                                        @if ($repository['pushed_at']){{ __('portfolio.activity.updated', ['date' => Carbon::parse($repository['pushed_at'])->locale(app()->getLocale())->isoFormat('LL')]) }}@endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($githubActivity->events !== [])
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
            <p class="hint">{{ __('portfolio.activity.public_only', ['user' => $githubActivity->username]) }}</p>
        </div>
    </section>
@endif

@if ($education !== [] || $certifications !== [] || $languages !== [])
    <section class="section" id="formation" aria-labelledby="education-title" style="padding-top: 0">
        <div class="container">
            <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.education_kicker') }}</p>
            <h2 id="education-title" data-scroll-reveal>{{ __('portfolio.sections.education') }}</h2>
            <div class="grid-4">
                @foreach ($education as $record)
                    <article class="card card--lift" data-scroll-reveal>
                        <p class="mono">{{ $record['start']['year'] ?? '' }} — {{ $record['end']['year'] ?? '' }}</p>
                        <h3>{{ $record['program'] }}</h3>
                        <p class="muted">{{ $record['institution'] }}</p>
                    </article>
                @endforeach
                @foreach ($certifications as $certification)
                    <article class="card card--lift" data-scroll-reveal>
                        <p class="mono">{{ $certification['issued']['year'] ?? '' }}</p>
                        <h3>{{ $certification['name'] }}</h3>
                        <p class="muted">{{ $certification['issuer'] }}</p>
                    </article>
                @endforeach
                @foreach ($languages as $language)
                    <article class="card card--lift" data-scroll-reveal>
                        <p class="mono">{{ strtoupper($language['language_code'] ?? '') }}@if (! empty($language['cefr_level'])) · {{ $language['cefr_level'] }}@endif</p>
                        <h3>{{ $language['name'] }}</h3>
                        <p class="muted">{{ $language['proficiency_label'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="section" id="interets" aria-labelledby="interests-title" style="padding-top: 0">
    <div class="container">
        <p class="eyebrow" data-scroll-reveal>{{ __('portfolio.sections.interests_kicker') }}</p>
        <h2 id="interests-title" data-scroll-reveal>{{ __('portfolio.sections.interests') }}</h2>
        <div class="trio">
            @foreach (['aviation', 'cars', 'fishing'] as $interest)
                <article class="card card--lift" data-scroll-reveal><span class="number">0{{ $loop->iteration }}</span><h3>{{ __('portfolio.interests.'.$interest) }}</h3><p>{{ __('portfolio.interests.'.$interest.'_text') }}</p></article>
            @endforeach
        </div>
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
                <form class="contact-form" method="post" action="{{ route('portfolio.contact.submit', ['locale' => $locale]) }}">
                    @csrf
                    <div class="field @error('name') field--error @enderror">
                        <input class="field__input" id="contact-name" name="name" value="{{ old('name') }}" placeholder=" " required maxlength="120" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                        <label class="field__label" for="contact-name">{{ __('portfolio.contact.name') }}</label>
                        @error('name')<p id="contact-name-error" class="error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field @error('email') field--error @enderror">
                        <input class="field__input" id="contact-email" name="email" type="email" value="{{ old('email') }}" placeholder=" " required maxlength="255" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                        <label class="field__label" for="contact-email">{{ __('portfolio.contact.email') }}</label>
                        @error('email')<p id="contact-email-error" class="error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field @error('subject') field--error @enderror">
                        <input class="field__input" id="contact-subject" name="subject" value="{{ old('subject') }}" placeholder=" " maxlength="160" @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                        <label class="field__label" for="contact-subject">{{ __('portfolio.contact.subject') }}</label>
                        @error('subject')<p id="contact-subject-error" class="error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field @error('message') field--error @enderror">
                        <textarea class="field__input field__input--area" id="contact-message" name="message" placeholder=" " required minlength="10" maxlength="5000" rows="6" @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
                        <label class="field__label" for="contact-message">{{ __('portfolio.contact.message') }}</label>
                        @error('message')<p id="contact-message-error" class="error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="contact-honeypot" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
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
