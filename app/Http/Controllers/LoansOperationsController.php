<?php

namespace App\Http\Controllers;

use App\Account\LoansOperations;
use App\Models\Orders;
use Illuminate\Http\Request;

class LoansOperationsController extends AccountController
{
    public function get(Request $request)
    {
        $orderId = $request['orderId'];

        if(!intval($orderId))
            return ['status' => 500, 'resp' => 'is not int'];

        $operations = LoansOperations::get($orderId);

        return ['status' => 200, 'resp' => $operations];
    }
}
