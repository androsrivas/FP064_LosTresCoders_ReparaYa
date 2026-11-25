@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Mi perfil</span>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-yellow btn-sm">Editar</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>
    
                        <dt class="col-sm-4">Correo electrónico</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
    
                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">{{ $user->phone ?? 'No indicado'  }}</dd>
    
                        <dt class="col-sm-4">Rol</dt>
                        <dd class="col-sm-8 mb-0">{{ $user->role->label() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection