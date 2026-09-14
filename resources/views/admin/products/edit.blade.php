@extends('layouts.admin')

@section('title', 'Editar produto')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Produtos', 'url' => route('admin.products.index')], ['label' => $product->name, 'url' => route('admin.products.show', $product)], ['label' => 'Editar']]" />@endsection

@section('content')
    <x-admin.page-header title="Editar produto" :description="$product->name" />
    <div class="card admin-content-card"><div class="card-body p-3 p-sm-4"><form method="POST" action="{{ route('admin.products.update', $product) }}">@csrf @method('PUT') @include('admin.products._form', ['cancelUrl' => route('admin.products.show', $product)])</form></div></div>
@endsection
