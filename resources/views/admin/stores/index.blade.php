@extends('layouts.admin')

@section('title', 'Lojas')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas']]" />@endsection
@section('content')
    <x-admin.page-header title="Lojas" description="Gerencie lojas e suas fontes de dados." :new-url="route('admin.stores.create')" new-label="Nova loja" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3">
            <form class="d-flex flex-column flex-sm-row gap-2 admin-search" method="GET" action="{{ route('admin.stores.index') }}" role="search">
                <label class="visually-hidden" for="store-search">Buscar lojas</label>
                <div class="input-group"><span class="input-group-text bg-white"><x-admin.icon name="search" /></span><input class="form-control" id="store-search" name="q" value="{{ $search }}" maxlength="100" placeholder="Nome ou slug"></div>
                <button class="btn btn-outline-primary" type="submit">Buscar</button>
                @if ($search !== '')<a class="btn btn-link" href="{{ route('admin.stores.index') }}">Limpar</a>@endif
            </form>
        </div>
        @if ($stores->isEmpty())
            <x-admin.empty-state title="Nenhuma loja encontrada" description="Cadastre uma loja ou ajuste os termos da busca." />
        @else
            <div class="table-responsive">
                <table class="table table-hover admin-table">
                    <thead><tr><th scope="col">Loja</th><th scope="col">Site</th><th scope="col">Fontes</th><th scope="col">Status</th><th scope="col" class="text-end">Ações</th></tr></thead>
                    <tbody>
                        @foreach ($stores as $store)
                            <tr>
                                <td><div class="d-flex align-items-center gap-2"><img class="admin-logo-preview" src="{{ $store->logo_url }}" alt=""><a class="admin-table-link" href="{{ route('admin.stores.show', $store) }}">{{ $store->name }}</a></div></td>
                                <td><a href="{{ $store->website_url }}" target="_blank" rel="noopener noreferrer">Visitar <x-admin.icon name="external" /></a></td>
                                <td><a href="{{ route('admin.stores.sources.index', $store) }}">{{ number_format($store->sources_count, 0, ',', '.') }}</a></td>
                                <td><x-admin.status-badge :active="$store->is_active" /></td>
                                <td class="admin-table-actions text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.stores.show', $store) }}">Visualizar</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stores.edit', $store) }}">Editar</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.stores.status', $store) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $store->is_active ? 'danger' : 'success' }}" type="submit">{{ $store->is_active ? 'Desativar' : 'Ativar' }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($stores->hasPages())<div class="card-footer bg-white border-top p-3">{{ $stores->links() }}</div>@endif
        @endif
    </div>
@endsection
