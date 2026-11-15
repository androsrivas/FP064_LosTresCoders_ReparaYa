@foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }} alert-dismissible dafe show" role="alert">
            {{ session($key) }}
            <button type="button" class="btn btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
@endforeach