<?php

namespace App\Http\Controllers\AccountControllers;

use App\Models\Contacts;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\Orders;
use App\Models\OtherDocuments;

class DocumentsController extends AccountController
{
    public function get()
    {
        $docs = Documents::getDocuments(self::$userId);
        $other = OtherDocuments::query()->where('user_id', '=', self::$userId)->get();
        $res = [];

        if(empty($docs) && empty($other))
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
                'doc_id'   => null,
                'type'     => $type,
                'number'   => $number,
                'date'     => $doc->created,
                'order_id' => $doc->order_id,
                'name'     => $doc->name,
                'link'     => env('URL_CRM') . 'online_docs?id=' . $doc->hash,
                'asp'      => true,
            ];
        }

        foreach ($other as $doc) {

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

            $res[] = [
                'doc_id'   => $doc->id,
                'type'     => $type,
                'number'   => $number,
                'date'     => $doc->created_at,
                'order_id' => $doc->order_id,
                'name'     => $doc->name,
                'link'     => env('URL_CRM') . 'files/users/' . $doc->user_id . '/' . $doc->md5_name,
                'asp'      => $doc->created_at_asp != null ? true : false,
            ];
        }

        return response($res, 200);
    }

    public function send_other_docs() {
        return response(['test' => '23'], 200);
    }

}
