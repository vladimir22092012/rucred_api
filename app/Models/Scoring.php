<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Scoring extends Model
{
    protected $table = 's_scorings';
    protected $guarded = [];
    public $timestamps = false;

    public static function addScorings($userId, $orderId) {

        $scoringTypes = ScoringType::getTypes();

        foreach ($scoringTypes as $key => $scoring) {
            //запускаем только бесплатные скоринги
            if (in_array($scoring->name, [
                'fns',
                'rfm_first_list',
                'rfm_second_list',
                'rfm_third_list',
                'rfm_fourth_list',
            ]) && $scoring->is_paid == 0) {
                $data = [
                    'user_id'  => $userId,
                    'order_id' => $orderId,
                    'type'     => $scoring->name,
                    'status'   => 'new',
                    'created'  => date('Y-m-d H:i:s'),
                ];
                Scoring::insert($data);
            }
        }

    }

}
