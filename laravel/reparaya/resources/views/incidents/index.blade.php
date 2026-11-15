<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test CRUD Incidencias - ReparaYa</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #eee; padding: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #333; padding: 0.75rem; text-align: left; }
        th { background: #1e1e1e; }
        .badge { padding: 0.2rem 0.5rem; border-radius: 4px; background: #333; }
    </style>
</head>
<body>
    <h1>Incidencias del Cliente (Vista de Testeo)</h1>

    @if (session('success'))
        <p style="color: #4ade80;">{{ session('success') }}</p>
    @endif

    <p>Total encontradas: <strong>{{ $incidents->count() }}</strong></p>

    <table>
        <thead>
            <tr>
                <th>Referencia</th>
                <th>Especialidad</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Urgencia</th>
                <th>Fecha Servicio</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($incidents as $incident)
                <tr>
                    <td>{{ $incident->reference }}</td>
                    <td>{{ $incident->speciality->name ?? 'Sin especialidad' }}</td>
                    <td>{{ $incident->description }}</td>
                    <td><span class="badge">{{ $incident->status->value }}</span></td>
                    <td>{{ $incident->urgency->value }}</td>
                    <td>{{ $incident->service_date->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay incidencias registradas para este usuario (Auth::id() es null o sin registros).</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>