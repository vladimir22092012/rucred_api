<?php

namespace App\Account;

use App\Models\Operations;

class LoansOperations extends Account
{
    public static function get($orderId)
    {
        $operations = Operations::where('order_id', $orderId)->get();
        return $operations;
    }
}