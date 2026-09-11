@extends('layouts.admin')
@section('title', $store->name)
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => $store->name]]" />@endsection
@section('content')
    <x-admin.page-header :title="$store->name" description="Detalhes da loja e acesso às fontes de dados." />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 p-3"><x-admin.status-badge :active="$store->is_active" /><div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.stores.index') }}">Voltar</a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stores.sources.index', $store) }}">Fontes ({{ $store->sources_count }})</a><a class="btn btn-sm btn-primary" href="{{ route('admin.stores.edit', $store) }}">Editar</a></div></div>
        <div class="card-body p-3 p-sm-4">
            <dl class="row g-4 mb-0">
                <div class="col-md-6"><dt class="admin-detail-label">Nome</dt><dd class="admin-detail-value">{{ $store->name }}</dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Slug</dt><dd class="admin-detail-value"><code>{{ $store->slug }}</code></dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Site</dt><dd class="admin-detail-value"><a href="{{ $store->website_url }}" target="_blank" rel="noopener noreferrer">{{ $store->website_url }} <x-admin.icon name="external" /></a></dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Logo</dt><dd class="admin-detail-value d-flex align-items-center gap-3"><img class="admin-logo-preview" src="{{ $store->logo_url }}" alt="Logo de {{ $store->name }}"><a href="{{ $store->logo_url }}" target="_blank" rel="noopener noreferrer">Abrir imagem</a></dd></div>
            </dl>
        </div>
    </div>
@endsection
