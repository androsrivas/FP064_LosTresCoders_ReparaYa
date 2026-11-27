@extends('layouts.app')

@section('title', 'Nuevo técnico')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">Nuevo técnico</h1>

            <form method="POST" action="{{ route('admin.technicians.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="full_name" class="form-label">
                        Nombre completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name"
                        value="{{ old('full_name') }}"
                        class="form-control @error('full_name') is-invalid @enderror" required>
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            Correo electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="text" id="phone" name="phone"
                            value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">
                            Contraseña inicial <span class="text-danger">*</span>
                        </label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">
                            Confirmar contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="speciality_id" class="form-label">
                        Especialidad <span class="text-danger">*</span>
                    </label>
                    <select id="speciality_id" name="speciality_id"
                        class="form-select @error('speciality_id') is-invalid @enderror" required>
                        <option value="">Selecciona una especialidad...</option>
                        @foreach ($specialities as $speciality)
                            <option value="{{ $speciality->id }}" @selected(old('speciality_id') == $speciality->id)>
                                {{ $speciality->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('speciality_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_available"
                        name="is_available" value="1" @checked(old('is_available', true))>
                    <label class="form-check-label" for="is_available">
                        Técnico disponible para asignación de incidencias
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-yellow">Guardar técnico</button>
                    <a href="{{ route('admin.technicians.index') }}" class="btn btn-outline-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection