@extends('portfolio.layout')

@section('content')
    <header class="page-header">
        <p class="eyebrow">{{ __('portfolio.nav.projects') }}</p>
        <h1>{{ __('portfolio.projects.title') }}</h1>
        <p>{{ __('portfolio.projects.intro', ['count' => count($projects)]) }}</p>
    </header>

    @if ($projects !== [])
        <div class="project-grid project-index-grid">
            @foreach ($projects as $project)
                @include('portfolio.partials.project-card', ['project' => $project, 'index' => $loop->index])
            @endforeach
        </div>
    @else
        <div class="empty-state page-empty">{{ __('portfolio.projects.empty') }}</div>
    @endif
@endsection
