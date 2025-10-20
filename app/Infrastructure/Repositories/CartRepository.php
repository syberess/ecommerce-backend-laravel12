<?php

namespace App\Infrastructure\Repositories;

use App\Core\Interfaces\ICartRepository;
use App\Core\Entities\Cart;
use App\Core\Entities\CartItem;
use Illuminate\Support\Facades\Auth;

class CartRepository extends BaseRepository implements ICartRepository
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getUserCart()
    {
        return $this->model
            ->firstOrCreate(['user_id' => Auth::id()])
            ->load('items.product');
    }

    public function addItem(array $data)
    {
        $cart = $this->model->firstOrCreate(['user_id' => Auth::id()]);

        $item = CartItem::firstOrNew([
            'cart_id'    => $cart->id,
            'product_id' => $data['product_id'],
        ]);

        $item->quantity = ($item->quantity ?? 0) + $data['quantity'];
        $item->save();

        return $cart->load('items.product');
    }

    public function updateItem(int $id, int $quantity)
    {
        // Aktif kullanıcının cart'ını al
        $cartId = $this->model->firstOrCreate(['user_id' => Auth::id()])->id;

        // SADECE bu cart'a ait item'ı güncelle
        $item = CartItem::where('cart_id', $cartId)
            ->where('id', $id)
            ->firstOrFail();

        $item->update(['quantity' => $quantity]);

        return $item->cart->load('items.product');
    }

    public function removeItem(int $id)
    {
        $cartId = $this->model->firstOrCreate(['user_id' => Auth::id()])->id;

        $item = CartItem::where('cart_id', $cartId)
            ->where('id', $id)
            ->firstOrFail();

        $item->delete();

        return $this->getUserCart();
    }

    public function clearCart()
    {
        $cart = $this->model->where('user_id', Auth::id())->first();

        if ($cart) {
            $cart->items()->delete();
            return $cart->load('items.product');
        }
        return null;
    }
}
