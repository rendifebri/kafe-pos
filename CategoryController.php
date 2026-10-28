<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('nama_kategori')->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama_kategori' => 'required|string|max:100|unique:categories,nama_kategori']);

        Category::create([
            'nama_kategori' => $data['nama_kategori'],
            'slug' => Str::slug($data['nama_kategori']),
            'status' => 'aktif',
        ]);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', Rule::unique('categories', 'nama_kategori')->ignore($category->id)],
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $category->update($data + ['slug' => Str::slug($data['nama_kategori'])]);

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih memiliki menu, tidak dapat dihapus.');
        }

        $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}
