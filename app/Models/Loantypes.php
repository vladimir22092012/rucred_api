<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loantypes extends Model
{
    protected $table = 's_loantypes';
    protected $guarded = [];

    public static function getLoantype($id)
    {

        $loanType = self::select('id', 'name', 'organization_id', 'percent', 'max_amount', 'max_period', 'min_amount', 'profunion', 'online_flag', 'reason_flag', 'number')
            ->where('id', $id)
            ->first();

        return $loanType;
    }
}
