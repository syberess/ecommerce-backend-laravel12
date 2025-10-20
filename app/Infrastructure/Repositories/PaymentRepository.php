<?php

namespace App\Infrastructure\Repositories;

use App\Core\Entities\Payment;
use App\Core\Interfaces\IPaymentRepository;

class PaymentRepository extends BaseRepository implements IPaymentRepository
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * Belirli bir siparişe ait ödemeyi getirir.
     */
    public function getPaymentByOrder(int $orderId)
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    /**
     * Ödeme durumunu günceller.
     */
    public function updateStatus(int $id, string $status)
    {
        $payment = $this->model->findOrFail($id);
        $payment->update(['status' => $status]);
        return $payment;
    }
}
