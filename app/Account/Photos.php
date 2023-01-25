<?php

namespace App\Account;

use App\Models\Files;
use Illuminate\Http\Request;

class Photos extends Account
{
    public function get(Request $request)
    {
        $userId = self::$userId;

        $photos = Files::getPhotos($userId);
        $res = [];

        foreach ($photos as $key => $photo) {
            $res[$key] = [
                'type' => $photo->type,
                'status' => $photo->status,
                'link' => env('URL_CRM') . 'files/users/' . $userId . '/' . $photo->name
            ];
        }

        return response($res, 200);
    }

    public function add(Request $request)
    {
        $userId = self::$userId;
        $type = $request['type'];

        $file = $request->file('file');


        $size = $file->getSize();

        $size /= 1048576;

        if ($size > 10)
            return response(['message' => 'Файл ' . $type . ' превышает размер в 10 Мегабайт', 'type' => $type], 400);

        $ext = $file->extension();

        $approvedFormat =
            [
                'jpeg',
                'png',
                'jpg'
            ];

        if (!in_array($ext, $approvedFormat))
            return response(['message' => 'Файл ' . $type . ' имеет не поддерживаемый формат', 'type' => $type], 400);

        $new_filename = md5(microtime() . rand()) . '.' . $ext;

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId))
            mkdir($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId, 0777, true);

        $file->move($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId . '/', $new_filename);

        Files::where('user_id', $userId)->where('type', $type)->delete();

        Files::updateOrCreate(
            ['user_id' => $userId, 'type' => $type],
            ['name' => $new_filename, 'status' => 0]
        );


        return response('success', 200);
    }
}