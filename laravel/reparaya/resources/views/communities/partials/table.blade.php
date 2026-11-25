@if ($communities->isEmpty())
    <div class="text-center py-4 text-secondary">
        <p class="mb-0">Aún no hay comunidades registradas.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Zona</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($communities as $community)
                    <tr class="js-search-row">
                        <td class="fw-semibold text-white">{{ $community->name }}</td>
                        <td>{{ $community->address }}</td>
                        <td>{{ $community->zone?->name ?? 'Sin zona' }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning js-edit-community"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editCommunityModal"
                                        data-action="{{ route('communities.update', $community) }}"
                                        data-id="{{ $community->id }}"
                                        data-name="{{ $community->name }}"
                                        data-address="{{ $community->address }}"
                                        data-zone-id="{{ $community->zone_id }}">
                                    Editar
                                </button>

                                <form action="{{ route('communities.destroy', $community) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar la comunidad «{{ addslashes($community->name) }}»?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif