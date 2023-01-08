<?php

namespace App\Account;

use App\Models\Files;
use Illuminate\Http\Request;

class Photos extends Account
{
    public function get(Request $request)
    {
        $userId = self::getUserByToken($request);
        $photos = Files::getPhotos($userId);
        $res = [];

        foreach ($photos as $key => $photo) {
            $res[$key] = [
                'type'     => $photo->type,
                'status'   => $photo->status,
                'link'     => $_ENV['URL_CRM'].'files/users/' . $userId.'/'.$photo->name
            ];
        }

        return ['status' => 200, 'resp' => $res];
    }
}