<?php

namespace App\Core\Services;

use App\Core\Interfaces\ICartRepository;

class CartService
{
    protected ICartRepository $repository;

    public function __construct(ICartRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getCart()
    {
        return $this->repository->getUserCart();
    }

    public function addItem(array $data)
    {
        return $this->repository->addItem($data);
    }

    public function updateItem(int $id, int $qty)
    {
        return $this->repository->updateItem($id, $qty);
    }

    public function removeItem(int $id)
    {
        return $this->repository->removeItem($id);
    }

    public function clear()
    {
        return $this->repository->clearCart();
    }
}
