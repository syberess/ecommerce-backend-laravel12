<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Services\PaymentService;

class PaymentController extends Controller
{
    protected PaymentService $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    /**
     * Yeni ödeme oluşturur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:credit_card,cash,transfer',
        ]);

        $payment = $this->service->create($validated);
        return response()->json($payment, 201);
    }

    /**
     * Siparişe göre ödeme bilgilerini getirir.
     */
    public function show(int $orderId)
    {
        $payment = $this->service->getByOrder($orderId);
        return response()->json($payment ?? ['message' => 'Payment not found'], $payment ? 200 : 404);
    }

    /**
     * Ödeme durumunu günceller.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,failed',
        ]);

        $payment = $this->service->updateStatus($id, $validated['status']);
        return response()->json($payment);
    }
}
