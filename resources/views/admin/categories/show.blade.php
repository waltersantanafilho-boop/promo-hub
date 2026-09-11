@extends('layouts.admin')

@section('title', $category->name)

@section('breadcrumbs')
    <x-admin.breadcrumb :items="[['label' => 'Categorias', 'url' => route('admin.categories.index')], ['label' => $category->name]]" />
@endsection

@section('content')
    <x-admin.page-header :title="$category->name" description="Detalhes da categoria." />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 p-3">
            <x-admin.status-badge :active="$category->is_active" />
            <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.categories.index') }}">Voltar</a><a class="btn btn-sm btn-primary" href="{{ route('admin.categories.edit', $category) }}">Editar</a></div>
        </div>
        <div class="card-body p-3 p-sm-4">
            <dl class="row g-4 mb-0">
                <div class="col-md-6"><dt class="admin-detail-label">Nome</dt><dd class="admin-detail-value">{{ $category->name }}</dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Slug</dt><dd class="admin-detail-value"><code>{{ $category->slug }}</code></dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Categoria pai</dt><dd class="admin-detail-value">{{ $category->parent?->name ?? 'Sem categoria pai' }}</dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Categorias filhas</dt><dd class="admin-detail-value">{{ number_format($category->children_count, 0, ',', '.') }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
