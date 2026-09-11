<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StoreSourceStatusController extends Controller
{
    public function __invoke(Store $store, StoreSource $source): RedirectResponse
    {
        $source->update(['is_active' => ! $source->is_active]);

        return redirect()->route('admin.stores.sources.index', $store)
            ->with('success', $source->is_active ? 'Fonte ativada com sucesso.' : 'Fonte desativada com sucesso.');
    }
}
