@php
    $cover = collect($project->media)->first(
        fn (array $media): bool => in_array($media['kind'] ?? null, ['image', 'screenshot', 'poster'], true),
    );
    $status = __('portfolio.lifecycle.'.$project->lifecycleStatus);
@endphp
<article class="project-card" data-project-style="{{ $index % 6 }}">
    <div class="project-cover">
        @if ($cover)
            <img
                src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $cover['delivery_key']]) }}"
                width="{{ $cover['width'] ?? 1200 }}"
                height="{{ $cover['height'] ?? 675 }}"
                alt="{{ $cover['alt_text'] }}"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="project-glyph" aria-hidden="true"><i class="fa-solid fa-code"></i></span>
        @endif
        <span class="project-status">{{ $status }}</span>
    </div>
    <div class="project-card-body">
        <div class="project-card-heading">
            <h3><a href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]) }}">{{ $project->title }}</a></h3>
            <span>{{ $project->role }}</span>
        </div>
        <p>{{ $project->summary }}</p>
        @if ($project->technologies !== [])
            <ul class="tag-list" aria-label="{{ __('portfolio.projects.technologies') }}">
                @foreach (array_slice($project->technologies, 0, 6) as $technology)
                    <li>{{ $technology }}</li>
                @endforeach
            </ul>
        @endif
        <div class="project-card-actions">
            <a href="{{ route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]) }}">
                {{ __('portfolio.projects.view') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
            <span>
                @if ($project->repositoryUrl)
                    <a href="{{ $project->repositoryUrl }}" rel="noopener noreferrer" aria-label="{{ __('portfolio.projects.repository') }}">
                        <i class="fa-brands fa-github" aria-hidden="true"></i>
                    </a>
                @endif
                @if ($project->demoUrl)
                    <a href="{{ $project->demoUrl }}" rel="noopener noreferrer" aria-label="{{ __('portfolio.projects.demo') }}">
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                @endif
            </span>
        </div>
    </div>
</article>
