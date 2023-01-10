<?php

namespace App\Account;

use App\Models\UsersTokens;

abstract class Account
{
    public static function getUserByToken($request)
    {
        $token = $request->header('Authorization');

        $usersToken = UsersTokens::where('token', $token)->first();
        $userId = $usersToken->user_id;

        return $userId;
    }
}