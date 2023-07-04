<?php

namespace App\Entity;

use App\Models\AspCode;
use App\Models\CommunicationTheme;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\NotificationCron;
use App\Models\Orders;
use App\Models\OtherDocuments;
use App\Models\Ticket;
use App\Models\TicketsMessages;
use App\Models\Users;
use App\Models\UsersTokens;
use App\Models\YaDiskCron;
use Aspose\Words\WordsApi;
use Illuminate\Http\Request;
use App\Models\SmsMessages as SmsDB;
use Illuminate\Support\Facades\Cookie;

date_default_timezone_set('Europe/Moscow');

class Sms
{

    protected static $login = 'RuCred';
    protected static $password = 'Ee6-eEF-w7f';


    public function send(Request $request)
    {
        $phone = $request['phone'];
        $step = $request['step'];

        if (empty($phone))
            return response('Не заполнен параметр phone', 400);

        $phone = self::clear_phone($phone);

        $user = Users::where('phone_mobile', $phone)->first();

        if (empty($user) && $step == 'auth')
            return response('Такого клиента нет', 404);


        $code = rand(1000, 9999);

        $message = "Ваш код подтверждения телефона на сайте Рестарт.Онлайн:  $code";

        if ($step == 'reg-doc') {
            $message = "Ваш код для подписания документов на сайте Рестарт.Онлайн:  $code";

            if (!empty($user)) {

                $sendTo = $user->email;
                $title = 'RuCred | Ваш код для подписания ';
                $htmlMsg = '<h1>Ваш код для подписания документов на сайте Рестарт.Онлайн: </h1>' . "<h2>$code</h2>";

                Mail::send($sendTo, $title, $htmlMsg);
            }
        }

        $url = 'http://smsc.ru/sys/send.php?login=' . self::$login . '&psw=' . self::$password . '&phones=' . $phone . '&mes=' . $message . '';

        $resp = file_get_contents($url);

        $data = [
            'phone' => $phone,
            'user_id' => 0,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'code' => $code,
            'type' => 'sms',
            'response' => $resp,
            'created' => date('Y-m-d H:i:s')
        ];

        SmsDB::insert($data);
        $msg = $resp;


        return response($msg, 200);
    }

    public function check(Request $request)
    {
        $phone = $request['phone'];
        $code = $request['code'];
        $step = $request['step'];
        $typeClient = $request['typeClient'];

        if (empty($phone))
            return response('Не заполнен параметр phone', 400);

        if (empty($code))
            return response('Не заполнен параметр code', 400);

        $checkCode = SmsDB::getCode($phone);

        if ($checkCode != $code)
            return response('Введеный код не совпадает с отправленным', 406);

        $user = Users::where('phone_mobile', $phone)->first();


        if (!empty($user)) {
            Cookies::doExpireTokens($user->id);
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        $rand = rand(1, 999999);
        $newToken = md5((string)$rand);

        $insert =
            [
                'token' => $newToken,
                'user_id' => $userId
            ];

        UsersTokens::insert($insert);

        if (!empty($userId)) {
            $uid = rand(000000000, 999999999);

            $order = Orders::getUnfinished($userId);

            $aspData = [
                'user_id' => $userId,
                'code' => $code,
                'recepient' => $phone,
                'type' => 'sms',
                'order_id' => !empty($order) ? $order->id : null,
                'created' => date('Y-m-d H:i:s'),
                'uid' => $uid
            ];

            $aspCode = new AspCode($aspData);
            $aspCode->save();
        }

        if (!empty($user) && $user->stage_registration == 8 && $step == 'reg')
            return response($newToken, 301);
        elseif (!empty($user) && $user->stage_registration != 8 && $step == 'reg')
            return response(['stage' => $user->stage_registration, 'token' => $newToken], 302);
        elseif (!empty($user) && $step == 'endReg') {

            if (empty($typeClient))
                return response('Не заполнен параметр typeClient', 400);

            Documents::where('order_id', $order->id)->delete();

            Users::where('id', $user->id)->update(['stage_registration' => 8]);

            Documents::createDocsForRegistration($userId, $order->id);
            Documents::createDocsAfterRegistrarion($userId, $order->id);
            Documents::createDocsEndRegistrarion($userId, $order->id);

            if ($typeClient == 'new') {
                $documents = Documents::where('order_id', $order->id)->get();
                $lastAsp = AspCode::where('order_id', $order->id)->orderBy('id', 'DESC')->first();
                $middleAsp = AspCode::find($lastAsp->id - 1);

                $lastPak = [
                    'INDIVIDUALNIE_USLOVIA_ONL',
                    'GRAFIK_OBSL_MKR',
                    'PERECHISLENIE_ZAEMN_SREDSTV',
                    'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR',
                    'OBSHIE_USLOVIYA'
                ];

                foreach ($documents as $document) {
                    if (in_array($document->type, $lastPak))
                        Documents::where('id', $document->id)->update(['asp_id' => $lastAsp->id]);
                    else
                        Documents::where('id', $document->id)->update(['asp_id' => $middleAsp->id]);
                }
            }

            Contracts::where('order_id', $order->id)->update(['deal_date' => date('Y-m-d')]);

            Orders::where('id', $order->id)->update(['status' => 0]);
            Contracts::where('order_id', $order->id)->update(['deal_date' => date('Y-m-d H:i:s')]);

            $communicationTheme = CommunicationTheme::find(18);
            $ticket = [
                'creator' => 0,
                //'creator_company'   => 2,
                'client_lastname' => $user->lastname,
                'client_firstname' => $user->firstname,
                'client_patronymic' => $user->patronymic,
                'head' => $communicationTheme->head,
                'text' => $communicationTheme->text,
                'theme_id' => 18,
                'company_id' => $order->company_id,
                'group_id' => 2,//$order->group_id,
                'order_id' => $order->id,
                'status' => 0
            ];

            $tiketId = Ticket::insertGetId($ticket);

            //Сообщение в тикет
            $message =
                [
                    'message' => $communicationTheme->text,
                    'ticket_id' => $tiketId,
                    'manager_id' => 0
                ];

            TicketsMessages::insertGetId($message);

            //Добавляем в расписание крон
            $cron = [
                'ticket_id' => $tiketId,
                'is_complited' => 0
            ];
            NotificationCron::insert($cron);

            return response($newToken, 200);
        } else
            return response($newToken, 200);
    }

    public static function sendTelegramLink($phone, $message)
    {
        $login = 'RuCred';
        $password = 'Ee6-eEF-w7f';

        $phone = self::clear_phone($phone);

        $url = 'http://smsc.ru/sys/send.php?login=' . $login . '&psw=' . $password . '&phones=' . $phone . '&mes=' . $message . '';

        $resp = file_get_contents($url);

        return $resp;
    }

    public static function clear_phone($phone)
    {
        $remove_symbols = [
            '(',
            ')',
            '-',
            ' ',
            '+'
        ];
        return str_replace($remove_symbols, '', $phone);
    }

    public function send_other_docs(Request $request)
    {
        $doc_id = $request['doc_id'];

        if (empty($doc_id))
            return response('Не заполнен параметр doc_id', 400);

        $doc = OtherDocuments::query()->where('id', '=', $doc_id)->first();
        if (empty($doc))
            return response('Не найден документ', 400);

        $user = Users::query()->where('id', '=', $doc->user_id)->first();

        if (empty($user))
            return response('Такого клиента нет', 404);


        $code = rand(1000, 9999);

        $message = "Ваш код подтверждения телефона на сайте Рестарт.Онлайн:  $code";

        $url = 'http://smsc.ru/sys/send.php?login=' . self::$login . '&psw=' . self::$password . '&phones=' . $user->phone_mobile . '&mes=' . $message . '';

        $resp = file_get_contents($url);

        $doc->update([
            'asp' => $code,
        ]);

        $msg = $resp;


        return response($msg, 200);
    }

    public function check_other_docs(Request $request) {

        $doc_id = $request['doc_id'];
        $code = $request['code'];
        if (empty($doc_id))
            return response(['message' => 'Не заполнен параметр doc_id'], 400);

        if (empty($code))
            return response(['message' => 'Не заполнен параметр code'], 400);
        $document = OtherDocuments::query()->where('id', '=', $doc_id)->first();
        if (empty($document))
            return response(['message' => 'Не найден документ'], 400);

        if (!empty($document->created_at_asp))
            return response(['message' => 'Документ уже был подписан'], 400);

        if ($document->asp != $code)
            return response(['message' => 'Введеный код не совпадает с отправленным'global ], 406);

        try {

            $document->update([
                'created_at_asp' => date('Y-m-d H:i:s')
            ]);
            $user = Users::query()->where(['id' => $document->user_id])->first();
            $fio = "{$user->lastname}-{$user->firstname}-{$user->patronymic}";
            $command = "/bin/python3 /home/rucred/laravel-api/laravelapi/edit_pdf.py {$user->id} {$fio} {$document->asp} {$document->path}";
            exec($command, $output);
            return response([
                'message' => 'Документ подписан успешно!',
                'command' => $command,
                'output' => $output
            ], 200);
        } catch (\Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ], 400);
        }
    }
}
