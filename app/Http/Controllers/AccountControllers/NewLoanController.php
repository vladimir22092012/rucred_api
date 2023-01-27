<?php

namespace App\Http\Controllers\AccountControllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class NewLoanController extends AccountController
{
    public function checkAvailable()
    {
        $unfinishedOrder = Orders::getUnfinished(self::$userId);

        if(!empty($unfinishedOrder))
            $canSendNew = 0;
        else
            $canSendNew = 1;

        return response($canSendNew, 200);
    }
}