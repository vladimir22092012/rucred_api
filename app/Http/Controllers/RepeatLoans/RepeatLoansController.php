<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Http\Controllers\Controller;
use App\Models\UsersTokens;
use Illuminate\Http\Request;

class RepeatLoansController extends Controller
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