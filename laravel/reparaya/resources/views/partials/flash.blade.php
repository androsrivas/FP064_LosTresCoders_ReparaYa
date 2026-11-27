@foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }} alert-flash alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <span>{{ $type === 'success' ? '✅' : '⚠️' }}</span>
            <div>{{ session($key) }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
@endforeach