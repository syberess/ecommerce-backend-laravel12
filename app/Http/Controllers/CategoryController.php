<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Services\CategoryService;

class CategoryController extends Controller
{
    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * 🔹 Tüm kategorileri listeler
     */
    public function index()
    {
        $categories = $this->service->getAll();
        return response()->json($categories);
    }

    /**
     * 🔹 Yeni kategori oluşturur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $category = $this->service->create($validated);
        return response()->json($category, 201);
    }

    /**
     * 🔹 ID’ye göre kategori getirir
     */
    public function show(int $id)
    {
        $category = $this->service->getById($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    /**
     * 🔹 Kategoriyi günceller
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $updated = $this->service->update($id, $validated);

        return $updated
            ? response()->json(['message' => 'Category updated'])
            : response()->json(['message' => 'Category not found'], 404);
    }

    /**
     * 🔹 Kategoriyi siler
     */
    public function destroy(int $id)
    {
        $deleted = $this->service->delete($id);

        return $deleted
            ? response()->json(['message' => 'Category deleted'])
            : response()->json(['message' => 'Category not found'], 404);
    }
}
