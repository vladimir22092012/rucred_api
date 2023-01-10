<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    protected $table = 's_documents';
    public $timestamps = false;
    protected $guarded = [];

    public static function getDocuments($userId) {

        $documents = self::select('id', 'name', 'order_id', 'type', 'created', 'hash')
            ->where('user_id', $userId)
            ->where('client_visible', 1)
            ->whereNotIn('type', ['SOGLASIE_NA_OBR_PERS_DANNIH_OBL'])
            ->where('order_id', '>', 0)
            ->get();

        return $documents;
    }

    public static function getAllDocuments($userId) {

        $documents = self::where('user_id', $userId)
            ->get();

        return $documents;
    }

    public static function getEndRegDocuments($orderId, $type) {

        $documents = self::where('order_id', $orderId)
            ->whereIn('type', $type)
            ->get();

        return $documents;
    }

    public static function getRegDocuments($userId) {

        $documents = self::where('user_id', $userId)
            ->where('order_id', 0)
            ->get();

        return $documents;
    }

    public static function getParamsDocument($hash) {

        $document = self::select('id', 'user_id', 'name', 'template', 'params')
            ->where('hash', $hash)
            ->first();

        return $document;
    }
}
