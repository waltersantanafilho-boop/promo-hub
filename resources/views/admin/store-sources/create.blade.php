@extends('layouts.admin')
@section('title', 'Nova fonte')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => $store->name, 'url' => route('admin.stores.show', $store)], ['label' => 'Fontes', 'url' => route('admin.stores.sources.index', $store)], ['label' => 'Nova fonte']]" />@endsection
@section('content')
    <x-admin.page-header title="Nova fonte" :description="'Adicione uma fonte de dados para '.$store->name.'.'" />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.stores.sources.store', $store) }}">@csrf<div class="card-body p-3 p-sm-4">@include('admin.store-sources._form', ['cancelUrl' => route('admin.stores.sources.index', $store), 'configJson' => null])</div></form>
@endsection
