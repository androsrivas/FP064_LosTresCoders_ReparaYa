@php
    /** @var \App\Models\User $user */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Incident> $incidents */
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Perfil - ReparaYa</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .badge-role { background: #facc15; color: #0f172a; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 6px; font-size: 0.8rem; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: 1fr 1.4fr; gap: 1.5rem; align-items: start; }
        .card { background: #1e293b; border-top: 3px solid #facc15; border-radius: 10px; padding: 1.5rem; }
        .card h2 { margin-top: 0; color: #facc15; font-size: 1.1rem; }
        .field-box { background: #263348; border: 1px solid #334155; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 0.85rem; }
        .field-box small { display: block; color: #94a3b8; font-size: 0.8rem; margin-bottom: 0.25rem; }
        .field-box strong { font-size: 1rem; }
        .btn { background: #facc15; color: #0f172a; border: none; padding: 0.65rem 1.2rem; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn-secondary { background: #334155; color: #f8fafc; }
        .alert-success { background: #064e3b; border: 1px solid #10b981; color: #a7f3d0; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-error { background: #7f1d1d; border: 1px solid #ef4444; color: #fecaca; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .incident-item { background: #263348; border: 1px solid #334155; border-radius: 8px; padding: 0.9rem 1rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
        .tag { font-size: 0.75rem; padding: 0.2rem 0.55rem; border-radius: 999px; background: #334155; margin-left: 0.4rem; }
        dialog { background: #1e293b; color: #f8fafc; border: 1px solid #475569; border-radius: 10px; padding: 1.5rem; width: 100%; max-width: 460px; }
        dialog::backdrop { background: rgba(0, 0, 0, 0.75); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.85rem; color: #cbd5e1; margin-bottom: 0.35rem; }
        .form-group input { width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #475569; color: #f8fafc; padding: 0.6rem 0.75rem; border-radius: 6px; }
        .error-text { color: #f87171; font-size: 0.8rem; margin-top: 0.25rem; display: block; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Mi <span style="color:#facc15;">Perfil</span></h1>
        <span class="badge-role">{{ $user->role->value ?? 'USUARIO' }}</span>
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="grid">
        {{-- Columna izquierda: Datos de usuario --}}
        <div class="card">
            <h2>Datos de usuario</h2>

            <div class="field-box">
                <small>Nombre completo:</small>
                <strong>{{ $user->name }}</strong>
            </div>

            <div class="field-box">
                <small>Correo electrónico:</small>
                <strong>{{ $user->email }}</strong>
            </div>

            <div class="field-box">
                <small>Teléfono de contacto:</small>
                <strong>{{ $user->phone ?? 'No especificado' }}</strong>
            </div>

            <div style="text-align: right; margin-top: 1.25rem;">
                <button type="button" class="btn" onclick="document.getElementById('editProfileModal').showModal()">
                    Editar perfil
                </button>
            </div>
        </div>

        {{-- Columna derecha: Incidencias asociadas --}}
        <div class="card">
            <h2>Incidencias asociadas al cliente ({{ $incidents->count() }})</h2>

            @forelse ($incidents as $incident)
                <div class="incident-item">
                    <div>
                        <strong style="color:#facc15;">{{ $incident->reference }}</strong>
                        <span class="tag">{{ $incident->urgency->value }}</span>
                        <span class="tag">{{ $incident->status->value }}</span>
                        <div style="font-size: 0.85rem; color: #cbd5e1; margin-top: 0.35rem;">
                            Especialidad: <strong>{{ $incident->speciality->name ?? '—' }}</strong>
                        </div>
                    </div>
                    <button type="button" class="btn" onclick="openDetail('{{ route('incidents.show', $incident) }}')">
                        Ver Detalle
                    </button>
                </div>
            @empty
                <p style="color: #94a3b8;">Este usuario aún no tiene incidencias registradas.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal de Edición de Perfil --}}
<dialog id="editProfileModal">
    <h2 style="margin-top:0; color:#facc15;">Editar perfil</h2>

    @if ($errors->any())
        <div class="alert-error">
            Revisa los campos marcados antes de guardar.
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="name">Nombre completo *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="phone">Teléfono de contacto</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
            @error('phone') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <hr style="border-color: #334155; margin: 1.25rem 0;">
        <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0;">
            Cambio de contraseña (deja estos campos en blanco si no deseas cambiarla):
        </p>

        <div class="form-group">
            <label for="current_password">Contraseña actual</label>
            <input type="password" id="current_password" name="current_password" autocomplete="new-password">
            @error('current_password') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password" autocomplete="new-password">
            @error('password') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Repite la nueva contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editProfileModal').close()">
                Cancelar
            </button>
            <button type="submit" class="btn">Guardar cambios</button>
        </div>
    </form>
</dialog>

{{-- Modal AJAX para reutilizar incidents.show --}}
<dialog id="detailModal">
    <div id="detailContent">Cargando...</div>
    <div style="text-align:right; margin-top:1rem;">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('detailModal').close()">Cerrar</button>
    </div>
</dialog>

<script>
    async function openDetail(url) {
        const modal = document.getElementById('detailModal');
        const content = document.getElementById('detailContent');
        content.innerHTML = 'Cargando detalle...';
        modal.showModal();

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            content.innerHTML = await res.text();
        } catch (err) {
            content.innerHTML = `<p style="color:#f87171;">Error al cargar detalle: ${err.message}</p>`;
        }
    }

    // Si hubo errores de validación al guardar, reabre el modal automáticamente
    @if ($errors->any())
        document.getElementById('editProfileModal').showModal();
    @endif
</script>
</body>
</html>