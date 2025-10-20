<?php

namespace App\Notifications;

use App\Core\Entities\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Siparişiniz başarıyla oluşturuldu!')
            ->greeting('Merhaba, ' . $notifiable->name . ' 👋')
            ->line('Sipariş numaranız: #' . $this->order->id)
            ->line('Toplam tutar: ₺' . number_format($this->order->total_price, 2))
            ->action('Siparişi Görüntüle', url('/orders/' . $this->order->id))
            ->line('E-Ticaret uygulamamızı kullandığınız için teşekkürler!');
    }
}
