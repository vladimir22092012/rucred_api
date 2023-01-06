<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationSettlement extends Model
{
    protected $table = 's_organization_settlements';
    protected $guarded = [];

    public static function getDefault() {

        $settlement = OrganisationSettlement::where('std', 1)
            ->first();

        return $settlement;
    }
}
