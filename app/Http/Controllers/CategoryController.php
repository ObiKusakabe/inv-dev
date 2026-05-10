<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->with('parent:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'created_at']);

        $parents = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('category/Index', [
            'categories' => $categories,
            'parents' => $parents,
        ]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Hindari parent == diri sendiri (store sih belum mungkin, tapi aman)
        Category::create($data);

        return redirect()->route('category.index');
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        // Cegah category jadi parent dirinya sendiri
        if (isset($data['parent_id']) && (int) $data['parent_id'] === (int) $category->id) {
            return redirect()
                ->route('category.index')
                ->withErrors(['parent_id' => 'Parent category tidak boleh dirinya sendiri.']);
        }

        $category->update($data);

        return redirect()->route('category.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Jika punya children, kamu bisa pilih: block delete atau set null.
        // Default DB kamu nullOnDelete untuk parent_id, jadi aman untuk child.
        $category->delete();

        return redirect()->route('category.index');
    }
}