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
    $sections = array_values($project->sections);
    $gallery = collect($project->media)->filter(
        fn (array $media): bool => in_array($media['kind'] ?? null, ['image', 'screenshot', 'poster'], true),
    )->values();
@endphp
<article class="case container" aria-labelledby="case-title">
    <a class="case__back" href="{{ route('portfolio.projects.index', ['locale' => $locale]) }}">← {{ __('portfolio.projects.back') }}</a>

    <header class="case__header">
        <p class="eyebrow" data-reveal style="--i: 0">{{ __('portfolio.projects.case_study') }} · {{ __('portfolio.lifecycle.'.$project->lifecycleStatus) }}</p>
        <h1 id="case-title" data-reveal style="--i: 1">{{ $project->title }}</h1>
        <p class="lede" data-reveal style="--i: 2">{{ $project->summary }}</p>
    </header>

    @if ($cover)
        <figure class="case__cover" data-reveal style="--i: 3">
            <img src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $cover['delivery_key']]) }}" alt="{{ $cover['alt_text'] }}" width="{{ $cover['width'] ?? 1600 }}" height="{{ $cover['height'] ?? 900 }}" fetchpriority="high" decoding="async">
        </figure>
    @endif

    @php
        $skillMap = collect($skills?->categories ?? [])->flatMap(fn (array $category): array => $category['skills'] ?? [])->pluck('name', 'key');
        $linkedSkills = collect($project->skillKeys)->map(fn (string $key): ?string => $skillMap[$key] ?? null)->filter()->values();
        $renderBody = function (string $body): string {
            $html = '';
            $items = [];
            $flush = function () use (&$html, &$items): void {
                if ($items !== []) {
                    $html .= '<ul class="case__list">'.implode('', array_map(fn (string $item): string => '<li>'.e($item).'</li>', $items)).'</ul>';
                    $items = [];
                }
            };
            foreach (preg_split('/\R/', trim($body)) ?: [] as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (str_starts_with($line, '- ')) {
                    $items[] = substr($line, 2);
                } else {
                    $flush();
                    $html .= '<p>'.e($line).'</p>';
                }
            }
            $flush();

            return $html;
        };
    @endphp

    @if ($project->demoUrl || $project->repositoryUrl)
        <div class="case__links" data-reveal style="--i: 3">
            @if ($project->demoUrl)
                <a class="button" href="{{ $project->demoUrl }}" rel="noopener noreferrer" target="_blank"><span>{{ __('portfolio.projects.demo') }}</span><span class="button__arrow" aria-hidden="true">↗</span></a>
            @endif
            @if ($project->repositoryUrl)
                <a class="button button--ghost" href="{{ $project->repositoryUrl }}" rel="noopener noreferrer" target="_blank"><span>{{ __('portfolio.projects.repository') }}</span><span class="button__arrow" aria-hidden="true">↗</span></a>
            @endif
        </div>
    @endif

    <dl class="case__meta" data-scroll-reveal>
        <div><dt class="mono">{{ __('portfolio.projects.status') }}</dt><dd>{{ __('portfolio.lifecycle.'.$project->lifecycleStatus) }}</dd></div>
        @if ($project->role)<div><dt class="mono">{{ __('portfolio.projects.role') }}</dt><dd>{{ $project->role }}</dd></div>@endif
        @if ($period)<div><dt class="mono">{{ __('portfolio.projects.period') }}</dt><dd>{{ $period }}</dd></div>@endif
        @if ($project->repositoryUrl)<div><dt class="mono">{{ __('portfolio.projects.source') }}</dt><dd><a href="{{ $project->repositoryUrl }}" rel="noopener noreferrer" target="_blank">{{ preg_replace('#^https?://(www\.)?#', '', $project->repositoryUrl) }}</a></dd></div>@endif
    </dl>

    @if ($project->technologies !== [] || $linkedSkills->isNotEmpty())
        <div class="case__stack" data-scroll-reveal>
            @if ($project->technologies !== [])
                <div>
                    <h2 class="mono">{{ __('portfolio.projects.technologies') }}</h2>
                    <ul class="tag-row">@foreach ($project->technologies as $tech)<li class="chip">{{ $tech }}</li>@endforeach</ul>
                </div>
            @endif
            @if ($linkedSkills->isNotEmpty())
                <div>
                    <h2 class="mono">{{ __('portfolio.projects.skills') }}</h2>
                    <ul class="tag-row">@foreach ($linkedSkills as $skillName)<li><a class="chip chip--link" href="{{ route('portfolio.home', ['locale' => $locale]) }}#competences">{{ $skillName }}</a></li>@endforeach</ul>
                </div>
            @endif
        </div>
    @endif

    <div class="case__body">
        @if (count($sections) > 0)
            <nav class="case__toc" aria-label="{{ __('portfolio.projects.toc') }}" data-case-toc>
                @foreach ($sections as $section)
                    <a href="#section-{{ $loop->iteration }}">{{ $section['heading'] }}</a>
                @endforeach
                @if ($gallery->count() > 1)<a href="#galerie">{{ __('portfolio.projects.media') }}</a>@endif
            </nav>
        @endif

        <div class="case__content">
            @forelse ($sections as $section)
                <section id="section-{{ $loop->iteration }}" aria-labelledby="section-{{ $loop->iteration }}-title" data-scroll-reveal>
                    <p class="mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                    <h2 id="section-{{ $loop->iteration }}-title">{{ $section['heading'] }}</h2>
                    {!! $renderBody($section['body']) !!}
                </section>
            @empty
                <p class="state">{{ __('portfolio.projects.case_study_empty') }}</p>
            @endforelse

            @if ($gallery->count() > 1)
                <section id="galerie" aria-labelledby="galerie-title" data-scroll-reveal>
                    <p class="mono">{{ str_pad((string) (count($sections) + 1), 2, '0', STR_PAD_LEFT) }}</p>
                    <h2 id="galerie-title">{{ __('portfolio.projects.media') }}</h2>
                    <ul class="case__gallery">
                        @foreach ($gallery as $media)
                            <li>
                                <img src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $media['delivery_key']]) }}" alt="{{ $media['alt_text'] }}" loading="lazy" decoding="async" width="{{ $media['width'] ?? 1280 }}" height="{{ $media['height'] ?? 800 }}">
                                @if ($media['caption'] ?? null)<span class="hint">{{ $media['caption'] }}</span>@endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    </div>

    @if ($previousProject || $nextProject)
        <nav class="case__pager" aria-label="{{ __('portfolio.projects.other') }}">
            @if ($previousProject)
                <a class="card card--lift" href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $previousProject->slug]) }}" rel="prev"><span class="mono">← {{ __('portfolio.projects.previous') }}</span><h3>{{ $previousProject->title }}</h3></a>
            @endif
            @if ($nextProject)
                <a class="card card--lift case__pager-next" href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $nextProject->slug]) }}" rel="next"><span class="mono">{{ __('portfolio.projects.next') }} →</span><h3>{{ $nextProject->title }}</h3></a>
            @endif
        </nav>
    @endif
</article>
@endsection
