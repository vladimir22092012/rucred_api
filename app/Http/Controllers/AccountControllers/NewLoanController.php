<?php

namespace App\Http\Controllers\AccountControllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class NewLoanController extends AccountController
{
    public function checkAvailable()
    {
        $unfinishedOrder = Orders::where('user_id', self::$userId)
            ->where('is_archived', 0)
            ->where('unreability', 0)
            ->whereIn('status', [0,1,2,4,10,13,14])
            ->first();

        if(!empty($unfinishedOrder))
            $canSendNew = 0;
        else
            $canSendNew = 1;

        return response($canSendNew, 200);
    }
}