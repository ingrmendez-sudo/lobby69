<nav class="navbar">
    <div class="container navbar__container">
        <a href="{{ route('landing') }}" class="navbar__brand">
            <img src="{{ asset('img/logo-lobby69_.png') }}" alt="LOBBY69" class="navbar__logo"
                 onerror="this.style.display='none'">
            <span class="navbar__brand-text"></span>
        </a>

        <button class="navbar__toggle" id="navbarToggle" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>

        <ul class="navbar__menu" id="navbarMenu">
            @auth
                <li><a href="{{ route('dashboard') }}" class="navbar__link"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="{{ route('explore') }}" class="navbar__link"><i class="fas fa-search"></i> Explorar</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="navbar__link navbar__link--logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="navbar__link"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                <li><a href="{{ route('invitation.show') }}" class="btn btn--primary btn--sm">Solicitar Invitación</a></li>
            @endauth
        </ul>
    </div>
</nav>
