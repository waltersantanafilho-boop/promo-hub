@extends('layouts.admin')

@section('title', $brand->name)
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Marcas', 'url' => route('admin.brands.index')], ['label' => $brand->name]]" />@endsection
@section('content')
    <x-admin.page-header :title="$brand->name" description="Detalhes da marca." />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 p-3"><x-admin.status-badge :active="$brand->is_active" /><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.brands.index') }}">Voltar</a><a class="btn btn-sm btn-primary" href="{{ route('admin.brands.edit', $brand) }}">Editar</a></div></div>
        <div class="card-body p-3 p-sm-4">
            <dl class="row g-4 mb-0">
                <div class="col-md-6"><dt class="admin-detail-label">Nome</dt><dd class="admin-detail-value">{{ $brand->name }}</dd></div>
                <div class="col-md-6"><dt class="admin-detail-label">Slug</dt><dd class="admin-detail-value"><code>{{ $brand->slug }}</code></dd></div>
                <div class="col-12"><dt class="admin-detail-label">Logo</dt><dd class="admin-detail-value">@if ($brand->logo_url)<div class="d-flex align-items-center gap-3"><img class="admin-logo-preview" src="{{ $brand->logo_url }}" alt="Logo de {{ $brand->name }}"><a href="{{ $brand->logo_url }}" target="_blank" rel="noopener noreferrer">Abrir imagem <x-admin.icon name="external" /></a></div>@else Não informado @endif</dd></div>
            </dl>
        </div>
    </div>
@endsection
