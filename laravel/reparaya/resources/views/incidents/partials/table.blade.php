@php
    $isManager = auth()->user()->hasRole(\App\Enums\UserRole::PropertyManager);
    $cancellable = [\App\Enums\IncidentStatus::Assigned, App\Enums\IncidentStatus::Pending];
@endphp

@if ($incidents->isEmpty())
    <div class="text-center py-5">
        <p class="text-white-50 mb-3">Todavía no has registrado ninguna incidencia.</p>
        <button type="button" class="btn btn-yellow btn-sm fw-bold"
            data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
            Solicitar un servicio
        </button>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Referencia</th>
                    @if ($isManager)
                        <th>Comunidad</th>
                    @endif
                    <th>Fecha</th>
                    <th>Urgencia</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($incidents as $incident)
                    <tr>
                        <td>
                            <strong class="text-nowrap">{{ $incident->reference }}</strong><br>
                            <small class="text-white-50">{{ Str::limit($incident->description, 45) }}</small>
                        </td>
                        @if ($isManager)
                            <td>{{ $incident->community->name ?? '-' }}</td>
                        @endif
                        <td class="text-nowrap">{{ $incident->service_date->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge badge-status badge-urgencia-{{ Str::slug($incident->urgency->value) }}">
                                {{ $incident->urgency->value }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-status badge-status-{{ Str::slug($incident->status->value) }}">
                                {{ $incident->status->value }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <div class="d-inline-flex gap-1">
                                @if (in_array($incident->status, $cancellable, true))
                                    <form action="{{ route('incidents.cancel', $incident) }}" method="POST"
                                        class="d-inline"
                                        data-confirm="¿Seguro que quieres cancelar la incidencia {{ $incident->reference }}?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-outline-yellow btn-sm"
                                    data-detail-url="{{ route('incidents.show', $incident) }}">
                                    Ver
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif