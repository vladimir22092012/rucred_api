<?php

namespace App\Info;

use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use Illuminate\Http\Request;

class Products extends Info
{
    public static function get(Request $request)
    {
        $group_id = $request->group_id ?? false;

        $result = [];
        if ($group_id) {
            $groupLoanType = GroupsLoantypes::getGroupPercents($group_id);
            $tariffs = Loantypes::all();
            $groupPercents = [];
            foreach ($groupLoanType as $key => $value) {

                $groupPercents[$value->loantype_id] = [
                    'percent'    => $value->standart_percents,
                    'profunion'  => $value->preferential_percents,
                    'individual' => $value->individual
                ];
            }

            foreach ($tariffs as $key => $tariff) {
                if ($tariff->online_flag == 2) {
                    continue;
                }
                $data = [
                    'id'          => $tariff->id,
                    'name'        => $tariff->name,
                    'number'      => $tariff->number,
                    'percent'     => $groupPercents[$tariff->id]['percent'],
                    'min_amount'  => $tariff->min_amount,
                    'max_amount'  => (!empty($groupPercents[$tariff->id]['individual'])) ? $groupPercents[$tariff->id]['individual'] : $tariff->max_amount,
                    'max_period'  => $tariff->max_period,
                    'profunion'   => $groupPercents[$tariff->id]['profunion'],    //% для членов профсоюза
                    'type'        => $tariff->type,
                    'description' => $tariff->description,
                ];
                $result[$key] = $data;
            }

        } else {
            $tariffs = Loantypes::all();
            foreach ($tariffs as $key => $tariff) {
                if ($tariff->online_flag == 2) {
                    continue;
                }
                $data = [
                    'id'          => $tariff->id,
                    'name'        => $tariff->name,
                    'number'      => $tariff->number,
                    'percent'     => $tariff->percent,
                    'min_amount'  => $tariff->min_amount,
                    'max_amount'  => $tariff->max_amount,
                    'max_period'  => $tariff->max_period,
                    'profunion'   => $tariff->profunion,    //% для членов профсоюза
                    'type'        => $tariff->type,
                    'description' => $tariff->description,
                ];
                $result[$key] = $data;
            }
        }

        return response(['resp' => $result], 200);
    }
}