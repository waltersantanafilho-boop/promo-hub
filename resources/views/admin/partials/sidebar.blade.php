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
                        <li class="nav-item">
                            <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.product-groups.*')]) href="{{ route('admin.product-groups.index') }}" @if (request()->routeIs('admin.product-groups.*')) aria-current="page" @endif>
                                <x-admin.icon name="layers" />
                                <span>Grupos de Produtos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.products.*')]) href="{{ route('admin.products.index') }}" @if (request()->routeIs('admin.products.*')) aria-current="page" @endif>
                                <x-admin.icon name="box" />
                                <span>Produtos</span>
                            </a>
                        </li>
                    @endcan
                    @can('manage categories')
                        <li class="nav-item">
                            <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.categories.*')]) href="{{ route('admin.categories.index') }}" @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif>
                                <x-admin.icon name="grid" />
                                <span>Categorias</span>
                            </a>
                        </li>
                    @endcan
                    @can('manage brands')
                        <li class="nav-item">
                            <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.brands.*')]) href="{{ route('admin.brands.index') }}" @if (request()->routeIs('admin.brands.*')) aria-current="page" @endif>
                                <x-admin.icon name="brand" />
                                <span>Marcas</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            @endcanany

            @canany(['manage offers', 'manage stores'])
                <h2 class="admin-nav-heading">Comercial</h2>
                <ul class="nav flex-column gap-1">
                    @can('manage offers')
                        <li class="nav-item">
                            <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.offers.*')]) href="{{ route('admin.offers.index') }}" @if (request()->routeIs('admin.offers.*')) aria-current="page" @endif>
                                <x-admin.icon name="tag" />
                                <span>Ofertas</span>
                            </a>
                        </li>
                    @endcan
                    @can('manage stores')
                        <li class="nav-item">
                            <a @class(['nav-link admin-nav-link', 'active' => request()->routeIs('admin.stores.*')]) href="{{ route('admin.stores.index') }}" @if (request()->routeIs('admin.stores.*')) aria-current="page" @endif>
                                <x-admin.icon name="store" />
                                <span>Lojas</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            @endcanany
        </nav>
    </div>
</aside>
