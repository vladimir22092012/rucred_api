<?php

namespace App\Http\Controllers\AccountControllers;

use App\Models\UsersTokens;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    protected static $userId;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        $usersToken = UsersTokens::where('token', $token)->first();

        if(!empty($usersToken))
            self::$userId = $usersToken->user_id;
        else
            return response('Не прошел проверку подлинности', 401);

    }
}
