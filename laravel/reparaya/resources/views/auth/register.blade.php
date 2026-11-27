<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" id="registerModalLabel">
                    Crear cuenta en <span class="text-yellow">ReparaYa</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                <input type="hidden" name="auth_modal" value="register">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="register_name" class="form-label text-white-50 small">Nombre</label>
                        <input
                            id="register_name"
                            name="name"
                            type="text"
                            value="{{ old('auth_modal') === 'register' ? old('name') : '' }}"
                            required
                            autocomplete="name"
                            class="form-control {{ old('auth_modal') === 'register' && $errors->has('name') ? 'is-invalid' : '' }}"
                        >
                        @if (old('auth_modal') === 'register')
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="register_email" class="form-label text-white-50 small">Correo electrónico</label>
                        <input
                            id="register_email"
                            name="email"
                            type="email"
                            value="{{ old('auth_modal') === 'register' ? old('email') : '' }}"
                            required
                            autocomplete="email"
                            placeholder="ejemplo@correo.com"
                            class="form-control {{ old('auth_modal') === 'register' && $errors->has('email') ? 'is-invalid' : '' }}"
                        >
                        @if (old('auth_modal') === 'register')
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="register_phone" class="form-label text-white-50 small">
                            Teléfono <span class="text-white-50">(opcional)</span>
                        </label>
                        <input
                            id="register_phone"
                            name="phone"
                            type="tel"
                            value="{{ old('auth_modal') === 'register' ? old('phone') : '' }}"
                            autocomplete="tel"
                            class="form-control {{ old('auth_modal') === 'register' && $errors->has('phone') ? 'is-invalid' : '' }}"
                        >
                        @if (old('auth_modal') === 'register')
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="register_password" class="form-label text-white-50 small">Contraseña</label>
                        <input
                            id="register_password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="form-control {{ old('auth_modal') === 'register' && $errors->has('password') ? 'is-invalid' : '' }}"
                        >
                        @if (old('auth_modal') === 'register')
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-0">
                        <label for="register_password_confirmation" class="form-label text-white-50 small">Repite la contraseña</label>
                        <input
                            id="register_password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="form-control"
                        >
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25 justify-content-between">
                    <span class="small text-white-50">
                        ¿Ya tienes cuenta?
                        <a href="#loginModal" class="text-yellow text-decoration-none" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Inicia sesión
                        </a>
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-yellow btn-sm">Crear cuenta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>