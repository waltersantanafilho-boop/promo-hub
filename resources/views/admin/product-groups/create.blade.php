@extends('layouts.admin')

@section('title', 'Novo grupo de produtos')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Grupos de Produtos', 'url' => route('admin.product-groups.index')], ['label' => 'Novo']]" />@endsection

@section('content')
    <x-admin.page-header title="Novo grupo de produtos" description="Agrupe variações do mesmo modelo de produto." />
    <div class="card admin-content-card"><div class="card-body p-3 p-sm-4"><form method="POST" action="{{ route('admin.product-groups.store') }}">@csrf @include('admin.product-groups._form', ['cancelUrl' => route('admin.product-groups.index')])</form></div></div>
@endsection
