<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Mail\OrderPaidMail; 
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderPaid
{
    public function handle(OrderPaidEvent $event): void
    {
        // Akses data order dari event
        $order = $event->order;

        // Contoh: Log status ke terminal
        info("Order dengan ID {$order->id} telah dibayar!");
    }
}

