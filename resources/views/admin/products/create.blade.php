@extends('layouts.admin')

@section('title', 'Novo produto')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Produtos', 'url' => route('admin.products.index')], ['label' => 'Novo']]" />@endsection

@section('content')
    <x-admin.page-header title="Novo produto" description="Cadastre uma variação canônica do catálogo." />
    <div class="card admin-content-card"><div class="card-body p-3 p-sm-4"><form method="POST" action="{{ route('admin.products.store') }}">@csrf @include('admin.products._form', ['cancelUrl' => route('admin.products.index')])</form></div></div>
@endsection
