@extends('portfolio.layout')

@section('content')
    <article class="legal-page">
        <p class="eyebrow">{{ __('portfolio.footer.privacy') }}</p>
        <h1>{{ __('portfolio.privacy.title') }}</h1>
        <p>{{ __('portfolio.privacy.intro') }}</p>
        <section>
            <h2>{{ __('portfolio.privacy.contact_title') }}</h2>
            <p>{{ __('portfolio.privacy.contact_text') }}</p>
        </section>
        <section>
            <h2>{{ __('portfolio.privacy.analytics_title') }}</h2>
            <p>{{ __('portfolio.privacy.analytics_text') }}</p>
        </section>
        <section>
            <h2>{{ __('portfolio.privacy.visibility_title') }}</h2>
            <p>{{ __('portfolio.privacy.visibility_text') }}</p>
        </section>
    </article>
@endsection
