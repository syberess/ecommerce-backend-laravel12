<?php

namespace App\Http\Policies;   // ← BURAYI değiştir

use App\Core\Entities\Cart;

class CartPolicy
{
    public function view($user, Cart $cart): bool
    {
        return $user->id === $cart->user_id || $user->role === 'admin';
    }
    public function update($user, Cart $cart): bool
    {
        return $user->id === $cart->user_id || $user->role === 'admin';
    }
    public function delete($user, Cart $cart): bool
    {
        return $user->id === $cart->user_id || $user->role === 'admin';
    }
}
