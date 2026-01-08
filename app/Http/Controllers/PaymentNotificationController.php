<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentNotificationController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Midtrans Notification', $request->all());

        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (
            $transactionStatus === 'capture' ||
            $transactionStatus === 'settlement'
        ) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        } elseif (
            in_array($transactionStatus, ['deny', 'expire', 'cancel'])
        ) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
        }

        return response()->json(['message' => 'OK']);
    }
}
