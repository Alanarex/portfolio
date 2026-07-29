@extends('portfolio.layout')

@section('content')
    <section class="hero-section" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow">{{ __('portfolio.hero.eyebrow') }}</p>
            <h1 id="hero-title">{{ $profile?->professionalTitles[0] ?? __('portfolio.hero.title_fallback') }}</h1>
            <p class="hero-summary">{{ $profile?->summary ?? __('portfolio.hero.summary_fallback') }}</p>
            <div class="hero-actions">
                <a href="#projects" class="button button-primary">{{ __('portfolio.hero.projects_cta') }}</a>
                <a href="#contact" class="button button-secondary">{{ __('portfolio.hero.contact_cta') }}</a>
                @if ($cvAvailable)
                    <a href="{{ route('portfolio.cv.download', ['locale' => $locale]) }}" class="button button-secondary">
                        <i class="fa-regular fa-file-pdf" aria-hidden="true"></i>{{ __('portfolio.hero.cv_cta') }}
                    </a>
                @else
                    <span class="button button-muted" aria-disabled="true">{{ __('portfolio.hero.cv_pending') }}</span>
                @endif
            </div>
            @if ($profile?->professionalTitles)
                <ul class="hero-title-list" aria-label="{{ __('portfolio.sections.positioning') }}">
                    @foreach ($profile->professionalTitles as $title)
                        <li>{{ $title }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="hero-portrait">
            <div class="portrait-halo" aria-hidden="true"></div>
            <picture>
                <source type="image/webp" srcset="{{ asset('assets/brand/portrait-hero-320.webp') }} 320w, {{ asset('assets/brand/portrait-hero-640.webp') }} 640w" sizes="(max-width: 760px) 72vw, 360px">
                <img src="{{ asset('assets/brand/portrait-hero-640.webp') }}" width="640" height="800" alt="{{ __('portfolio.hero.portrait_alt') }}" fetchpriority="high" decoding="async">
            </picture>
        </div>
    </section>

    <section class="section-block positioning-section" aria-labelledby="positioning-title">
        <header class="section-heading">
            <p>{{ __('portfolio.sections.positioning_kicker') }}</p>
            <h2 id="positioning-title">{{ __('portfolio.sections.positioning') }}</h2>
        </header>
        @if ($profile)
            <div class="prose-card">
                <p>{{ $profile->biography }}</p>
            </div>
        @else
            <div class="empty-state">{{ __('portfolio.profile.unavailable') }}</div>
        @endif
    </section>

    <section id="projects" class="section-block" aria-labelledby="projects-title">
        <header class="section-heading section-heading-row">
            <div>
                <p>{{ __('portfolio.sections.projects_intro') }}</p>
                <h2 id="projects-title">{{ __('portfolio.sections.projects') }}</h2>
            </div>
            <a href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}">{{ __('portfolio.sections.all_projects') }}</a>
        </header>
        @if ($featuredProjects !== [])
            <div class="project-grid">
                @foreach ($featuredProjects as $project)
                    @include('portfolio.partials.project-card', ['project' => $project, 'index' => $loop->index])
                @endforeach
            </div>
        @else
            <div class="empty-state">{{ __('portfolio.projects.featured_empty') }}</div>
        @endif
    </section>

    <section class="section-block" aria-labelledby="metrics-title">
        <header class="section-heading">
            <p>{{ __('portfolio.sections.metrics_intro') }}</p>
            <h2 id="metrics-title">{{ __('portfolio.sections.metrics') }}</h2>
        </header>
        @if ($verifiedMetrics !== [])
            <div class="metric-grid">
                @foreach ($verifiedMetrics as $metric)
                    <article><i class="fa-solid fa-chart-line" aria-hidden="true"></i><p>{{ $metric }}</p></article>
                @endforeach
            </div>
        @else
            <div class="empty-state">{{ __('portfolio.empty.metrics') }}</div>
        @endif
    </section>

    <section class="section-block three-d-section" aria-labelledby="three-d-title">
        <div>
            <p class="eyebrow">{{ __('portfolio.sections.three_d_pending') }}</p>
            <h2 id="three-d-title">{{ __('portfolio.sections.three_d') }}</h2>
            <p>{{ __('portfolio.sections.three_d_intro') }}</p>
        </div>
        <div class="room-preview" role="img" aria-label="{{ __('portfolio.sections.three_d_pending') }}">
            <span class="room-screen"></span>
            <span class="room-desk"></span>
            <span class="room-avatar"></span>
            <span class="room-plant"></span>
        </div>
    </section>

    <section class="section-block" aria-labelledby="strengths-title">
        <header class="section-heading">
            <h2 id="strengths-title">{{ __('portfolio.sections.strengths') }}</h2>
        </header>
        @if (($skills?->categories ?? []) !== [])
            <div class="strength-grid">
                @foreach (array_slice($skills->categories, 0, 4) as $category)
                    <article>
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        <h3>{{ $category['name'] }}</h3>
                        <p>{{ collect($category['skills'] ?? [])->pluck('name')->take(5)->join(' · ') }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">{{ __('portfolio.empty.skills') }}</div>
        @endif
    </section>

    <section id="experience" class="section-block" aria-labelledby="experience-title">
        <header class="section-heading"><h2 id="experience-title">{{ __('portfolio.sections.experience') }}</h2></header>
        @if (($career?->experiences ?? []) !== [])
            <ol class="timeline">
                @foreach ($career->experiences as $experience)
                    <li>
                        <span class="timeline-dot" aria-hidden="true"></span>
                        <div>
                            <p class="timeline-date">{{ $experience['start']['year'] }} — {{ $experience['is_current'] ? __('portfolio.projects.ongoing') : ($experience['end']['year'] ?? '') }}</p>
                            <h3>{{ $experience['role'] }} · {{ $experience['organization'] }}</h3>
                            @if ($experience['summary'])<p>{{ $experience['summary'] }}</p>@endif
                            @if (($experience['highlights'] ?? []) !== [])
                                <ul>
                                    @foreach (array_slice($experience['highlights'], 0, 4) as $highlight)<li>{{ $highlight }}</li>@endforeach
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <div class="empty-state">{{ __('portfolio.empty.career') }}</div>
        @endif
    </section>

    <section id="skills" class="section-block" aria-labelledby="skills-title">
        <header class="section-heading"><h2 id="skills-title">{{ __('portfolio.sections.skills') }}</h2></header>
        @if (($skills?->categories ?? []) !== [])
            <div class="skills-grid">
                @foreach ($skills->categories as $category)
                    <article>
                        <h3>{{ $category['name'] }}</h3>
                        <ul class="tag-list">
                            @foreach ($category['skills'] ?? [] as $skill)<li>{{ $skill['name'] }}</li>@endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">{{ __('portfolio.empty.skills') }}</div>
        @endif
    </section>

    @if (($settings?->featureFlags['activity'] ?? false) === true)
        <section class="section-block activity-placeholder" aria-labelledby="activity-title">
            <i class="fa-brands fa-github" aria-hidden="true"></i>
            <div>
                <h2 id="activity-title">{{ __('portfolio.sections.activity') }}</h2>
                <p>{{ __('portfolio.sections.activity_pending') }}</p>
            </div>
        </section>
    @endif

    <section id="education" class="section-block" aria-labelledby="education-title">
        <header class="section-heading"><h2 id="education-title">{{ __('portfolio.sections.education') }}</h2></header>
        @if (($career?->education ?? []) !== [] || ($career?->certifications ?? []) !== [])
            <div class="education-grid">
                @foreach ($career?->education ?? [] as $education)
                    <article>
                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        <p>{{ $education['start']['year'] }} — {{ $education['end']['year'] }}</p>
                        <h3>{{ $education['program'] }}</h3>
                        <span>{{ $education['institution'] }}</span>
                    </article>
                @endforeach
                @foreach ($career?->certifications ?? [] as $certification)
                    <article>
                        <i class="fa-solid fa-certificate" aria-hidden="true"></i>
                        <p>{{ $certification['issued']['year'] }}</p>
                        <h3>{{ $certification['name'] }}</h3>
                        <span>{{ $certification['issuer'] }}</span>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">{{ __('portfolio.empty.education') }}</div>
        @endif
    </section>

    <section class="section-block" aria-labelledby="interests-title">
        <header class="section-heading"><h2 id="interests-title">{{ __('portfolio.sections.interests') }}</h2></header>
        <div class="interest-grid">
            <article><i class="fa-solid fa-plane" aria-hidden="true"></i><h3>{{ __('portfolio.interests.aviation') }}</h3><p>{{ __('portfolio.interests.aviation_text') }}</p></article>
            <article><i class="fa-solid fa-car-side" aria-hidden="true"></i><h3>{{ __('portfolio.interests.cars') }}</h3><p>{{ __('portfolio.interests.cars_text') }}</p></article>
            <article><i class="fa-solid fa-fish" aria-hidden="true"></i><h3>{{ __('portfolio.interests.fishing') }}</h3><p>{{ __('portfolio.interests.fishing_text') }}</p></article>
        </div>
    </section>

    <section id="contact" class="section-block contact-section" aria-labelledby="contact-title">
        <div>
            <p class="eyebrow">{{ __('portfolio.nav.contact') }}</p>
            <h2 id="contact-title">{{ __('portfolio.sections.contact') }}</h2>
            <p>{{ __('portfolio.sections.contact_intro') }}</p>
        </div>
        <div>
            @if (session('contact_success'))
                <p class="contact-success" role="status">{{ session('contact_success') }}</p>
            @endif
            @if ($settings?->contactFormEnabled)
                <form class="contact-form" method="post" action="{{ route('portfolio.contact.submit', ['locale' => $locale]) }}">
                    @csrf
                    <div class="contact-field">
                        <label for="contact-name">{{ __('portfolio.contact.name') }}</label>
                        <input id="contact-name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                        @error('name')<p id="contact-name-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="contact-field">
                        <label for="contact-email">{{ __('portfolio.contact.email') }}</label>
                        <input id="contact-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                        @error('email')<p id="contact-email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="contact-field contact-field-wide">
                        <label for="contact-subject">{{ __('portfolio.contact.subject') }}</label>
                        <input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="160" @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                        @error('subject')<p id="contact-subject-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="contact-field contact-field-wide">
                        <label for="contact-message">{{ __('portfolio.contact.message') }}</label>
                        <textarea id="contact-message" name="message" required minlength="10" maxlength="5000" rows="6" @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')<p id="contact-message-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="contact-honeypot" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="contact-field-wide contact-submit">
                        <button class="button button-primary" type="submit">
                            <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>{{ __('portfolio.contact.submit') }}
                        </button>
                        <p>{{ __('portfolio.contact.privacy') }}</p>
                    </div>
                </form>
            @else
                <p class="empty-state">{{ __('portfolio.empty.contact') }}</p>
            @endif
            @if ($settings?->email || $settings?->phone || ($settings?->socialLinks ?? []) !== [])
                <div class="contact-actions">
                    @if ($settings?->email)<a class="button button-secondary" href="mailto:{{ $settings->email }}"><i class="fa-regular fa-envelope" aria-hidden="true"></i>{{ $settings->email }}</a>@endif
                    @if ($settings?->phone)<a class="button button-secondary" href="tel:{{ preg_replace('/\\s+/', '', $settings->phone) }}"><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $settings->phone }}</a>@endif
                    @foreach ($settings?->socialLinks ?? [] as $platform => $url)
                        <a class="button button-secondary" href="{{ $url }}" rel="noopener noreferrer">{{ ucfirst($platform) }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
