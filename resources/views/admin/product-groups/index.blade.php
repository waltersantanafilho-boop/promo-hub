@extends('layouts.admin')

@section('title', 'Grupos de Produtos')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Grupos de Produtos']]" />@endsection

@section('content')
    <x-admin.page-header title="Grupos de Produtos" description="Organize modelos e suas variações." :new-url="route('admin.product-groups.create')" new-label="Novo grupo" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3"><form class="row g-2" method="GET" action="{{ route('admin.product-groups.index') }}" role="search">
            <div class="col-12 col-lg"><label class="visually-hidden" for="product-group-search">Buscar</label><input class="form-control" id="product-group-search" name="q" value="{{ $search }}" maxlength="100" placeholder="Nome ou slug"></div>
            <div class="col-sm-4 col-lg-3"><select class="form-select" name="category_id" aria-label="Filtrar por categoria"><option value="">Todas as categorias</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-sm-4 col-lg-3"><select class="form-select" name="brand_id" aria-label="Filtrar por marca"><option value="">Todas as marcas</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected($brandId === $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
            <div class="col-sm-4 col-lg-2"><select class="form-select" name="status" aria-label="Filtrar por status"><option value="">Todos os status</option><option value="active" @selected($status === 'active')>Ativos</option><option value="inactive" @selected($status === 'inactive')>Inativos</option></select></div>
            <div class="col-12 col-lg-auto d-flex gap-2"><button class="btn btn-outline-primary" type="submit">Filtrar</button><a class="btn btn-link" href="{{ route('admin.product-groups.index') }}">Limpar</a></div>
        </form></div>
        @if ($productGroups->isEmpty())<x-admin.empty-state title="Nenhum grupo encontrado" description="Cadastre um grupo ou ajuste os filtros." />@else
            <div class="table-responsive"><table class="table table-hover admin-table"><thead><tr><th>Nome</th><th>Categoria</th><th>Marca</th><th>Produtos</th><th>Status</th><th class="text-end">Ações</th></tr></thead><tbody>
                @foreach ($productGroups as $productGroup)<tr><td><a class="admin-table-link" href="{{ route('admin.product-groups.show', $productGroup) }}">{{ $productGroup->name }}</a><div><code>{{ $productGroup->slug }}</code></div></td><td>{{ $productGroup->category->name }}</td><td>{{ $productGroup->brand->name }}</td><td>{{ number_format($productGroup->products_count, 0, ',', '.') }}</td><td><x-admin.status-badge :active="$productGroup->is_active" /></td><td class="admin-table-actions text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.product-groups.show', $productGroup) }}">Visualizar</a> <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.product-groups.edit', $productGroup) }}">Editar</a> <form class="d-inline" method="POST" action="{{ route('admin.product-groups.status', $productGroup) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $productGroup->is_active ? 'danger' : 'success' }}" type="submit">{{ $productGroup->is_active ? 'Desativar' : 'Ativar' }}</button></form></td></tr>@endforeach
            </tbody></table></div>
            @if ($productGroups->hasPages())<div class="card-footer bg-white border-top p-3">{{ $productGroups->links() }}</div>@endif
        @endif
    </div>
@endsection
