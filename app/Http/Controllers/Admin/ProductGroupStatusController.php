<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\ProductGroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ProductGroupStatusController extends Controller
{
    public function __invoke(ProductGroup $productGroup): RedirectResponse
    {
        $productGroup->update(['is_active' => ! $productGroup->is_active]);

        return redirect()->route('admin.product-groups.index')
            ->with('success', $productGroup->is_active ? 'Grupo de produtos ativado com sucesso.' : 'Grupo de produtos desativado com sucesso.');
    }
}
