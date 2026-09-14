@extends('layouts.admin')

@section('title', 'Nova oferta')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Ofertas', 'url' => route('admin.offers.index')], ['label' => 'Nova']]" />@endsection

@section('content')
    <x-admin.page-header title="Nova oferta" description="Associe um produto à oferta de uma loja." />
    <div class="card admin-content-card"><div class="card-body p-3 p-sm-4"><form method="POST" action="{{ route('admin.offers.store') }}">@csrf @include('admin.offers._form', ['cancelUrl' => route('admin.offers.index')])</form></div></div>
@endsection
