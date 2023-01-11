<?php

namespace App\Entity;


use App\Models\Users;
use App\Models\UsersTokens;
use Illuminate\Http\Request;

class Cookies
{
    public static function checkToken(Request $request)
    {
        $token = $request->header('Authorization');
        $validToken = 'valid';

        $userToken = UsersTokens::where('token', $token)->orderBy('id', 'desc')->first();

        if (empty($userToken))
            $validToken = 'invalid';

        if (!empty($userToken) && $userToken->is_expired == 1)
            $validToken = 'invalid';


        return ['status' => 200, 'resp' => $validToken];
    }

    public static function setToken(Request $request)
    {
        $user = Users::where('phone_mobile', $request['phone'])->first();
        $step = $request['step'];

        if ($step == 'auth') {
            if (empty($user))
                return ['status' => 500, 'resp' => 'Такого юзера нет'];
            else
            {
                self::doExpireTokens($user->id);
                $userId = $user->id;
            }

        }else
            $userId = 0;

        $rand = rand(1, 999999);
        $newToken = md5((string)$rand);

        $insert =
            [
                'token' => $newToken,
                'user_id' => $userId
            ];

        UsersTokens::insert($insert);

        return ['status' => 200, 'resp' => $newToken];
    }

    public static function doExpireTokens($userId)
    {
        UsersTokens::where('user_id', $userId)->update(['is_expired' => 1]);
        return 'success';
    }
}