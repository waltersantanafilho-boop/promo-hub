<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class CategoryStatusController extends Controller
{
    public function __invoke(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return redirect()->route('admin.categories.index')
            ->with('success', $category->is_active ? 'Categoria ativada com sucesso.' : 'Categoria desativada com sucesso.');
    }
}
