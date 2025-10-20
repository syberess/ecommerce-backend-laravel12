<?php

namespace App\Core\Interfaces;

interface ICartRepository extends IBaseRepository
{
    public function getUserCart();
    public function addItem(array $data);
    public function updateItem(int $id, int $quantity);
    public function removeItem(int $id);
    public function clearCart();
}
