<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" id="loginModalLabel">
                    Iniciar sesión en <span class="text-yellow">ReparaYa</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <input type="hidden" name="auth_modal" value="login">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="login_email" class="form-label text-white-50 small">Correo electrónico</label>
                        <input
                            id="login_email"
                            name="email"
                            type="email"
                            value="{{ old('auth_modal') === 'login' ? old('email') : '' }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="ejemplo@correo.com"
                            class="form-control {{ old('auth_modal') === 'login' && $errors->has('email') ? 'is-invalid' : '' }}"
                        >
                        @if (old('auth_modal') === 'login')
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="login_password" class="form-label text-white-50 small">Contraseña</label>
                        <input
                            id="login_password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="******"
                            class="form-control {{ old('auth_modal') === 'login' && $errors->has('password') ? 'is-invalid' : '' }}"
                        >
                        @if (old('auth_modal') === 'login')
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="form-check">
                        <input id="login_remember" name="remember" type="checkbox" class="form-check-input">
                        <label for="login_remember" class="form-check-label small text-white-50">Recordarme</label>
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25 justify-content-between">
                    <span class="small text-white-50">
                        ¿No tienes cuenta?
                        <a href="#registerModal" class="text-yellow text-decoration-none" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">
                            Regístrate
                        </a>
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-yellow btn-sm">Acceder</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>