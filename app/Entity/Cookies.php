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

        if(empty($userToken))
            $validToken = 'invalid';

        if (!empty($userToken) && $userToken->is_expired == 1)
            $validToken = 'invalid';


        return ['status' => 200, 'resp' => $validToken];
    }

    public static function setToken(Request $request)
    {
        $user = Users::where('phone_mobile', $request['phone'])->first();

        if(empty($user))
            return ['status' => 500, 'resp' => 'Такого юзера нет'];

        self::doExpireTokens($user->id);

        $rand = rand(1, 999999);
        $newToken = md5((string)$rand);

        $insert =
            [
                'token' => $newToken,
                'user_id' => $user->id
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