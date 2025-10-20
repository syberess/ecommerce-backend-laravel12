<?php

namespace App\Core\Interfaces;

interface IProductRepository extends IBaseRepository
{
    // Ürüne özel metotlar buraya
    public function getByCategory($categoryId);
    public function searchProducts(?string $keyword);
    // 🔹 Sayfalama + filtreleme desteği
    public function paginateWithFilters(array $filters = [], $perPage = 10, ?string $sortBy = null, string $sortOrder = 'asc');
}
