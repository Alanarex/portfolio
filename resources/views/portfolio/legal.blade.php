@extends('portfolio.layout')

@section('content')
<article class="legal container" aria-labelledby="legal-title">
    <header>
        <p class="eyebrow" data-reveal style="--i: 0">{{ __('portfolio.legal.eyebrow') }}</p>
        <h1 id="legal-title" data-reveal style="--i: 1">{{ __('portfolio.legal.title') }}</h1>
        @if (! empty($legal['updated_at']))
            <p class="hint" data-reveal style="--i: 2">{{ __('portfolio.legal.updated', ['date' => $legal['updated_at']]) }}</p>
        @endif
    </header>

    <section aria-labelledby="legal-publisher">
        <h2 id="legal-publisher">{{ __('portfolio.legal.publisher') }}</h2>
        <dl class="legal__list">
            @if (! empty($legal['publisher_name']))
                <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.name') }}</dt><dd>{{ $legal['publisher_name'] }}</dd></div>
            @endif
            <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.status') }}</dt><dd>{{ $legal['publisher_status'] }}</dd></div>
            @if (! empty($legal['director']))
                <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.director') }}</dt><dd>{{ $legal['director'] }}</dd></div>
            @endif
            @if (! empty($legal['publisher_email']))
                <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.contact') }}</dt><dd><a href="mailto:{{ $legal['publisher_email'] }}">{{ $legal['publisher_email'] }}</a></dd></div>
            @endif
        </dl>
        <p>{{ __('portfolio.legal.publisher_note') }}</p>
    </section>

    <section aria-labelledby="legal-host">
        <h2 id="legal-host">{{ __('portfolio.legal.hosting') }}</h2>
        <dl class="legal__list">
            <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.host') }}</dt><dd>{{ $legal['host_name'] }}</dd></div>
            <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.address') }}</dt><dd>{{ $legal['host_address'] }}</dd></div>
            <div class="legal__item"><dt class="mono">{{ __('portfolio.legal.website') }}</dt><dd><a href="{{ $legal['host_url'] }}" rel="noopener noreferrer">{{ $legal['host_url'] }}</a></dd></div>
        </dl>
    </section>

    <section aria-labelledby="legal-data">
        <h2 id="legal-data">{{ __('portfolio.legal.data') }}</h2>
        <p>{{ __('portfolio.legal.data_1') }}</p>
        <p>{{ __('portfolio.legal.data_2') }}</p>
        <p>{{ __('portfolio.legal.data_3') }}</p>
    </section>

    <section aria-labelledby="legal-ip">
        <h2 id="legal-ip">{{ __('portfolio.legal.ip') }}</h2>
        <p>{{ __('portfolio.legal.ip_text') }}</p>
        <ul class="legal__credits">
            <li>{!! __('portfolio.legal.credit_model', [
                'title' => '<a href="https://sketchfab.com/3d-models/mercedes-amg-gt-roadster-30255e1d942a455e970e7c776998f695" rel="noopener noreferrer">Mercedes Amg GT roadster</a>',
                'author' => '<a href="https://sketchfab.com/Resinnnn" rel="noopener noreferrer">RES1N</a>',
                'license' => '<a href="http://creativecommons.org/licenses/by/4.0/" rel="noopener noreferrer">CC BY 4.0</a>',
            ]) !!}</li>
            <li>{{ __('portfolio.legal.credit_fonts') }}</li>
            <li>{{ __('portfolio.legal.credit_libs') }}</li>
        </ul>
        <p>{{ __('portfolio.legal.trademarks') }}</p>
    </section>

    <p><a href="{{ route('portfolio.home', ['locale' => $locale]) }}">← {{ __('portfolio.legal.back') }}</a></p>
</article>
@endsection
