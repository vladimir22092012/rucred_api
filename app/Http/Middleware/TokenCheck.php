<?php

namespace App\Http\Middleware;

use App\Models\UsersTokens;
use Closure;
use Illuminate\Http\Request;

class TokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        $validToken = 'valid';
        $userToken = UsersTokens::where('token', $token)->orderBy('id', 'desc')->first();

        if (empty($userToken))
            $validToken = 'invalid';

        if (!empty($userToken) && $userToken->is_expired == 1)
            $validToken = 'invalid';

        if ($validToken == 'valid')
            return $next($request);
        elseif (empty($token))
            return response()->json(['status' => 404, 'resp' => 'token is empty']);
        else
            return response()->json(['status' => 401, 'resp' => 'token invalid']);
    }
}
