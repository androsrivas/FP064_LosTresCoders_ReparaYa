<div>
    <h3 style="margin-top:0;">{{ $incident->reference }} — {{ $incident->status->value }}</h3>
    <p><strong>Especialidad:</strong> {{ $incident->speciality->name ?? 'N/A' }} | <strong>Urgencia:</strong> {{ $incident->urgency->value }}</p>
    <p><strong>Fecha:</strong> {{ $incident->service_date->format('d/m/Y H:i') }} | <strong>Dirección:</strong> {{ $incident->address }}</p>
    <p><strong>Descripción:</strong> {{ $incident->description }}</p>

    <h4>Historial de estados ({{ $incident->statusLogs->count() }})</h4>
    <ul>
        @foreach ($incident->statusLogs as $log)
            <li>
                <strong>{{ $log->new_status->value }}</strong>
                ({{ $log->created_at?->format('d/m/Y H:i') }}) — {{ $log->note }}
            </li>
        @endforeach
    </ul>
</div>