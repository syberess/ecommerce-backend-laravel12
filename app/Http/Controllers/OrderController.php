<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Services\OrderService;

class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $userId = auth()->id();
        $orders = $this->service->getByUserId($userId);
        return response()->json($orders);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = $this->service->createOrder($validated);
        return response()->json($order, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled'
        ]);

        $order = $this->service->updateStatus($id, $validated['status']);
        return response()->json($order);
    }

    public function all()
    {
        return response()->json($this->service->getAllOrders());
    }

    public function logs($id)
    {
        $logs = $this->service->getOrderLogs($id);
        return response()->json($logs);
    }

    public function show($id)
    {
        $order = \App\Core\Entities\Order::with(['items.product'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    public function createFromCart()
    {
        $result = $this->service->createFromCart();
        return response()->json($result, 201);
    }

    public function getByUser(Request $request)
    {
        $userId = $request->query('user_id');

        $orders = $this->service->getByUserId($userId);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($orders);
    }



}
