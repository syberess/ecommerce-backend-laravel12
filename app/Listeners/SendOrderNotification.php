<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderCompletedNotification;

class SendOrderNotification
{
    public function handle(OrderCreated $event)
    {
        $user = $event->order->user;
        if ($user) {
            $user->notify(new OrderCompletedNotification($event->order));
        }
    }
}
