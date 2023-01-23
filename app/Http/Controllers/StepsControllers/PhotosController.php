<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Files;
use App\Models\Users;
use Illuminate\Http\Request;

class PhotosController extends StepsController
{
    public function action(Request $request)
    {
        $userId = self::$userId;
        $types = $request['type'];

        $files = $request->file('file');

        for ($i = 0; $i <= 2; $i++) {

            $size = $files[$i]->getSize();

            $size /= 1048576;

            if ($size > 10)
                return response(['message' =>'Файл ' . $types[$i] . ' превышает размер в 10 Мегабайт', 'type' => $types[$i]], 400);

            $ext = pathinfo($files[$i]->getFilename(), PATHINFO_EXTENSION);
            $new_filename = md5(microtime() . rand()) . '.' . $ext;

            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId, 0777, true);
            }

            $files[$i]->move($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId . '/' . $new_filename);

            Files::updateOrCreate(
                ['user_id' => $userId, 'type' => $types[$i]],
                ['name' => $new_filename, 'status' => 0]
            );

            //Удаление старых фото со статусом отклонено (3)
            Files::delOldPhotos($userId, $types[$i]);
        }

        Users::where('id', $userId)->update(['stage_registration' => 7]);

        return response('success', 200);
    }
}