<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderPaidEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaidEvent $event): void
    {
         // Kirim email ke user
                Mail::to($event->order->user->email)
            ->send(new OrderPaid($event->order));

        // Opsional: Kirim notif ke Admin juga
    }

    // Retry jika gagal
    public $tries = 3;

}
