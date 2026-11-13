@php
    $cover = collect($project->media)->first(
        fn (array $media): bool => in_array($media['kind'] ?? null, ['image', 'screenshot', 'poster'], true),
    );
    $url = route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]);
@endphp
<article class="card card--lift project-card" data-scroll-reveal>
    @if ($cover)
        <div class="project-card__cover">
            <img src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $cover['delivery_key']]) }}" width="{{ $cover['width'] ?? 1200 }}" height="{{ $cover['height'] ?? 675 }}" alt="{{ $cover['alt_text'] }}" loading="lazy" decoding="async">
        </div>
    @endif
    <div class="project-card__top">
        @include('portfolio.partials.project-logo', ['project' => $project, 'class' => 'logo--mark'])
        <span class="tag">{{ strtoupper(__('portfolio.lifecycle.'.$project->lifecycleStatus)) }}</span>
    </div>
    <h3><a class="project-card__link" href="{{ $url }}">{{ $project->title }}</a></h3>
    <p>{{ $project->summary }}</p>
    @if ($project->technologies !== [])
        <ul class="tag-row" aria-label="{{ __('portfolio.projects.technologies') }}">
            @foreach (array_slice($project->technologies, 0, 5) as $technology)<li class="chip">{{ $technology }}</li>@endforeach
        </ul>
    @endif
    <p class="project-card__actions">
        <a href="{{ $url }}">{{ __('portfolio.projects.view') }} <span aria-hidden="true">→</span></a>
        @if ($project->repositoryUrl)<a class="mono" href="{{ $project->repositoryUrl }}" rel="noopener noreferrer">{{ __('portfolio.projects.repository') }}</a>@endif
        @if ($project->demoUrl)<a class="mono" href="{{ $project->demoUrl }}" rel="noopener noreferrer">{{ __('portfolio.projects.demo') }}</a>@endif
    </p>
</article>
