<header class="admin-navbar navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn admin-menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Abrir menu">
            <x-admin.icon name="menu" />
        </button>
        <span class="admin-navbar-label">Painel administrativo</span>
    </div>

    <div class="dropdown ms-auto">
        <button class="btn admin-user-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu do usuário">
            <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="admin-user-name d-none d-sm-inline text-truncate">{{ auth()->user()->name }}</span>
            <x-admin.icon name="chevron-down" class="admin-chevron" />
        </button>
        <div class="dropdown-menu dropdown-menu-end admin-user-menu">
            <div class="px-3 py-2">
                <p class="fw-semibold mb-1 text-break">{{ auth()->user()->name }}</p>
                <p class="small text-body-secondary mb-0 text-break">{{ auth()->user()->email }}</p>
            </div>
            <hr class="dropdown-divider">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item d-flex align-items-center gap-2 py-2" type="submit">
                    <x-admin.icon name="logout" />
                    Sair
                </button>
            </form>
        </div>
    </div>
</header>
