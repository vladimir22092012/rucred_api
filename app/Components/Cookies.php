<?php

namespace App\Components;


use App\Models\UsersTokens;
use Illuminate\Http\Response;

class Cookies
{
    public static function setToken()
    {
        $rand = rand(1, 999999);
        $newToken = md5((string)$rand);
        $minutes = 180;
        $response = new Response('Set Cookie');
        $response->withCookie(cookie('name', $newToken, $minutes));
        return $response;
    }

    public static function getToken()
    {

    }

    public static function deleteToken($userId)
    {
        UsersTokens::where('user_id', $userId)->delete();

        return 'success';
    }
}