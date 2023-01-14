<?php

namespace App\Http\Controllers\AccountControllers;

use App\Account\LoansOperations;
use App\Models\Orders;
use Illuminate\Http\Request;

class LoansOperationsController extends AccountController
{
    public function get(Request $request)
    {
        $orderId = $request['orderId'];

        if(!intval($orderId))
            return response('Не число', 400);

        $operations = LoansOperations::get($orderId);

        return response($operations, 200);
    }
}
