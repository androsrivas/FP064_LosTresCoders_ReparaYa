@@extends('layouts.app')

@@section('title', 'Inicio')

@section('content')
    <div class="text-center py-5">
        <h1 class="display-5 fw-bold">{{ config('reparaya.name') }}</h1>
        <p class="lead text-white-50">Gestión de reparaciones del hogar.</p>
        <p class="small text-white-50">Código del grupo: <strong>{{ config('reparaya.group_code') }}</strong></p>
        
        @@guest
        <a href="{{ route('login') }}" class="btn btn-yellow">Iniciar sesión</a>
        @else 
        <a href="{{ route(auth()->user()->role->homeRoute()) }}" class="btn btn-yellow">Ir a mi panel</a>
        @endguest
    </div>
@endsection