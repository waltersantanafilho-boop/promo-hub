@extends('layouts.admin')

@section('title', 'Editar grupo de produtos')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Grupos de Produtos', 'url' => route('admin.product-groups.index')], ['label' => $productGroup->name, 'url' => route('admin.product-groups.show', $productGroup)], ['label' => 'Editar']]" />@endsection

@section('content')
    <x-admin.page-header title="Editar grupo de produtos" :description="$productGroup->name" />
    <div class="card admin-content-card"><div class="card-body p-3 p-sm-4"><form method="POST" action="{{ route('admin.product-groups.update', $productGroup) }}">@csrf @method('PUT') @include('admin.product-groups._form', ['cancelUrl' => route('admin.product-groups.show', $productGroup)])</form></div></div>
@endsection
