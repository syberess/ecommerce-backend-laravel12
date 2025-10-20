<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Core\Interfaces\IOrderRepository;
use App\Core\Entities\Order;
use App\Core\Entities\OrderItem;
use App\Core\Entities\OrderStatusLog;
use App\Core\Entities\Product;
use App\Core\Entities\Cart;
use Exception;

class OrderService
{
    protected IOrderRepository $repository;

    public function __construct(IOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUserOrders()
    {
        return Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->get();
    }

    public function getAllOrders()
    {
        return Order::with(['user', 'items.product'])->get();
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $total = 0;

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => 0,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new Exception("Yetersiz stok: {$product->name}");
                }

                $product->decrement('stock', $item['quantity']);

                $price = $product->price * $item['quantity'];
                $total += $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ]);
            }

            $order->update(['total_price' => $total]);
            return $order->load('items.product');
        });
    }

    public function updateStatus($id, $status)
    {
        // Siparişi ve ürünlerini beraber çek
        $order = \App\Core\Entities\Order::with('items.product')->findOrFail($id);

        $oldStatus = $order->status;

        // Aynı statüye tekrar geçişte hiçbir şey yapma (idempotent)
        if ($oldStatus === $status) {
            return $order->load('items.product');
        }

        // Yeni statüyü yaz
        $order->update(['status' => $status]);

        // ❗İPTALDE STOK İADESİ (cancelled)
        // Sadece ilk kez cancelled'a düşüyorsa iade et (çift iade olmaz)
        if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                // ürün ilişkisi with('items.product') sayesinde yüklü
                $item->product->increment('stock', $item->quantity);
            }
        }

        // Durum logu
        \App\Core\Entities\OrderStatusLog::create([
            'order_id'   => $order->id,
            'changed_by' => \Illuminate\Support\Facades\Auth::id(), // istersen $order->user_id koy
            'old_status' => $oldStatus,
            'new_status' => $status,
        ]);

        return $order->load('items.product');
    }


    public function getOrderLogs($id)
    {
        return OrderStatusLog::with('user')
            ->where('order_id', $id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function createFromCart()
    {
        $user = Auth::user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Sepet boş'], 400);
        }

        return DB::transaction(function () use ($cart, $user) {
            $total = 0;

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => 0
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $price = $product->price * $item->quantity;
                $total += $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $price
                ]);

                $product->decrement('stock', $item->quantity);
            }

            $order->update(['total_price' => $total]);
            $cart->items()->delete();

            event(new \App\Events\OrderCreated($order));

            return $order->load('items.product');
        });
    }

    // 🔹 Generic repository üzerinden kullanıcı siparişlerini getir
    public function getByUserId(int $userId)
    {
        return $this->repository->getAllByUser($userId);
    }
}
