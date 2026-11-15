<header class="sticky-top w-100 bg-dark border-bottom border-secondary border-opacity-25">
    <nav class="navbar navbar-expand-lg px-4 py-2" style="background-color: var(--bg-main);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-yellow d-flex align-items-center gap-2" href="{{ route('home') }}"><span style="font-size: 1.4rem;">🛠️</span> <span>Repara<span class="text-white">Ya</span></span></a>

            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-control="mainNav" aria-expanded="false" aria-label="Mostrar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        @foreach (auth()->user()->role->menu()  as [$label, $routeName])
                            @continue(! Route::has($routeName))
                            @php($active = request()->routeIs($routeName))
                            <li class="nav-item">
                                <a @class(['nav-link', 'text-white fw-bold active' => $active, 'text-white-50' => ! $active])
                                    href="{{ route($routeName) }}" @if ($active) aria-current="pager" @endif>
                                    {{ label }}
                                </a>
                            </li>                            
                        @endforeach
                    @endauth
                </ul>

                <div class="d-flex align-items-center gap-3">
                    @auth
                        <span class="text-white-50 small">
                            Hola <strong class="text-white">{{ auth()->user()->name }}</strong>
                            ({{ auth()->user()->role->label() }})
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-yellow btn-sm">Cerrar sesión</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-yellow btn-sm">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-yellow btn-sm">Registrarse</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>