@extends('layouts.admin')
@section('title', 'Editar loja')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => $store->name, 'url' => route('admin.stores.show', $store)], ['label' => 'Editar']]" />@endsection
@section('content')
    <x-admin.page-header title="Editar loja" :description="$store->name" />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.stores.update', $store) }}">@csrf @method('PUT')<div class="card-body p-3 p-sm-4">@include('admin.stores._form', ['cancelUrl' => route('admin.stores.show', $store)])</div></form>
@endsection
