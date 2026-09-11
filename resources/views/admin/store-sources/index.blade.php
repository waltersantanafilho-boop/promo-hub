@extends('layouts.admin')
@section('title', 'Fontes de '.$store->name)
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => $store->name, 'url' => route('admin.stores.show', $store)], ['label' => 'Fontes']]" />@endsection
@section('content')
    <x-admin.page-header :title="'Fontes de '.$store->name" description="Canais técnicos usados para receber dados desta loja." :new-url="route('admin.stores.sources.create', $store)" new-label="Nova fonte" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3">
            <form class="d-flex flex-column flex-sm-row gap-2 admin-search" method="GET" action="{{ route('admin.stores.sources.index', $store) }}" role="search">
                <label class="visually-hidden" for="source-search">Buscar fontes</label>
                <div class="input-group"><span class="input-group-text bg-white"><x-admin.icon name="search" /></span><input class="form-control" id="source-search" name="q" value="{{ $search }}" maxlength="100" placeholder="Tipo ou provider"></div>
                <button class="btn btn-outline-primary" type="submit">Buscar</button>
                @if ($search !== '')<a class="btn btn-link" href="{{ route('admin.stores.sources.index', $store) }}">Limpar</a>@endif
            </form>
        </div>
        @if ($sources->isEmpty())
            <x-admin.empty-state title="Nenhuma fonte encontrada" description="Cadastre uma fonte para esta loja ou ajuste os termos da busca." />
        @else
            <div class="table-responsive">
                <table class="table table-hover admin-table">
                    <thead><tr><th scope="col">Tipo</th><th scope="col">Provider</th><th scope="col">Configuração</th><th scope="col">Status</th><th scope="col" class="text-end">Ações</th></tr></thead>
                    <tbody>
                        @foreach ($sources as $source)
                            <tr>
                                <td><a class="admin-table-link" href="{{ route('admin.stores.sources.show', [$store, $source]) }}">{{ $source->type->label() }}</a></td>
                                <td><code>{{ $source->provider_key ?? '—' }}</code></td>
                                <td>{{ $source->config === null ? 'Não informada' : count($source->config).' item(ns)' }}</td>
                                <td><x-admin.status-badge :active="$source->is_active" /></td>
                                <td class="admin-table-actions text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.stores.sources.show', [$store, $source]) }}">Visualizar</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stores.sources.edit', [$store, $source]) }}">Editar</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.stores.sources.status', [$store, $source]) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $source->is_active ? 'danger' : 'success' }}" type="submit">{{ $source->is_active ? 'Desativar' : 'Ativar' }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($sources->hasPages())<div class="card-footer bg-white border-top p-3">{{ $sources->links() }}</div>@endif
        @endif
    </div>
@endsection
