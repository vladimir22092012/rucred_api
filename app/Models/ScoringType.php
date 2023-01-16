<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ScoringType extends Model
{
    protected $table = 's_scoring_types';
    protected $guarded = [];
    public $timestamps = false;

    public static function getTypes() {
        
        $types = self::where('active', 1)
                        ->get();

        return $types;
    }

}