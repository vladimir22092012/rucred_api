<?php

namespace App\Components;


use App\Models\UsersTokens;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class Cookies
{
    public static function setToken()
    {
        $rand = rand(1, 999999);
        $newToken = md5((string)$rand);
        $minutes = 180;
        $response = new Response('Set Cookie');
        $response->withCookie(cookie('token', $newToken, $minutes));
        return $newToken;
    }

    public static function getToken()
    {

    }

    public static function deleteToken($userId)
    {
        UsersTokens::where('user_id', $userId)->delete();
        Cookie::queue(Cookie::forget('token'));

        return 'success';
    }
}