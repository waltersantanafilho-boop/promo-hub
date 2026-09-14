@extends('layouts.admin')

@section('title', 'Editar oferta')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Ofertas', 'url' => route('admin.offers.index')], ['label' => '#'.$offer->id, 'url' => route('admin.offers.show', $offer)], ['label' => 'Editar']]" />@endsection

@section('content')
    <x-admin.page-header title="Editar oferta" :description="'Oferta #'.$offer->id" />
    <div class="card admin-content-card"><div class="card-body p-3 p-sm-4"><form method="POST" action="{{ route('admin.offers.update', $offer) }}">@csrf @method('PUT') @include('admin.offers._form', ['cancelUrl' => route('admin.offers.show', $offer)])</form></div></div>
@endsection
