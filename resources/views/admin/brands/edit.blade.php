@extends('layouts.admin')

@section('title', 'Editar marca')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Marcas', 'url' => route('admin.brands.index')], ['label' => $brand->name, 'url' => route('admin.brands.show', $brand)], ['label' => 'Editar']]" />@endsection
@section('content')
    <x-admin.page-header title="Editar marca" :description="$brand->name" />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.brands.update', $brand) }}">@csrf @method('PUT')<div class="card-body p-3 p-sm-4">@include('admin.brands._form', ['cancelUrl' => route('admin.brands.show', $brand)])</div></form>
@endsection
