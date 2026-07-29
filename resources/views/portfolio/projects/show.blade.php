@extends('portfolio.layout')

@section('content')
    @php
        $cover = collect($project->media)->first(
            fn (array $media): bool => in_array($media['kind'] ?? null, ['image', 'screenshot', 'poster'], true),
        );
        $start = $project->dates['start'];
        $end = $project->dates['end'];
        $period = $start['year'] ?? null;
        if ($period && ($project->dates['ongoing'] ?? false)) {
            $period .= ' — '.__('portfolio.projects.ongoing');
        } elseif ($period && ($end['year'] ?? null)) {
            $period .= ' — '.$end['year'];
        }
    @endphp
    <article class="case-study">
        <header class="case-study-hero" data-project-style="{{ crc32($project->slug) % 6 }}">
            @if ($cover)
                <img
                    src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $cover['delivery_key']]) }}"
                    width="{{ $cover['width'] ?? 1600 }}"
                    height="{{ $cover['height'] ?? 900 }}"
                    alt="{{ $cover['alt_text'] }}"
                    fetchpriority="high"
                    decoding="async"
                >
            @endif
            <a class="back-link" href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>{{ __('portfolio.projects.back') }}
            </a>
            <div class="case-study-title">
                <span class="project-status">{{ __('portfolio.lifecycle.'.$project->lifecycleStatus) }}</span>
                <h1>{{ $project->title }}</h1>
                <p>{{ $project->summary }}</p>
            </div>
        </header>

        <div class="case-study-body">
            <dl class="project-facts">
                @if ($project->role)
                    <div><dt>{{ __('portfolio.projects.role') }}</dt><dd>{{ $project->role }}</dd></div>
                @endif
                @if ($period)
                    <div><dt>{{ __('portfolio.projects.period') }}</dt><dd>{{ $period }}</dd></div>
                @endif
                @if ($project->technologies !== [])
                    <div>
                        <dt>{{ __('portfolio.projects.technologies') }}</dt>
                        <dd><ul class="tag-list">@foreach ($project->technologies as $technology)<li>{{ $technology }}</li>@endforeach</ul></dd>
                    </div>
                @endif
            </dl>

            @if ($project->repositoryUrl || $project->demoUrl)
                <div class="case-study-actions">
                    @if ($project->demoUrl)
                        <a class="button button-primary" href="{{ $project->demoUrl }}" rel="noopener noreferrer">
                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>{{ __('portfolio.projects.demo') }}
                        </a>
                    @endif
                    @if ($project->repositoryUrl)
                        <a class="button button-secondary" href="{{ $project->repositoryUrl }}" rel="noopener noreferrer">
                            <i class="fa-brands fa-github" aria-hidden="true"></i>{{ __('portfolio.projects.repository') }}
                        </a>
                    @endif
                </div>
            @endif

            <section aria-labelledby="case-study-sections-title">
                <header class="section-heading"><h2 id="case-study-sections-title">{{ __('portfolio.projects.case_study') }}</h2></header>
                @if ($project->sections !== [])
                    <div class="case-study-sections">
                        @foreach ($project->sections as $section)
                            <section>
                                <span class="section-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div><h3>{{ $section['heading'] }}</h3><p>{{ $section['body'] }}</p></div>
                            </section>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">{{ __('portfolio.projects.case_study_empty') }}</div>
                @endif
            </section>

            @if (count($project->media) > 1)
                <section aria-labelledby="project-gallery-title">
                    <header class="section-heading"><h2 id="project-gallery-title">{{ __('portfolio.projects.media') }}</h2></header>
                    <div class="project-gallery">
                        @foreach ($project->media as $media)
                            @if (in_array($media['kind'] ?? null, ['image', 'screenshot', 'poster'], true))
                                <figure>
                                    <img
                                        src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $media['delivery_key']]) }}"
                                        width="{{ $media['width'] ?? 1200 }}"
                                        height="{{ $media['height'] ?? 675 }}"
                                        alt="{{ $media['alt_text'] }}"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                    @if ($media['caption'] ?? null)<figcaption>{{ $media['caption'] }}</figcaption>@endif
                                </figure>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </article>
@endsection
