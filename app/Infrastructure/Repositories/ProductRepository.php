<?php

namespace App\Infrastructure\Repositories;

use App\Core\Interfaces\IProductRepository;
use App\Core\Entities\Product; // 🔹 doğru konum
use App\Infrastructure\Repositories\BaseRepository;

class ProductRepository extends BaseRepository implements IProductRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    // Ürüne özel sorgular burada
    public function getByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }
    public function searchProducts($keyword)
    {
        return $this->search($keyword, ['name', 'description']);
    }
    public function filter(array $filters)
    {
        $query = $this->model->query();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->get();
    }


    public function paginateWithFilters(array $filters = [], $perPage = 10, ?string $sortBy = null, string $sortOrder = 'asc')
    {
        return parent::paginateWithFilters($filters, $perPage, $sortBy, $sortOrder);
    }



}
