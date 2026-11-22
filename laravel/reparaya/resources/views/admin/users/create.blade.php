@extends('layouts.app')

@section('title', 'Nuevo administrador')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h1 class="h3 mb-4">Nuevo administrador</h1>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                @include('admin.users._form')

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-yellow">Crear administrador</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection