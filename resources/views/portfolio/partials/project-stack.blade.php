@php
    $cover = collect($project->media)->first(
        fn (array $media): bool => in_array($media['kind'] ?? null, ['image', 'screenshot', 'poster'], true),
    );
    $url = route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]);
    $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
@endphp
<article class="stack__card" style="--n: {{ $index }}">
    <div class="stack__body">
        <div>
            @if ($cover)@include('portfolio.partials.project-logo', ['project' => $project, 'class' => 'logo--mark'])@endif
            <h3><a class="stack__link" href="{{ $url }}">{{ $project->title }}</a></h3>
            <p class="stack__summary">{{ $project->summary }}</p>
            @if ($project->technologies !== [])
                <ul class="tag-row" aria-label="{{ __('portfolio.projects.technologies') }}">
                    @foreach (array_slice($project->technologies, 0, 6) as $technology)<li class="chip">{{ $technology }}</li>@endforeach
                </ul>
            @endif
        </div>
        <div class="stack__footer">
            <span class="tag">{{ strtoupper(__('portfolio.lifecycle.'.$project->lifecycleStatus)) }}</span>
            <a class="stack__cta" href="{{ $url }}">{{ __('portfolio.projects.view') }} <span aria-hidden="true">→</span></a>
        </div>
    </div>
    <div class="stack__visual">
        @if ($cover)
            <img src="{{ route('portfolio.media.show', ['locale' => $locale, 'deliveryKey' => $cover['delivery_key']]) }}" width="{{ $cover['width'] ?? 1200 }}" height="{{ $cover['height'] ?? 675 }}" alt="{{ $cover['alt_text'] }}" loading="lazy" decoding="async">
        @else
            @include('portfolio.partials.project-logo', ['project' => $project, 'class' => 'logo--stack'])
        @endif
    </div>
</article>
