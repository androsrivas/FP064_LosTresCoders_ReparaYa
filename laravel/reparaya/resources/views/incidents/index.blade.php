<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco de Pruebas CRUD - IncidentController</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #121212; color: #e5e7eb; padding: 2rem; max-width: 1100px; margin: 0 auto; }
        h1, h2 { color: #fff; margin-top: 0; }
        .grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 2rem; align-items: start; }
        .card { background: #1e1e1e; border: 1px solid #333; padding: 1.5rem; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: #9ca3af; }
        input, select, textarea { width: 100%; padding: 0.55rem; background: #121212; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        .error-msg { color: #f87171; font-size: 0.8rem; margin-top: 0.25rem; display: block; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: 0.9rem; }
        .alert-success { background: #14532d; color: #bbf7d0; border: 1px solid #166534; }
        .alert-error { background: #7f1d1d; color: #fecaca; border: 1px solid #991b1b; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        th, td { border-bottom: 1px solid #333; padding: 0.7rem 0.5rem; text-align: left; }
        th { color: #9ca3af; font-weight: 600; }
        .badge { padding: 0.2rem 0.5rem; border-radius: 4px; background: #333; font-size: 0.78rem; }
        .btn { padding: 0.45rem 0.85rem; border-radius: 4px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: #fff; width: 100%; padding: 0.65rem; }
        .btn-secondary { background: #374151; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .actions { display: flex; gap: 0.4rem; }
        dialog { background: #1e1e1e; color: #fff; border: 1px solid #444; border-radius: 8px; padding: 1.5rem; max-width: 600px; width: 90%; }
        dialog::backdrop { background: rgba(0, 0, 0, 0.75); }
    </style>
</head>
<body>

    <h1>Banco de Pruebas: IncidentController (Usuario ID: {{ Auth::id() ?? 'Guest' }})</h1>

    {{-- Feedback de éxito --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Feedback de errores generales (como DomainException en cancel) --}}
    @error('general')
        <div class="alert alert-error">{{ $message }}</div>
    @enderror

    {{-- Resumen de errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Errores detectados ({{ $errors->count() }}):</strong>
            <ul style="margin: 0.4rem 0 0 1.2rem; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid">
        {{-- COLUMNA 1: TEST STORE() + VALIDACIONES --}}
        <div class="card">
            <h2>1. Probar Alta (store)</h2>
            <form action="{{ route('incidents.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="speciality_id">Especialidad *</label>
                    <select name="speciality_id" id="speciality_id">
                        <option value="">-- Selecciona --</option>
                        @foreach ($specialities as $spec)
                            <option value="{{ $spec->id }}" @selected(old('speciality_id') == $spec->id)>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('speciality_id') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="urgency">Urgencia *</label>
                    <select name="urgency" id="urgency">
                        @foreach (\App\Enums\Urgency::cases() as $urg)
                            <option value="{{ $urg->value }}" @selected(old('urgency') === $urg->value)>
                                {{ $urg->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('urgency') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="service_date">Fecha y Hora Servicio *</label>
                    <input type="datetime-local" name="service_date" id="service_date" value="{{ old('service_date') }}">
                    @error('service_date') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="address">Dirección *</label>
                    <input type="text" name="address" id="address" placeholder="Calle Falsa 123" value="{{ old('address') }}">
                    @error('address') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción (mín. 10 chars) *</label>
                    <textarea name="description" id="description" rows="3" placeholder="Detalle de la avería...">{{ old('description') }}</textarea>
                    @error('description') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Enviar POST a store()</button>
            </form>
        </div>

        {{-- COLUMNA 2: TEST INDEX(), SHOW() AJAX Y CANCEL() --}}
        <div class="card">
            <h2>2. Listado ({{ $incidents->count() }}) / Show / Cancel</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ref.</th>
                        <th>Esp. / Urg.</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        <tr>
                            <td>
                                <strong>{{ $incident->reference }}</strong><br>
                                <small style="color:#9ca3af;">{{ Str::limit($incident->description, 30) }}</small>
                            </td>
                            <td>
                                {{ $incident->speciality->name ?? '—' }}<br>
                                <small style="color:#9ca3af;">{{ $incident->urgency->value }}</small>
                            </td>
                            <td>{{ $incident->service_date->format('d/m/Y H:i') }}</td>
                            <td><span class="badge">{{ $incident->status->value }}</span></td>
                            <td class="actions">
                                {{-- Prueba AJAX contra show() --}}
                                <button type="button" class="btn btn-secondary" onclick="openDetail('{{ route('incidents.show', $incident) }}')">
                                    Ver
                                </button>

                                {{-- Prueba PATCH contra cancel() --}}
                                <form action="{{ route('incidents.cancel', $incident) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger" title="Probar cancel()">X</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No hay incidencias para este usuario.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal para probar show() (incidents.partials.detail) --}}
    <dialog id="detailModal">
        <div id="detailContent">Cargando...</div>
        <div style="margin-top: 1rem; text-align: right;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('detailModal').close()">Cerrar</button>
        </div>
    </dialog>

    <script>
        async function openDetail(url) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('detailContent');
            content.innerHTML = 'Cargando parcial...';
            modal.showModal();

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                content.innerHTML = await res.text();
            } catch (err) {
                content.innerHTML = `<p style="color:#f87171;">Error al cargar detail: ${err.message}</p>`;
            }
        }
    </script>
</body>
</html>