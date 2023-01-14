<?php

namespace App\Http\Controllers\AccountControllers;

use App\Models\Contacts;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\Orders;

class DocumentsController extends AccountController
{
    public function get()
    {
        $docs = Documents::getDocuments(self::$userId);
        $res = [];

        if(empty($docs))
            return response('Документы отсутствуют', 404);

        foreach ($docs as $key => $doc) {

            $contract = Contracts::where('order_id', $doc->order_id)->first();
            $order = Orders::where('id', $doc->order_id)->first();

            if(empty($contract)){
                $number = $order->uid;
                $type = 'Заявка';
            }else{
                $number = $contract->number;
                $type = 'Микрозайм';

                if (!in_array($contract->status, [2,3])) {
                    $type = 'Заявка';
                    $number = $order->uid;
                }
            }

            $res[$key] = [
                'type'     => $type,
                'number'   => $number,
                'date'     => $doc->created,
                'order_id' => $doc->order_id,
                'name'     => $doc->name,
                'link'     => env('URL_CRM') . 'online_docs?id=' . $doc->hash
            ];
        }

        return response($res, 200);
    }
}
