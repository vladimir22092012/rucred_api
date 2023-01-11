<?php

namespace App\Http\Controllers\StepsControllers;

use App\Http\Controllers\Controller;
use App\Models\UsersTokens;
use Illuminate\Http\Request;

class StepsController extends Controller
{
    protected static $userId;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        $usersToken = UsersTokens::where('token', $token)->first();
        self::$userId = $usersToken->user_id;
    }
}