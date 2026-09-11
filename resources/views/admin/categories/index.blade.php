@extends('layouts.admin')

@section('title', 'Categorias')

@section('breadcrumbs')
    <x-admin.breadcrumb :items="[['label' => 'Categorias']]" />
@endsection

@section('content')
    <x-admin.page-header title="Categorias" description="Organize a hierarquia do catálogo." :new-url="route('admin.categories.create')" new-label="Nova categoria" />

    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3">
            <form class="d-flex flex-column flex-sm-row gap-2 admin-search" method="GET" action="{{ route('admin.categories.index') }}" role="search">
                <label class="visually-hidden" for="category-search">Buscar categorias</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><x-admin.icon name="search" /></span>
                    <input class="form-control" id="category-search" name="q" value="{{ $search }}" maxlength="100" placeholder="Nome ou slug">
                </div>
                <button class="btn btn-outline-primary" type="submit">Buscar</button>
                @if ($search !== '')
                    <a class="btn btn-link" href="{{ route('admin.categories.index') }}">Limpar</a>
                @endif
            </form>
        </div>

        @if ($categories->isEmpty())
            <x-admin.empty-state title="Nenhuma categoria encontrada" description="Cadastre uma categoria ou ajuste os termos da busca." />
        @else
            <div class="table-responsive">
                <table class="table table-hover admin-table">
                    <thead><tr><th scope="col">Nome</th><th scope="col">Slug</th><th scope="col">Categoria pai</th><th scope="col">Status</th><th scope="col" class="text-end">Ações</th></tr></thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td><a class="admin-table-link" href="{{ route('admin.categories.show', $category) }}">{{ $category->name }}</a></td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>{{ $category->parent?->name ?? '—' }}</td>
                                <td><x-admin.status-badge :active="$category->is_active" /></td>
                                <td class="admin-table-actions text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.categories.show', $category) }}">Visualizar</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Editar</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.categories.status', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-{{ $category->is_active ? 'danger' : 'success' }}" type="submit">{{ $category->is_active ? 'Desativar' : 'Ativar' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="card-footer bg-white border-top p-3">{{ $categories->links() }}</div>
            @endif
        @endif
    </div>
@endsection
