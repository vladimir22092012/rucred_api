<?php

namespace App\Policies;

use App\Models\Orders;
use App\Models\UsersTokens;
use Illuminate\Http\Request;

class OrderOwner
{
    public function __construct(Request $request)
    {
        $orderId = $request['orderId'];

        $order = Orders::find($orderId);

        $token = $request->header('Authorization');

        $usersToken = UsersTokens::where('token', $token)->first();

        return $order->user_id == $usersToken->user_id;
    }
}
