@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-page-heading mb-4">
        <h1 class="mb-1">Dashboard</h1>
        <p class="text-body-secondary mb-0">Uma visão geral do catálogo do PromoHub.</p>
    </div>

    <section aria-labelledby="catalog-overview-title">
        <div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-3">
            <h2 class="h6 mb-0" id="catalog-overview-title">Resumo do catálogo</h2>
            <span class="small text-body-secondary">Total de registros, incluindo inativos</span>
        </div>
        <div class="row g-3">
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card id="categories" label="Categorias" :count="$counts['categories']" icon="grid" description="Organização do catálogo" />
            </div>
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card id="brands" label="Marcas" :count="$counts['brands']" icon="brand" description="Marcas cadastradas" tone="purple" />
            </div>
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card id="stores" label="Lojas" :count="$counts['stores']" icon="store" description="Lojas do catálogo" tone="teal" />
            </div>
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card id="product-groups" label="Grupos de Produtos" :count="$counts['product_groups']" icon="layers" description="Modelos que agrupam variações" tone="purple" />
            </div>
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card id="products" label="Produtos" :count="$counts['products']" icon="box" description="Produtos e suas variações" />
            </div>
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card id="offers" label="Ofertas" :count="$counts['offers']" icon="tag" description="Ofertas vinculadas aos produtos" tone="teal" />
            </div>
        </div>
    </section>

    <div class="admin-foundation-note d-flex align-items-start gap-3 mt-4 p-3 p-sm-4">
        <x-admin.icon name="layers" class="mt-1" />
        <div>
            <h2 class="h6 mb-1">Seu catálogo, em um só lugar</h2>
            <p class="mb-0 small text-body-secondary">Este é o ponto de partida do painel. Por enquanto, você pode consultar os totais. As telas de gerenciamento serão disponibilizadas nas próximas etapas.</p>
        </div>
    </div>
@endsection
