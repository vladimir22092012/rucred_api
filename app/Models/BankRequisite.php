<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankRequisite extends Model
{
    protected $table = 's_bank_requisites';
    protected $guarded = [];
    public $timestamps = false;

    public static function getAccounts($userId) {
        $accounts = BankRequisite::where('user_id', $userId)
            ->get();

        return $accounts;
    }

    public static function getDefault($userId) {
        $account = BankRequisite::where('user_id', $userId)
            ->where('default', 1)
            ->first();
        if (is_null($account)) {
            $account = BankRequisite::where('user_id', $userId)
                ->first();
        }

        return $account;
    }

    //"обнуление" всех счетов по умолчанию
    public static function setZeroDefault($userId) {
        BankRequisite::where('user_id', $userId)
            ->update(['default' => 0]);
    }

    public static function checkNumber($number)
    {
        $account = BankRequisite::select('user_id')
            ->where('number', $number)
            ->first();

        return $account;
    }
}
