@extends('portfolio.layout')

@section('content')
<article class="legal container" aria-labelledby="legal-title">
    <header>
        <p class="eyebrow" data-reveal style="--i: 0">{{ __('portfolio.footer.privacy') }}</p>
        <h1 id="legal-title" data-reveal style="--i: 1">{{ __('portfolio.privacy.title') }}</h1>
        <p class="lede" data-reveal style="--i: 2">{{ __('portfolio.privacy.intro') }}</p>
    </header>
    <div class="legal__list">
        @foreach (['contact', 'analytics', 'visibility'] as $topic)
            <section class="legal__item" data-scroll-reveal>
                <h2>{{ __('portfolio.privacy.'.$topic.'_title') }}</h2>
                <p>{{ __('portfolio.privacy.'.$topic.'_text') }}</p>
            </section>
        @endforeach
    </div>
</article>
@endsection
