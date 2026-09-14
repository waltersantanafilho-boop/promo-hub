@extends('layouts.admin')

@section('title', $productGroup->name)
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Grupos de Produtos', 'url' => route('admin.product-groups.index')], ['label' => $productGroup->name]]" />@endsection

@section('content')
    <x-admin.page-header :title="$productGroup->name" description="Detalhes do grupo de produtos." />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 p-3"><x-admin.status-badge :active="$productGroup->is_active" /><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.product-groups.index') }}">Voltar</a><a class="btn btn-sm btn-primary" href="{{ route('admin.product-groups.edit', $productGroup) }}">Editar</a></div></div>
        <div class="card-body p-3 p-sm-4"><dl class="row g-4 mb-0">
            <div class="col-md-6"><dt class="admin-detail-label">Slug</dt><dd class="admin-detail-value"><code>{{ $productGroup->slug }}</code></dd></div>
            <div class="col-md-3"><dt class="admin-detail-label">Categoria</dt><dd class="admin-detail-value">{{ $productGroup->category->name }}</dd></div>
            <div class="col-md-3"><dt class="admin-detail-label">Marca</dt><dd class="admin-detail-value">{{ $productGroup->brand->name }}</dd></div>
            <div class="col-md-4"><dt class="admin-detail-label">Produtos</dt><dd class="admin-detail-value"><a href="{{ route('admin.products.index', ['product_group_id' => $productGroup->id]) }}">{{ number_format($productGroup->products_count, 0, ',', '.') }}</a></dd></div>
            <div class="col-12"><dt class="admin-detail-label">Descrição</dt><dd class="admin-detail-value">{{ $productGroup->description ?: 'Sem descrição' }}</dd></div>
        </dl></div>
    </div>
@endsection
