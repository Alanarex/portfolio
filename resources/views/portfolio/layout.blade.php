<!DOCTYPE html>
<html lang="{{ $locale }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $meta['description'] }}">
    <meta name="theme-color" content="#f8fafc" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b1426" media="(prefers-color-scheme: dark)">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings?->siteName ?? __('portfolio.brand_fallback') }}">
    <meta property="og:locale" content="{{ $locale === 'fr' ? 'fr_FR' : 'en_GB' }}">
    <meta property="og:locale:alternate" content="{{ $locale === 'fr' ? 'en_GB' : 'fr_FR' }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:url" content="{{ $meta['canonical'] }}">
    <meta property="og:image" content="{{ asset('assets/brand/portrait-hero-640.webp') }}">
    <meta property="og:image:alt" content="{{ __('portfolio.hero.portrait_alt') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    <meta name="twitter:image" content="{{ asset('assets/brand/portrait-hero-640.webp') }}">
    @php
        $frUrl = $locale === 'fr' ? $meta['canonical'] : $alternateUrl;
        $enUrl = $locale === 'en' ? $meta['canonical'] : $alternateUrl;
    @endphp
    <link rel="canonical" href="{{ $meta['canonical'] }}">
    <link rel="alternate" hreflang="fr" href="{{ $frUrl }}">
    <link rel="alternate" hreflang="en" href="{{ $enUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $frUrl }}">
    <title>{{ $meta['title'] }}</title>
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script>
        document.documentElement.classList.remove('no-js');
        try {
            const theme = localStorage.getItem('portfolio-theme');
            const dark = theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        } catch (_) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/public.ts'])
</head>
@php
    $brandName = $profile?->displayName ?? $settings?->siteName ?? __('portfolio.brand_fallback');
    $primaryTitle = $profile?->professionalTitles[0] ?? null;
    $allSkillNames = collect($skills?->categories ?? [])
        ->flatMap(fn (array $category): array => $category['skills'] ?? [])
        ->pluck('name')
        ->filter()
        ->take(10);
@endphp
<body class="public-portfolio" data-analytics-driver="null">
    <a href="#main-content" class="skip-link">{{ __('portfolio.skip') }}</a>

    <div class="portfolio-shell">
        <aside class="profile-rail" aria-label="{{ __('portfolio.navigation') }}">
            <div class="rail-brand">
                <a href="{{ route('portfolio.home', ['locale' => $locale]) }}" class="brand-link" aria-label="{{ $brandName }}">
                    <span class="brand-mark" aria-hidden="true">
                        <img src="{{ asset('assets/brand/logo-light-96.webp') }}" class="logo-for-light" width="40" height="40" alt="">
                        <img src="{{ asset('assets/brand/logo-dark-96.webp') }}" class="logo-for-dark" width="40" height="40" alt="">
                    </span>
                    <span>
                        <strong>{{ $brandName }}</strong>
                        <small>{{ $primaryTitle ?? __('portfolio.brand_fallback') }}</small>
                    </span>
                </a>
            </div>

            <div class="rail-scroll">
                <section class="profile-card" aria-labelledby="profile-card-title">
                    <div class="profile-card-cover" aria-hidden="true"></div>
                    <picture class="profile-avatar">
                        <source type="image/webp" srcset="{{ asset('assets/brand/portrait-avatar-160.webp') }} 160w, {{ asset('assets/brand/portrait-avatar-320.webp') }} 320w" sizes="72px">
                        <img src="{{ asset('assets/brand/portrait-avatar-160.webp') }}" width="160" height="160" alt="{{ __('portfolio.hero.portrait_alt') }}" loading="eager" decoding="async">
                    </picture>
                    <div class="profile-card-body">
                        <h2 id="profile-card-title">{{ $brandName }}</h2>
                        @if ($profile)
                            @if ($primaryTitle)<p>{{ $primaryTitle }}</p>@endif
                            @if ($profile->location)<p><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $profile->location }}</p>@endif
                            @if ($profile->availability)
                                <p class="availability"><span aria-hidden="true"></span>{{ $profile->availability }}</p>
                            @endif
                        @else
                            <p>{{ __('portfolio.profile.unavailable') }}</p>
                        @endif
                        <dl class="profile-stats">
                            <div>
                                <dt>{{ __('portfolio.profile.projects_count') }}</dt>
                                <dd>{{ $projectsCount }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('portfolio.nav.skills') }}</dt>
                                <dd>{{ $allSkillNames->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <nav class="rail-navigation" aria-label="{{ __('portfolio.navigation') }}">
                    <a href="{{ route('portfolio.home', ['locale' => $locale]) }}" @class(['is-active' => $page === 'home'])>
                        <i class="fa-solid fa-house" aria-hidden="true"></i><span>{{ __('portfolio.nav.home') }}</span>
                    </a>
                    <a href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}" @class(['is-active' => in_array($page, ['projects', 'project'], true)])>
                        <i class="fa-solid fa-rocket" aria-hidden="true"></i><span>{{ __('portfolio.nav.projects') }}</span>
                    </a>
                    <a href="{{ route('portfolio.home', ['locale' => $locale]).'#experience' }}">
                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i><span>{{ __('portfolio.nav.experience') }}</span>
                    </a>
                    <a href="{{ route('portfolio.home', ['locale' => $locale]).'#skills' }}">
                        <i class="fa-solid fa-brain" aria-hidden="true"></i><span>{{ __('portfolio.nav.skills') }}</span>
                    </a>
                    <a href="{{ route('portfolio.home', ['locale' => $locale]).'#education' }}">
                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i><span>{{ __('portfolio.nav.education') }}</span>
                    </a>
                    <a href="{{ route('portfolio.home', ['locale' => $locale]).'#contact' }}">
                        <i class="fa-regular fa-message" aria-hidden="true"></i><span>{{ __('portfolio.nav.contact') }}</span>
                    </a>
                </nav>
            </div>

            <div class="rail-controls">
                <div class="segmented-control" role="group" aria-label="{{ __('portfolio.theme.label') }}">
                    <button type="button" data-theme-choice="dark" aria-pressed="false">
                        <i class="fa-solid fa-moon" aria-hidden="true"></i>{{ __('portfolio.theme.dark') }}
                    </button>
                    <button type="button" data-theme-choice="light" aria-pressed="false">
                        <i class="fa-solid fa-sun" aria-hidden="true"></i>{{ __('portfolio.theme.light') }}
                    </button>
                </div>
                <a class="language-switch" href="{{ $alternateUrl }}" hreflang="{{ $alternateLocale }}" lang="{{ $alternateLocale }}">
                    <i class="fa-solid fa-language" aria-hidden="true"></i>
                    {{ __('portfolio.language') }}: {{ strtoupper($alternateLocale) }}
                </a>
            </div>
        </aside>

        <main id="main-content" class="main-column" tabindex="-1">
            @yield('content')

            <footer class="site-footer">
                <a href="{{ route('portfolio.home', ['locale' => $locale]) }}" class="footer-brand">
                    <img src="{{ asset('assets/brand/logo-light-96.webp') }}" class="logo-for-light" width="28" height="28" alt="">
                    <img src="{{ asset('assets/brand/logo-dark-96.webp') }}" class="logo-for-dark" width="28" height="28" alt="">
                    <span><strong>{{ $brandName }}</strong><small>© {{ date('Y') }} · {{ __('portfolio.footer.rights') }}</small></span>
                </a>
                <nav aria-label="{{ __('portfolio.navigation') }}">
                    <a href="{{ route('portfolio.home', ['locale' => $locale]) }}">{{ __('portfolio.nav.home') }}</a>
                    <a href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}">{{ __('portfolio.nav.projects') }}</a>
                    <a href="{{ route('portfolio.home', ['locale' => $locale]).'#contact' }}">{{ __('portfolio.nav.contact') }}</a>
                    <a href="{{ route('portfolio.privacy', ['locale' => $locale]) }}">{{ __('portfolio.footer.privacy') }}</a>
                </nav>
            </footer>
        </main>

        <aside class="context-rail" aria-label="{{ __('portfolio.sections.projects') }}">
            @if ($featuredProjects !== [])
                <section class="context-card">
                    <p class="context-label">{{ __('portfolio.sections.projects') }}</p>
                    <ul class="mini-projects">
                        @foreach (array_slice($featuredProjects, 0, 3) as $featuredProject)
                            <li>
                                <span class="mini-project-icon mini-project-icon-{{ $loop->index % 3 }}" aria-hidden="true">
                                    <i class="fa-solid fa-code"></i>
                                </span>
                                <a href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $featuredProject->slug]) }}">
                                    <strong>{{ $featuredProject->title }}</strong>
                                    <span>{{ $featuredProject->summary }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($allSkillNames->isNotEmpty())
                <section class="context-card">
                    <p class="context-label">{{ __('portfolio.sections.skills') }}</p>
                    <div class="skill-cloud">
                        @foreach ($allSkillNames as $skillName)
                            <span>{{ $skillName }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (($settings?->featureFlags['activity'] ?? false) === true)
                <section class="context-card activity-card">
                    <p class="context-label">{{ __('portfolio.sections.activity') }}</p>
                    <p>{{ __('portfolio.sections.activity_pending') }}</p>
                </section>
            @endif
        </aside>
    </div>

    <div class="mobile-preferences">
        <div class="segmented-control" role="group" aria-label="{{ __('portfolio.theme.label') }}">
            <button type="button" data-theme-choice="dark" aria-pressed="false" aria-label="{{ __('portfolio.theme.dark') }}">
                <i class="fa-solid fa-moon" aria-hidden="true"></i>
            </button>
            <button type="button" data-theme-choice="light" aria-pressed="false" aria-label="{{ __('portfolio.theme.light') }}">
                <i class="fa-solid fa-sun" aria-hidden="true"></i>
            </button>
        </div>
        <a href="{{ $alternateUrl }}" hreflang="{{ $alternateLocale }}" lang="{{ $alternateLocale }}" aria-label="{{ __('portfolio.language') }}: {{ strtoupper($alternateLocale) }}">
            {{ strtoupper($alternateLocale) }}
        </a>
    </div>

    <nav class="mobile-navigation" aria-label="{{ __('portfolio.navigation') }}">
        <a href="{{ route('portfolio.home', ['locale' => $locale]) }}" @class(['is-active' => $page === 'home'])>
            <i class="fa-solid fa-house" aria-hidden="true"></i><span>{{ __('portfolio.nav.home') }}</span>
        </a>
        <a href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}" @class(['is-active' => in_array($page, ['projects', 'project'], true)])>
            <i class="fa-solid fa-rocket" aria-hidden="true"></i><span>{{ __('portfolio.nav.projects') }}</span>
        </a>
        <a href="{{ route('portfolio.home', ['locale' => $locale]).'#skills' }}">
            <i class="fa-solid fa-brain" aria-hidden="true"></i><span>{{ __('portfolio.nav.skills') }}</span>
        </a>
        <a href="{{ route('portfolio.home', ['locale' => $locale]).'#contact' }}">
            <i class="fa-regular fa-message" aria-hidden="true"></i><span>{{ __('portfolio.nav.contact') }}</span>
        </a>
    </nav>
</body>
</html>
