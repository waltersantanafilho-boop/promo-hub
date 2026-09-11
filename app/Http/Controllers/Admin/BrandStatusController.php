<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class BrandStatusController extends Controller
{
    public function __invoke(Brand $brand): RedirectResponse
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return redirect()->route('admin.brands.index')
            ->with('success', $brand->is_active ? 'Marca ativada com sucesso.' : 'Marca desativada com sucesso.');
    }
}
