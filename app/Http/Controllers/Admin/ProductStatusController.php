<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductStatusRequest;
use Illuminate\Http\RedirectResponse;

class ProductStatusController extends Controller
{
    public function __invoke(ProductStatusRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Status do produto atualizado com sucesso.');
    }
}
