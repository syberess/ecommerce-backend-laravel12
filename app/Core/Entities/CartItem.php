<?php
namespace App\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }
}
