@extends('layouts.public')
@section('no_loader', '1')
@section('robots', 'noindex, nofollow')
@section('title', $code.' — '.$title.' — Alan')
@section('description', $message)
@section('content')
<section class="error-page container" aria-labelledby="error-title">
    <p class="error-page__code stroke" aria-hidden="true">{{ $code }}</p>
    <p class="eyebrow">Erreur {{ $code }}</p>
    <h1 id="error-title">{{ $title }}</h1>
    <p class="lede">{{ $message }}</p>
    <div class="error-page__actions">
        <a class="button" href="{{ route('home') }}"><span>Retour à l’accueil</span><span class="button__arrow" aria-hidden="true">→</span></a>
        @isset($retry)
            <a class="button button--ghost" href="{{ url()->current() }}"><span>Réessayer</span></a>
        @endisset
    </div>
</section>
@endsection
