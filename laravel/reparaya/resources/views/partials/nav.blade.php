<header class="sticky-top w-100 bg-dark border-bottom border-secondary border-opacity-25" style="z-index: 1030;">
    <nav class="navbar navbar-expand-lg border-bottom border-secondary border-opacity-25 px-4 py-2 w-100"
        style="background-color: var(--bg-main);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-yellow d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span style="font-size: 1.4rem;">🛠️</span> <span>Repara<span class="text-white">Ya</span></span>
            </a>

            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Mostrar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'text-white fw-bold active' : 'text-white-50' }}"
                            href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>
                            Home
                        </a>
                    </li>

                    @auth
                        @foreach (auth()->user()->role->menu() as $item)
                            @php
                                $label = $item[0];
                                $routeName = $item[1];
                                $active = request()->routeIs($routeName);
                            @endphp
                            @continue(!Route::has($routeName) || $routeName === 'home')
                            <li class="nav-item">
                                <a class="nav-link {{ $active ? 'text-white fw-bold active' : 'text-white-50' }}"
                                    href="{{ route($routeName) }}" @if ($active) aria-current="page" @endif>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    @endauth
                </ul>

                <div class="d-flex align-items-center gap-3">
                    @auth
                        <a href="{{ route('profile.show') }}" title="Mi perfil"
                            class="text-decoration-none small {{ request()->routeIs('profile.*') ? 'text-white' : 'text-white-50' }}">
                            Hola, <strong class="text-white">{{ auth()->user()->name }}</strong>
                            <span class="text-white-50">({{ auth()->user()->role->label() }})</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-yellow btn-sm">Cerrar sesión</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-yellow btn-sm" data-bs-toggle="modal"
                            data-bs-target="#loginModal">
                            Iniciar sesión
                        </button>
                        <button type="button" class="btn btn-outline-yellow btn-sm" data-bs-toggle="modal"
                            data-bs-target="#registerModal">
                            Registrarse
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>

@guest
    @include('auth.login')
    @include('auth.register')

    @php
        $modalToOpen = session('open_modal')
            ?? ($errors->any() ? (old('auth_modal') === 'register' ? 'register' : 'login') : null);
    @endphp

    @if ($modalToOpen)
        <div data-auth-modal="{{ $modalToOpen }}" hidden></div>
        @push('scripts')
            <script src="{{ asset('js/auth-modal.js') }}" defer></script>
        @endpush
    @endif
@endguest