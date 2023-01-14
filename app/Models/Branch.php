<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 's_branches';
    protected $guarded = [];

    public static function getDefault($company_id, $group_id) {
      $branch = self::where('company_id', $company_id)
                        ->where('group_id', $group_id)
                        ->where('name', 'like', 'По умолчанию')
                      ->first();

      return $branch;
    }

    public function company()
    {
      return $this->belongsTo(Companies::class);
    }

}