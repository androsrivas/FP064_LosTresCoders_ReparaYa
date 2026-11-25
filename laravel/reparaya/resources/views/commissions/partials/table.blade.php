@if ($commissions->isEmpty())
    <div class="text-center py-4 text-secondary">
        <p class="mb-0">Aún no hay servicios finalizados con comisión registrada.</p>
    </div>
@else
    {{-- Carrusel de acumulado mensual (bloques de 3 meses, referencia: fecha de finalización) --}}
    @if ($monthlySettlements->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="h6 fw-bold text-uppercase text-white-50 mb-0">Acumulado mensual</h3>
                    <button type="button" id="clearPeriodFilter" class="btn btn-sm btn-outline-warning py-0 px-2 d-none">
                        Ver todos los meses
                    </button>
                </div>

                @if ($monthlySettlements->count() > 3)
                    <div class="btn-group btn-group-sm" role="group" aria-label="Navegación de meses">
                        <button type="button"
                                class="btn btn-outline-secondary text-light"
                                data-bs-target="#monthlySettlementsCarousel"
                                data-bs-slide="prev"
                                title="Meses más recientes">
                            ‹
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary text-light"
                                data-bs-target="#monthlySettlementsCarousel"
                                data-bs-slide="next"
                                title="Meses anteriores">
                            ›
                        </button>
                    </div>
                @endif
            </div>

            <div id="monthlySettlementsCarousel" class="carousel slide" data-bs-ride="false" data-bs-wrap="false">
                <div class="carousel-inner">
                    @foreach ($monthlySettlements->chunk(3) as $chunkIndex => $monthChunk)
                        <div class="carousel-item {{ $chunkIndex === 0 ? 'active' : '' }}">
                            <div class="row g-3">
                                @foreach ($monthChunk as $month)
                                    <div class="col-12 col-md-4">
                                        <div role="button"
                                             tabindex="0"
                                             data-period-card="{{ $month['period'] }}"
                                             class="p-3 rounded border border-secondary bg-dark bg-opacity-50 h-100 period-summary-card"
                                             title="Pulsa para filtrar los servicios de {{ $month['label'] }}">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-bold text-light">{{ $month['label'] }}</span>
                                                <span class="badge bg-secondary">
                                                    {{ $month['count'] }} {{ $month['count'] === 1 ? 'servicio' : 'servicios' }}
                                                </span>
                                            </div>
                                            <div class="fs-5 fw-bold text-yellow">
                                                {{ number_format((float) $month['total'], 2, ',', '.') }} €
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Detalle de servicios finalizados que han generado comisión --}}
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="commissionsTable">
            <thead>
                <tr>
                    <th>Fecha finalización</th>
                    <th>Comunidad</th>
                    <th>Tipo de servicio</th>
                    <th class="text-end">Precio base</th>
                    <th class="text-end">% Comisión</th>
                    <th class="text-end">Importe comisión</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($commissions as $commission)
                    <tr data-period="{{ $commission->finished_at?->format('Y-m') }}">
                        <td>{{ $commission->finished_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="fw-semibold text-white">
                            {{ $commission->incident?->community?->name ?? 'Comunidad eliminada' }}
                        </td>
                        <td>{{ $commission->incident?->speciality?->name ?? 'Servicio' }}</td>
                        <td class="text-end">{{ number_format((float) $commission->base_price, 2, ',', '.') }} €</td>
                        <td class="text-end">{{ number_format((float) $commission->commission_rate, 2, ',', '.') }} %</td>
                        <td class="text-end fw-bold text-yellow">
                            {{ number_format((float) $commission->amount, 2, ',', '.') }} €
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif