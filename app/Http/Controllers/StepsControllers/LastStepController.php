<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Documents;
use App\Models\Orders;
use Illuminate\Http\Request;

class LastStepController extends StepsController
{
    public function action(Request $request)
    {
        $userId = self::$userId;

        $type = [
            'INDIVIDUALNIE_USLOVIA_ONL',
            'GRAFIK_OBSL_MKR',
            'PERECHISLENIE_ZAEMN_SREDSTV',
            'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR',
            'OBSHIE_USLOVIYA'
        ];

        $order = Orders::getUnfinished($userId);

        $docs = Documents::getEndRegDocuments($order->id, $type);
        $res = [];

        foreach ($docs as $key => $doc) {
            $res[$key] = [
                'name' => $doc->name,
                'link' => env('URL_CRM') . 'online_docs?id=' . $doc->hash
            ];
        }

        return response($res, 200);
    }
}