<?php

namespace App\Core\Services;

use App\Core\Interfaces\IPaymentRepository;
use App\Events\PaymentCompleted;

class PaymentService
{
    protected IPaymentRepository $repository;

    public function __construct(IPaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $validated)
    {
        $validated['status'] = 'pending';
        return $this->repository->create($validated);
    }

    public function getByOrder(int $orderId)
    {
        return $this->repository->getPaymentByOrder($orderId);
    }

    public function updateStatus(int $id, string $status)
    {
        $payment = $this->repository->updateStatus($id, $status);

        if ($status === 'paid') {
            event(new PaymentCompleted($payment));
        }

        return $payment;
    }
}
