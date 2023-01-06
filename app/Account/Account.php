<?php

namespace App\Account;

use App\Models\UsersTokens;
use Illuminate\Http\Request;

abstract class Account
{
    public static function getUserByToken($request)
    {
        $token = $request->cookie('token');
        $usersToken = UsersTokens::where('token', $token)->first();
        $userId = $usersToken->user_id;

        return $userId;
    }
}