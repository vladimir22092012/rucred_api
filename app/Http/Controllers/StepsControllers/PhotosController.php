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
        $type = $request['type'];

        $file = $request->file('file');

        $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        $new_filename = md5(microtime() . rand()) . '.' . $ext;

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId, 0777, true);
        }

        $file->move($_SERVER['DOCUMENT_ROOT'] . "/../files/users/" . $userId . '/' . $new_filename);

        Files::updateOrCreate(
            ['user_id' => $userId, 'type' => $type],
            ['name' => $new_filename, 'status' => 0]
        );

        //Удаление старых фото со статусом отклонено (3)
        Files::delOldPhotos($userId, $type);

        Users::where('id' , $userId)->update(['stage_registration' => 7]);

        return response(env('URL_CRM').'files/users/' . $userId.'/'.$new_filename, 200);
    }
}