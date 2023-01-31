<?php

namespace App\Http\Controllers\StepsControllers;

use App\Http\Controllers\Controller;
use App\Models\UsersTokens;
use Illuminate\Http\Request;

date_default_timezone_set('Europe/Moscow');

class StepsController extends Controller
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