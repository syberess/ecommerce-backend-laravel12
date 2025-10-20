<?php

namespace App\Core\Services;

use App\Core\Interfaces\ICategoryRepository;

class CategoryService
{
    protected ICategoryRepository $repository;

    public function __construct(ICategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Tüm kategorileri getirir.
     */
    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * ID'ye göre kategori bulur.
     */
    public function getById(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * Yeni kategori oluşturur.
     */
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Kategoriyi günceller.
     */
    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Kategoriyi siler.
     */
    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    /**
     * (Opsiyonel) Gelecekte kategoriye özel mantıklar buraya eklenebilir.
     * Örneğin ürün sayısı ile birlikte kategorileri getirmek gibi.
     */
}
