<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Store\Store;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StoreStatusController extends Controller
{
    public function __invoke(Store $store): RedirectResponse
    {
        $store->update(['is_active' => ! $store->is_active]);

        return redirect()->route('admin.stores.index')
            ->with('success', $store->is_active ? 'Loja ativada com sucesso.' : 'Loja desativada com sucesso.');
    }
}
