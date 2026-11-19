@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <h1 class="h3 mb-4">Iniciar sesión</h1>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" 
                    required autofocus autocomplete="email"
                    @class(['form-control', 'is-invalid' => $errors->has('email')])>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                    @class(['form-control', 'is-invalid' => $errors->has('password')])>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check mb-3">
                    <input id="remember" name="remember" type="checkbox" class="form-check-input">
                    <label for="remember" class="form-check-label">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-yellow-100">Entrar</button>
            </form>

            <p class="mt-3 small">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
        </div>
    </div>
@endsection