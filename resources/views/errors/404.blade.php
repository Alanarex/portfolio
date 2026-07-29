@extends('errors.minimal')
@section('code', '404')
@section('title', app()->getLocale() === 'en' ? 'Page not found' : 'Page introuvable')
@section('message', app()->getLocale() === 'en' ? 'The requested page does not exist or is no longer public.' : 'La page demandée n’existe pas ou n’est plus publique.')
