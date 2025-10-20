<?php

namespace App\Infrastructure\Repositories;

use App\Core\Interfaces\IOrderRepository;
use App\Core\Entities\Order;

class OrderRepository extends BaseRepository implements IOrderRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getAllByUser(int $userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function createOrderWithItems(array $orderData, array $items)
    {
        // Sipariş + ürün ilişkilerini kaydetmek için özel mantık
    }

    public function updateStatus(int $orderId, string $status)
    {
        $order = $this->model->findOrFail($orderId);
        $order->status = $status;
        $order->save();
        return $order;
    }

    public function logStatusChange(int $orderId, int $changedBy, ?string $oldStatus, string $newStatus)
    {
        // OrderStatusLog tablosuna kayıt ekleme
    }

    public function getStatusLogs(int $orderId)
    {
        return $this->model->find($orderId)?->statusLogs;
    }
}
