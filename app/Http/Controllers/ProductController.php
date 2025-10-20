<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Services\ProductService;
use App\Http\Controllers\Controller; 

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    // 🔹 Tüm ürünleri listele
    public function index()
    {
        return response()->json($this->service->getAll());
    }

    // 🔹 ID’ye göre ürün getir
    public function show($id)
    {
        $product = $this->service->getById($id);
        return $product
            ? response()->json($product)
            : response()->json(['message' => 'Product not found'], 404);
    }

    // 🔹 Yeni ürün oluştur
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|string'
        ]);

        $product = $this->service->create($data);
        return response()->json($product, 201);
    }

    // 🔹 Ürün güncelle
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $updated = $this->service->update($id, $data);
        return $updated
            ? response()->json(['message' => 'Product updated'])
            : response()->json(['message' => 'Product not found'], 404);
    }

    // 🔹 Ürün sil
    public function destroy($id)
    {
        $deleted = $this->service->delete($id);
        return $deleted
            ? response()->json(['message' => 'Product deleted'])
            : response()->json(['message' => 'Product not found'], 404);
    }

    // 🔹 Ürün arama
    public function search(Request $request)
    {
        $keyword = $request->query('q');
        $results = $this->service->search($keyword);
        return response()->json($results);
    }

    // 🔹 Kategoriye göre filtreleme
    public function filterByCategory(Request $request)
    {
        $categoryId = $request->query('category_id');
        $results = $this->service->filterByCategory($categoryId);
        return response()->json($results);
    }

    // 🔹 Sayfalama
    public function paginate(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $products = $this->service->paginate($perPage);
        return response()->json($products);
    }

    // 🔹 Filtre + sıralama + sayfalama
    public function paginateWithFilters(Request $request)
    {
        $filters = [
            'category_id' => $request->query('category_id'),
            'price' => $request->has(['min_price', 'max_price'])
                ? [$request->query('min_price'), $request->query('max_price')]
                : null,
        ];

        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by');           // örn: price
        $sortOrder = $request->query('sort_order', 'asc'); // asc | desc

        $products = $this->service->paginateWithFilters(
            array_filter($filters),
            $perPage,
            $sortBy,
            $sortOrder
        );

        return response()->json($products);
    }
}
