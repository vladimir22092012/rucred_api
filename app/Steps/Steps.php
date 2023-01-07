<?php

namespace App\Steps;

use App\Models\UsersTokens;
use Illuminate\Http\Request;

abstract class Steps
{
    public static function getUserByToken($request)
    {
        $token = $request->cookie('token');
        $usersToken = UsersTokens::where('token', $token)->first();
        $userId = $usersToken->user_id;

        return $userId;
    }

    abstract public function action(Request $request);
}