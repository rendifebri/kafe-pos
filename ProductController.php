<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('nama_produk', 'like', '%' . $request->q . '%')
                  ->orWhere('kode_produk', 'like', '%' . $request->q . '%');
            }))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('nama_produk')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('nama_kategori')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('products.form', [
            'product' => new Product(['unit' => 'porsi', 'stok_minimum' => 5, 'aktif' => true]),
            'categories' => Category::orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('nama_kategori')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Menu yang sudah pernah dijual tidak boleh dihapus (jaga riwayat) -> nonaktifkan
        if ($product->orderDetails()->exists()) {
            $product->update(['aktif' => false]);

            return back()->with('success', 'Menu sudah pernah terjual, sehingga dinonaktifkan (bukan dihapus).');
        }

        $product->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'kode_produk' => ['required', 'string', 'max:30', Rule::unique('products', 'kode_produk')->ignore($product?->id)],
            'nama_produk' => 'required|string|max:150',
            'harga_jual' => 'required|integer|min:0',
            'harga_pokok' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
