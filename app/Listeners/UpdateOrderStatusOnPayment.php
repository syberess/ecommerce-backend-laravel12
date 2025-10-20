<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Core\Entities\Order;
use App\Core\Entities\OrderStatusLog;

class UpdateOrderStatusOnPayment
{
    public function handle(PaymentCompleted $event): void
    {
        $order = Order::find($event->payment->order_id);

        if ($order && $order->status !== 'completed') {
            $old = $order->status;
            $order->update(['status' => 'completed']);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'changed_by' => auth()->id() ?? 1,
                'old_status' => $old,
                'new_status' => 'completed'
            ]);
        }
    }
}
