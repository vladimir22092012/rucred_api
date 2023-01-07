<?php

namespace App\Steps;

use Illuminate\Http\Request;

class Main extends Steps
{
    public function action(Request $request)
    {
        $userId = self::getUserByToken($request);
    }
}