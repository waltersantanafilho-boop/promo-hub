@extends('layouts.admin')
@section('title', 'Fonte '.$source->type->label())
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => $store->name, 'url' => route('admin.stores.show', $store)], ['label' => 'Fontes', 'url' => route('admin.stores.sources.index', $store)], ['label' => $source->type->label()]]" />@endsection
@section('content')
    <x-admin.page-header :title="'Fonte '.$source->type->label()" :description="'Detalhes da fonte de '.$store->name.'.'" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 p-3"><x-admin.status-badge :active="$source->is_active" /><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.stores.sources.index', $store) }}">Voltar</a><a class="btn btn-sm btn-primary" href="{{ route('admin.stores.sources.edit', [$store, $source]) }}">Editar</a></div></div>
        <div class="card-body p-3 p-sm-4">
            <div class="alert alert-warning small" role="alert"><strong>Segurança:</strong> esta configuração deve conter apenas parâmetros não sensíveis.</div>
            <dl class="row g-4 mb-0">
                <div class="col-md-6"><dt class="admin-detail-label">Loja</dt><dd class="admin-detail-value"><a href="{{ route('admin.stores.show', $store) }}">{{ $store->name }}</a></dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">ID da loja</dt><dd class="admin-detail-value">{{ $source->store_id }}</dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Tipo</dt><dd class="admin-detail-value">{{ $source->type->label() }}</dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Provider</dt><dd class="admin-detail-value"><code>{{ $source->provider_key ?? 'Não informado' }}</code></dd></div>
                <div class="col-12"><dt class="admin-detail-label">Configuração</dt><dd class="admin-detail-value">@if ($configJson === null) Não informada @else <pre class="admin-config-preview bg-body-tertiary border rounded p-3 mt-2 mb-0"><code>{{ $configJson }}</code></pre> @endif</dd></div>
            </dl>
        </div>
    </div>
@endsection
