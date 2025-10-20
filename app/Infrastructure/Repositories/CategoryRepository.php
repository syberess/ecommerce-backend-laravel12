<?php

namespace App\Infrastructure\Repositories;

use App\Core\Entities\Category;
use App\Core\Interfaces\ICategoryRepository;

class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    
}
