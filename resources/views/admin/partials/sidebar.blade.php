<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-label">
    <div class="admin-sidebar-header d-flex align-items-center justify-content-between">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <span class="admin-brand-mark" aria-hidden="true">p<span>h</span></span>
            <span id="admin-sidebar-label">PromoHub</span>
        </a>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Fechar menu"></button>
    </div>

    <div class="offcanvas-body">
        <nav class="w-100" aria-label="Menu administrativo">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                        <x-admin.icon name="dashboard" />
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            @canany(['manage products', 'manage categories', 'manage brands'])
                <h2 class="admin-nav-heading">Catálogo</h2>
                <ul class="nav flex-column gap-1">
                    @can('manage products')
                        <x-admin.pending-nav-item icon="layers" label="Grupos de Produtos" />
                        <x-admin.pending-nav-item icon="box" label="Produtos" />
                    @endcan
                    @can('manage categories')
                        <x-admin.pending-nav-item icon="grid" label="Categorias" />
                    @endcan
                    @can('manage brands')
                        <x-admin.pending-nav-item icon="brand" label="Marcas" />
                    @endcan
                </ul>
            @endcanany

            @canany(['manage offers', 'manage stores'])
                <h2 class="admin-nav-heading">Comercial</h2>
                <ul class="nav flex-column gap-1">
                    @can('manage offers')
                        <x-admin.pending-nav-item icon="tag" label="Ofertas" />
                    @endcan
                    @can('manage stores')
                        <x-admin.pending-nav-item icon="store" label="Lojas" />
                    @endcan
                </ul>
            @endcanany

            <p class="admin-sidebar-note mb-0">Os módulos sinalizados estarão disponíveis nas próximas etapas.</p>
        </nav>
    </div>
</aside>
