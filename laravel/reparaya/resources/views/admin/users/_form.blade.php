{{-- Campos compartidos por alta y edición. $user es null en el alta. --}}
@php($user ??= null)
<div class="mb-3">
    <label for="name" class="form-label">Nombre</label>
    <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus
        @class(['form-control', 'is-invalid' => $errors->has('name')])>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Correo electrónico</label>
    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required
        @class(['form-control', 'is-invalid' => $errors->has('email')])>
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="phone" class="form-label">Teléfono <span class="text-white-50">(opcional)</span></label>
    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user?->phone) }}"
        @class(['form-control', 'is-invalid' => $errors->has('phone')])>
    @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="password" class="form-label">
        {{ $user ? 'Nueva contraseña' : 'Contraseña' }}
        @if ($user)
            <span class="text-white-50">(déjala vacía para mantener la actual)</span>
        @endif
    </label>
    <input id="password" name="password" type="password" autocomplete="new-password" @required(! $user)
        @class(['form-control', 'is-invalid' => $errors->has('password')])>
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="password_confirmation" class="form-label">Repite la contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
        @required(! $user) class="form-control">
</div>
