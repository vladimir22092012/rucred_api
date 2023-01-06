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
        $token = $request->cookie('token');

        if (!empty($token)) {
            $aliveToken = UsersTokens::where('token', $token)->first();

            if (!empty($aliveToken))
                return $next($request);
        }
    }
}
