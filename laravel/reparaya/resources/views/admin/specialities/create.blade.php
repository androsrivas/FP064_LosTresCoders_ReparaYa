@extends('layouts.app')

@section('title', 'Nueva especialidad')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">Nueva especialidad</h1>

            <form method="POST" action="{{ route('admin.specialities.store') }}" novalidate>
                @csrf
                @include('admin.specialities._form', ['submitLabel' => 'Guardar especialidad'])
            </form>
        </div>
    </div>
@endsection