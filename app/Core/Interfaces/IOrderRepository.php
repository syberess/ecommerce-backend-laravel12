<?php

namespace App\Core\Interfaces;

interface IOrderRepository
{
    public function getAllByUser(int $userId);
    public function createOrderWithItems(array $orderData, array $items);
    public function updateStatus(int $orderId, string $status);
    public function logStatusChange(int $orderId, int $changedBy, ?string $oldStatus, string $newStatus);
    public function getStatusLogs(int $orderId);
}
