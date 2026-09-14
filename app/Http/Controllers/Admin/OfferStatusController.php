<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Offer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferStatusRequest;
use Illuminate\Http\RedirectResponse;

class OfferStatusController extends Controller
{
    public function __invoke(OfferStatusRequest $request, Offer $offer): RedirectResponse
    {
        $offer->update($request->validated());

        return redirect()->route('admin.offers.show', $offer)
            ->with('success', 'Status da oferta atualizado com sucesso.');
    }
}
