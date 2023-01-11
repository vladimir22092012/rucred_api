<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 's_users';
    protected $guarded = [];
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(Orders::class, 'user_id','id');
    }

    public static function getProfile($userId)
    {
        //todo: добавить недостающие поля(титан id, соц сети, реквизиты работы и т.д.)
        $profile = self::select('lastname', 'firstname', 'patronymic',
            'phone_mobile', 'company_id', 'inn', 'snils', 'regaddress_id', 'faktaddress_id', 'birth', 'birth_place')
            ->where('id', $userId)
            ->first();

        return $profile;
    }

    public static function getLastPersonalNumber()
    {

        $number = self::max('personal_number');

        return $number;
    }

    public static function checkPassport($passport_serial)
    {

        $user = self::select('id')
            ->where('passport_serial', $passport_serial)
            ->first();

        return $user;
    }

    public static function checkSnils($snils)
    {

        $user = self::select('id')
            ->where('snils', $snils)
            ->first();

        return $user;
    }

    public static function checkInn($inn)
    {

        $user = self::select('id')
            ->where('inn', $inn)
            ->first();

        return $user;
    }
}
