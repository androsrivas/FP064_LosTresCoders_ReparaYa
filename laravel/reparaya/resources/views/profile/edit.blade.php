@extends('layouts.app')

@section('title', 'Editar perfil')

@section('content')
    <div class="row justify-content-center g-4">
        {{-- Datos personales --}}
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">Datos personales</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                                @class(['form-control', 'is-invalid' => $errors->has('name')])>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                                @class(['form-control', 'is-invalid' => $errors->has('email')])>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono <span class="text-white-50">(opcional)</span></label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel"
                                @class(['form-control', 'is-invalid' => $errors->has('phone')])>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-yellow">Guardar cambios</button>
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-yellow">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Cambio de contraseña --}}
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">Cambiar contraseña</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña actual</label>
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" 
                                @class(['form-control', 'is-invalid' => $errors->updatePassword->has('current_password')])>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                @class(['form-control', 'is-invalid' => $errors->updatePassword->has('password')])>
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Repite la nueva contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-yellow">Cambiar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection