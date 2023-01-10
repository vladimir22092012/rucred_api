<?php

namespace App\Http\Controllers;

use App\Models\UsersTokens;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected static $userId;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        $usersToken = UsersTokens::where('token', $token)->first();
        self::$userId = $usersToken->user_id;
    }
}
