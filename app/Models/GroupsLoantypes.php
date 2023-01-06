<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupsLoantypes extends Model
{
    protected $table = 's_group_loantypes';
    protected $guarded = [];

    public static function getPercents($groupId, $loantypeId) {

        $percents = self::select('standart_percents', 'preferential_percents')
            ->where('group_id', $groupId)
            ->where('loantype_id', $loantypeId)
            ->first();

        return $percents;
    }

    public static function getGroupPercents($groupId) {

        $percents = self::select('loantype_id', 'standart_percents', 'preferential_percents', 'individual')
            ->where('group_id', $groupId)
            ->get();

        return $percents;
    }
}
