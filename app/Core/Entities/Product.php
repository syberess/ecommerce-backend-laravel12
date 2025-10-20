<?php

namespace App\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'image_url'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
