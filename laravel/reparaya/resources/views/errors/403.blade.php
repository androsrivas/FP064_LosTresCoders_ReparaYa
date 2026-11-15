@extends('layouts.app')

@section('title', 'Acceso denegado')
    <div class="text-center py-5">
        <h1 class="display-4 fw-bold">403</h1>
        <p class="lead">{{ $exception->getMessage() ?: 'No tienes permiso para acceder a esta sección.' }}</p>
        <a href="{{ auth()->check() ? route(auth()->user()->role->homeRoute()) : route('home') }}">
            Volver
        </a>
    </div>    
@endsection