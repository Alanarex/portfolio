@extends('portfolio.layout')

@section('content')
<section class="section page-head">
    <div class="container">
        <p class="eyebrow" data-reveal style="--i: 0">{{ __('portfolio.nav.projects') }}</p>
        <h1 data-reveal style="--i: 1">{{ __('portfolio.projects.title') }}</h1>
        <p class="lede" data-reveal style="--i: 2">{{ __('portfolio.projects.intro', ['count' => count($projects)]) }}</p>

        @if ($projects !== [])
            <div class="project-grid">
                @foreach ($projects as $project)
                    @include('portfolio.partials.project-card', ['project' => $project, 'index' => $loop->index])
                @endforeach
            </div>
        @else
            <p class="state">{{ __('portfolio.projects.empty') }}</p>
        @endif
    </div>
</section>
@endsection
