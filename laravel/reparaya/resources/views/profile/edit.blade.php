@php
    /** @var \App\Models\User $user */
@endphp

<div class="modal fade" id="editPerfilModal" tabindex="-1" aria-labelledby="editPerfilModalLabel" aria-hidden="true"
    @if ($hasProfileErrors) data-auto-open="true" @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editPerfilModalLabel">
                    Editar <span class="text-yellow">Perfil</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    {{-- Formulario Datos Personales --}}
                    <div class="col-md-6">
                        <h6 class="text-yellow fw-bold mb-3">Datos personales</h6>
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label small">Nombre</label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                                    @class(['form-control', 'is-invalid' => $errors->has('name')])>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label small">Correo electrónico</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                    @class(['form-control', 'is-invalid' => $errors->has('email')])>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label small">Teléfono <span class="text-white-50">(opcional)</span></label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->managementCompany?->contact_phone ?? $user->phone) }}"
                                    @class(['form-control', 'is-invalid' => $errors->has('phone')])>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-outline-yellow btn-sm fw-bold w-100">Guardar datos</button>
                        </form>
                    </div>

                    {{-- Formulario Cambio de Contraseña --}}
                    <div class="col-md-6 border-start border-secondary">
                        <h6 class="text-yellow fw-bold mb-3">Cambiar contraseña</h6>
                        <form method="POST" action="{{ route('profile.password') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label small">Contraseña actual</label>
                                <input id="current_password" name="current_password" type="password" required
                                    @class(['form-control', 'is-invalid' => $errors->updatePassword->has('current_password')])>
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label small">Nueva contraseña</label>
                                <input id="password" name="password" type="password" required
                                    @class(['form-control', 'is-invalid' => $errors->updatePassword->has('password')])>
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label small">Repite la nueva contraseña</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    class="form-control">
                            </div>

                            <button type="submit" class="btn btn-outline-yellow btn-sm fw-bold w-100">Actualizar contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>