@extends('errors.minimal')
@section('code', '500')
@section('title', app()->getLocale() === 'en' ? 'Something went wrong' : 'Une erreur est survenue')
@section('message', app()->getLocale() === 'en' ? 'The service cannot complete this request right now. Please try again later.' : 'Le service ne peut pas traiter cette demande actuellement. Réessayez plus tard.')
