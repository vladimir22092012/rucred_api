<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    protected $table = 's_files';
    protected $guarded = [];
    public $timestamps = false;

    public static function getPhotos($userId)
    {

        $arrPhoto = [
            'Паспорт: разворот',
            'Паспорт: регистрация',
            'Селфи с паспортом'
        ];

        $documents = self::select('type', 'name', 'status')
            ->where('user_id', $userId)
            ->whereIn('type', $arrPhoto)
            ->get();

        return $documents;
    }

    public static function delOldPhotos($userId, $type)
    {
        self::where('user_id', $userId)
            ->where('type', $type)
            ->where('status', 3)
            ->delete();
    }
}
