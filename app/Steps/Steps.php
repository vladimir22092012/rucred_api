<?php

namespace App\Steps;

use App\Models\UsersTokens;
use Illuminate\Http\Request;

abstract class Steps
{
    protected static $userId;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $usersToken = UsersTokens::where('token', $token)->first();
        self::$userId = $usersToken->user_id;
    }

    abstract public function action(Request $request);
}