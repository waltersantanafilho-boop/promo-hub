@extends('layouts.admin')

@section('title', 'Editar categoria')

@section('breadcrumbs')
    <x-admin.breadcrumb :items="[['label' => 'Categorias', 'url' => route('admin.categories.index')], ['label' => $category->name, 'url' => route('admin.categories.show', $category)], ['label' => 'Editar']]" />
@endsection

@section('content')
    <x-admin.page-header title="Editar categoria" :description="$category->name" />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')
        <div class="card-body p-3 p-sm-4">@include('admin.categories._form', ['cancelUrl' => route('admin.categories.show', $category)])</div>
    </form>
@endsection
