@extends('layouts.app')

@section('title', 'Panel')

@section('content')
    <h1 class="h3">Panel de {{ auth()->user()->role->label() }}</h1>
    <p class="text-white-50">Sección en construcción.</p>  
@endsection