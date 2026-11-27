@extends('layouts.app')

@section('title', 'Editar especialidad')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">Editar especialidad: {{ $speciality->name }}</h1>

            <form method="POST" action="{{ route('admin.specialities.update', $speciality) }}" novalidate>
                @csrf
                @method('PUT')
                @include('admin.specialities._form', ['submitLabel' => 'Actualizar especialidad'])
            </form>
        </div>
    </div>
@endsection