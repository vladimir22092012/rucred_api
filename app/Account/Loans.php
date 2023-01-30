<?php

namespace App\Account;

use App\Models\Contracts;
use App\Models\Loantypes;
use App\Models\Orders;

class Loans extends Account
{
    public static function get()
    {
        $userId = self::$userId;
        $orders = Orders::getOrders($userId);

        $res = [];
        foreach ($orders as $key => $order) {

            $contract = Contracts::find($order->contract_id);

            $type = 'Заявка';
            $orderNumber = $order->uid;

            if ($order->contract_id && $contract->status >= 2) {
                $type = 'Микрозайм';
                $orderNumber = $contract->number;
            }

            $tariff = Loantypes::find($order->loan_type);
            $tariff = $tariff->name;

            $res[$key] = [
                'orderId' => $order->id,
                'number' => $orderNumber,
                'tariff' => $tariff,
                'amount' => $order->amount,
                'start_date' => ($type == 'Заявка') ? $order->probably_start_date : $contract->inssuance_date,
                'return_date' => $order->probably_return_date,
                'type' => $type,
                'date' => $order->date,
                'status' => $order->status,
                'is_archived' => $order->is_archived,
                'unreability' => $order->unreability
            ];
        }

        return response($res, 200);
    }
}