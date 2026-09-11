@extends('layouts.admin')

@section('title', 'Marcas')

@section('breadcrumbs')
    <x-admin.breadcrumb :items="[['label' => 'Marcas']]" />
@endsection

@section('content')
    <x-admin.page-header title="Marcas" description="Gerencie as marcas do catálogo." :new-url="route('admin.brands.create')" new-label="Nova marca" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3">
            <form class="d-flex flex-column flex-sm-row gap-2 admin-search" method="GET" action="{{ route('admin.brands.index') }}" role="search">
                <label class="visually-hidden" for="brand-search">Buscar marcas</label>
                <div class="input-group"><span class="input-group-text bg-white"><x-admin.icon name="search" /></span><input class="form-control" id="brand-search" name="q" value="{{ $search }}" maxlength="100" placeholder="Nome ou slug"></div>
                <button class="btn btn-outline-primary" type="submit">Buscar</button>
                @if ($search !== '')<a class="btn btn-link" href="{{ route('admin.brands.index') }}">Limpar</a>@endif
            </form>
        </div>
        @if ($brands->isEmpty())
            <x-admin.empty-state title="Nenhuma marca encontrada" description="Cadastre uma marca ou ajuste os termos da busca." />
        @else
            <div class="table-responsive">
                <table class="table table-hover admin-table">
                    <thead><tr><th scope="col">Marca</th><th scope="col">Slug</th><th scope="col">Status</th><th scope="col" class="text-end">Ações</th></tr></thead>
                    <tbody>
                        @foreach ($brands as $brand)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($brand->logo_url)<img class="admin-logo-preview" src="{{ $brand->logo_url }}" alt="">@endif
                                        <a class="admin-table-link" href="{{ route('admin.brands.show', $brand) }}">{{ $brand->name }}</a>
                                    </div>
                                </td>
                                <td><code>{{ $brand->slug }}</code></td>
                                <td><x-admin.status-badge :active="$brand->is_active" /></td>
                                <td class="admin-table-actions text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.brands.show', $brand) }}">Visualizar</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.brands.edit', $brand) }}">Editar</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.brands.status', $brand) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $brand->is_active ? 'danger' : 'success' }}" type="submit">{{ $brand->is_active ? 'Desativar' : 'Ativar' }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($brands->hasPages())<div class="card-footer bg-white border-top p-3">{{ $brands->links() }}</div>@endif
        @endif
    </div>
@endsection
