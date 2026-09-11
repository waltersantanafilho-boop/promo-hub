@extends('layouts.admin')
@section('title', 'Editar fonte')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => $store->name, 'url' => route('admin.stores.show', $store)], ['label' => 'Fontes', 'url' => route('admin.stores.sources.index', $store)], ['label' => $source->type->label(), 'url' => route('admin.stores.sources.show', [$store, $source])], ['label' => 'Editar']]" />@endsection
@section('content')
    <x-admin.page-header title="Editar fonte" :description="'Fonte de '.$store->name.'.'" />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.stores.sources.update', [$store, $source]) }}">@csrf @method('PUT')<div class="card-body p-3 p-sm-4">@include('admin.store-sources._form', ['cancelUrl' => route('admin.stores.sources.show', [$store, $source])])</div></form>
@endsection
