<?php

namespace App\Http\Middleware;

use App\Models\Orders;
use App\Models\UsersTokens;
use Closure;
use Illuminate\Http\Request;

class OrderOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $orderId = $request['orderId'];

        $token = $request->header('Authorization');
        $userToken = UsersTokens::where('token', $token)->orderBy('id', 'desc')->first();

        $order = Orders::find($orderId);

        if($order->user_id == $userToken->user_id)
            return $next($request);
        else
            return response('Заявка не пренадлежит вам', 403);
    }
}
