<?php

namespace App\Core\Interfaces;

interface IPaymentRepository extends IBaseRepository
{
    // Ödeme entity’sine özel metotlar burada tanımlanır
    public function getPaymentByOrder(int $orderId);
    public function updateStatus(int $id, string $status);
}
