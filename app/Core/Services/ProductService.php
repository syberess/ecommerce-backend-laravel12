<?php

namespace App\Core\Services;

use App\Core\Interfaces\IProductRepository;

class ProductService
{
    protected IProductRepository $repository;

    public function __construct(IProductRepository $repository)
    {
        $this->repository = $repository;
    }

    // 🔹 Tüm ürünleri getir
    public function getAll()
    {
        return $this->repository->getAll();
    }

    // 🔹 ID'ye göre ürün bul
    public function getById(int $id)
    {
        return $this->repository->getById($id);
    }

    // 🔹 Yeni ürün oluştur
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    // 🔹 Ürün güncelle
    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    // 🔹 Ürün sil
    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    // 🔹 Ürün arama
    public function search(?string $keyword = null)
    {
        return $this->repository->searchProducts($keyword);
    }

    // 🔹 Kategoriye göre filtreleme
    public function filterByCategory(?int $categoryId)
    {
        return $this->repository->filter(['category_id' => $categoryId]);
    }

    // 🔹 Sayfalama
    public function paginate(int $perPage = 10)
    {
        return $this->repository->paginate($perPage);
    }

    // 🔹 Filtre + sıralama + sayfalama
    public function paginateWithFilters(
        array $filters = [],
        int $perPage = 10,
        ?string $sortBy = null,
        string $sortOrder = 'asc'
    ) {
        return $this->repository->paginateWithFilters($filters, $perPage, $sortBy, $sortOrder);
    }
}
