<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');

        $categories = Category::query()
            ->with('parent:id,name')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category,
            'parentCategories' => $this->parentCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::query()->create($request->validated());

        return redirect()->route('admin.categories.show', $category)
            ->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $category->load('parent:id,name')->loadCount('children');

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parentCategories' => $this->parentCategories($category),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.show', $category)
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * @return Collection<int, Category>
     */
    private function parentCategories(?Category $category = null): Collection
    {
        return Category::query()
            ->when($category !== null, fn ($query) => $query->whereKeyNot($category->getKey()))
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id']);
    }
}
