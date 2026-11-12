@extends('errors.layout', ['code' => '429', 'title' => 'Trop de requêtes', 'message' => 'Trop de tentatives en peu de temps. Patientez un instant avant de réessayer.', 'retry' => true])
