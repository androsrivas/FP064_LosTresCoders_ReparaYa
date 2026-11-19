@extends('layouts.app')

@section('title', 'Registro')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <h1 class="h3 mb-4">Crear una cuenta</h1>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                    @class(['form-control', 'is-invalid' => $errors->has('name')])>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>                        
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    @class(['form-control', 'is-invalid' => $errors->has('email')])>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>                        
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono <span class="text-white-50">(opcional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel"
                    @class(['form-control', 'is-invalid' => $errors->has('phone')])>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>                        
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                    @class(['form-control', 'is-invalid' => $errors->has('password')])>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>                        
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Repite la contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="form-control">
                </div>

                <button type="submit" class="btn btn-yellow-100">Crear cuenta</button>
            </form>

            <p class="mt-3 small">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </div>
    </div>
@endsection