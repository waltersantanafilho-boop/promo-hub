@extends('layouts.admin')

@section('title', 'Produtos')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Produtos']]" />@endsection

@section('content')
    <x-admin.page-header title="Produtos" description="Gerencie as variações canônicas do catálogo." :new-url="route('admin.products.create')" new-label="Novo produto" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3"><form class="row g-2" method="GET" action="{{ route('admin.products.index') }}" role="search">
            <div class="col-12 col-xl"><input class="form-control" name="q" value="{{ $search }}" maxlength="100" aria-label="Buscar produtos" placeholder="Nome, slug ou GTIN"></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="product_group_id" aria-label="Filtrar por grupo"><option value="">Todos os grupos</option>@foreach ($productGroups as $group)<option value="{{ $group->id }}" @selected($productGroupId === $group->id)>{{ $group->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="category_id" aria-label="Filtrar por categoria"><option value="">Todas as categorias</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="brand_id" aria-label="Filtrar por marca"><option value="">Todas as marcas</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected($brandId === $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="status" aria-label="Filtrar por status"><option value="">Todos os status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status)>{{ $status->label() }}</option>@endforeach</select></div>
            <div class="col-12 d-flex justify-content-end gap-2"><button class="btn btn-outline-primary" type="submit">Filtrar</button><a class="btn btn-link" href="{{ route('admin.products.index') }}">Limpar</a></div>
        </form></div>
        @if ($products->isEmpty())<x-admin.empty-state title="Nenhum produto encontrado" description="Cadastre um produto ou ajuste os filtros." />@else
            <div class="table-responsive"><table class="table table-hover admin-table"><thead><tr><th>Produto</th><th>Grupo</th><th>Categoria</th><th>Marca</th><th>Ofertas</th><th>Status</th><th class="text-end">Ações</th></tr></thead><tbody>
                @foreach ($products as $product)<tr><td><a class="admin-table-link" href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>@if ($product->gtin)<div class="small text-body-secondary">GTIN {{ $product->gtin }}</div>@endif</td><td>{{ $product->productGroup?->name ?? '—' }}</td><td>{{ $product->category->name }}</td><td>{{ $product->brand->name }}</td><td>{{ number_format($product->offers_count, 0, ',', '.') }}</td><td><x-admin.state-badge :value="$product->status->value" :label="$product->status->label()" /></td><td class="admin-table-actions text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.show', $product) }}">Visualizar</a> <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product) }}">Editar</a></td></tr>@endforeach
            </tbody></table></div>
            @if ($products->hasPages())<div class="card-footer bg-white border-top p-3">{{ $products->links() }}</div>@endif
        @endif
    </div>
@endsection
