<?php

namespace App\Models;

use Faker\Provider\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    protected $table = 's_documents';
    public $timestamps = false;
    protected $guarded = [];

    static $templates = array(
        'SOGLASIE_MINB'                         => 'soglasie_minb.tpl',
        'SOGLASIE_NA_KRED_OTCHET'               => 'soglasie_na_kred_otchet.tpl',
        'SOGLASIE_NA_OBR_PERS_DANNIH_OBL'       => 'pre_soglasie_na_obr_pers_dannih.tpl',
        'SOGLASIE_NA_OBR_PERS_DANNIH'           => 'soglasie_na_obr_pers_dannih.tpl',
        'SOGLASIE_RABOTODATEL'                  => 'soglasie_rukred_rabotadatel.tpl',
        'SOGLASIE_RDB'                          => 'soglasie_rdb.tpl',
        'SOGLASIE_RUKRED_RABOTODATEL'           => 'soglasie_rabotadatelu.tpl',
        'ZAYAVLENIE_NA_PERECHISL_CHASTI_ZP'     => 'zayavlenie_na_perechislenie_chasti_zp.tpl',
        'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR' => 'zayavlenie_zp_v_schet_pogasheniya_mrk.tpl',
        'INDIVIDUALNIE_USLOVIA_ONL'                 => 'ind_usloviya_online.tpl',
        'GRAFIK_OBSL_MKR'                       => 'grafik_obsl_mkr.tpl',
        'PERECHISLENIE_ZAEMN_SREDSTV'           => 'perechislenie_zaemnih_sredstv.tpl',
        'DOP_SOGLASHENIE'                       => 'dop_soglashenie.tpl',
        'DOP_GRAFIK'                            => 'dop_grafik.tpl',
        'OBSHIE_USLOVIYA'                       => 'obshie_uslovia.tpl'
    );


    static $names = array(
        'SOGLASIE_MINB'                         => 'Согласие на обработку персональных данных и упрощенную идентификацию через МИнБ',
        'SOGLASIE_NA_KRED_OTCHET'               => 'Согласие заемщика на получение кредитного отчета',
        'SOGLASIE_NA_OBR_PERS_DANNIH_OBL'       => 'Облегчённое согласие на обработку персональных данных',
        'SOGLASIE_NA_OBR_PERS_DANNIH'           => 'Согласие на обработку персональных данных',
        'SOGLASIE_RABOTODATEL'                  => 'Согласие работодателю на распространение персональных данных',
        'SOGLASIE_RDB'                          => 'Согласие на обработку персональных данных и упрощенную идентификацию через РДБ',
        'SOGLASIE_RUKRED_RABOTODATEL'           => 'Согласие на обработку персональных данных РуКредом и распространение работодателю',
        'ZAYAVLENIE_NA_PERECHISL_CHASTI_ZP'     => 'Обязательство на подачу заявления о перечислении части заработной платы на счёт третьего лица',
        'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR' => 'Заявление на перечисление части зп в счет обслуживания микрозайма',
        'INDIVIDUALNIE_USLOVIA_ONL'                 => 'Индивидуальные условия договора микрозайма',
        'GRAFIK_OBSL_MKR'                       => 'График платежей по микрозайму',
        'PERECHISLENIE_ZAEMN_SREDSTV'           => 'Заявление на перечисление заемных денежных средств',
        'DOP_SOGLASHENIE'                       => 'Дополнительное соглашение к Индивидуальным условиям договора микрозайма',
        'DOP_GRAFIK'                            => 'График платежей по микрозайму (после реструктуризации)',
        'OBSHIE_USLOVIYA'                       => 'Справка по основным условиям микрозайма',
    );

    static $client_visible = array(
        'SOGLASIE_MINB'                         => 1,
        'SOGLASIE_NA_KRED_OTCHET'               => 1,
        'SOGLASIE_NA_OBR_PERS_DANNIH_OBL'       => 1,
        'SOGLASIE_NA_OBR_PERS_DANNIH'           => 1,
        'SOGLASIE_RABOTODATEL'                  => 1,
        'SOGLASIE_RDB'                          => 1,
        'SOGLASIE_RUKRED_RABOTODATEL'           => 1,
        'ZAYAVLENIE_NA_PERECHISL_CHASTI_ZP'     => 1,
        'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR' => 1,
        'INDIVIDUALNIE_USLOVIA_ONL'             => 1,
        'GRAFIK_OBSL_MKR'                       => 1,
        'PERECHISLENIE_ZAEMN_SREDSTV'           => 1,
        'DOP_SOGLASHENIE'                       => 1,
        'DOP_GRAFIK'                            => 1,
        'OBSHIE_USLOVIYA'                       => 1,
    );

    //todo: поправить нумерацию
    static $numeration = array(
        'SOGLASIE_MINB'                         => '04.05.1',
        'SOGLASIE_NA_KRED_OTCHET'               => '04.07',
        'SOGLASIE_NA_OBR_PERS_DANNIH_OBL'       => '04.01.3',
        'SOGLASIE_NA_OBR_PERS_DANNIH'           => '04.05',
        'SOGLASIE_RABOTODATEL'                  => '03.03',
        'SOGLASIE_RDB'                          => '04.05.2',
        'SOGLASIE_RUKRED_RABOTODATEL'           => '04.06',
        'ZAYAVLENIE_NA_PERECHISL_CHASTI_ZP'     => '04.09',
        'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR' => '03.04',
        'INDIVIDUALNIE_USLOVIA_ONL'             => '04.03.1',
        'GRAFIK_OBSL_MKR'                       => '04.04',
        'PERECHISLENIE_ZAEMN_SREDSTV'           => '04.12',
        'DOP_SOGLASHENIE'                       => '0',
        'DOP_GRAFIK'                            => '0',
        'OBSHIE_USLOVIYA'                       => '04.10'
    );

    public static function getDocuments($userId) {

        $documents = self::select('id', 'name', 'order_id', 'type', 'created', 'hash')
            ->where('user_id', $userId)
            ->where('client_visible', 1)
            ->whereNotIn('type', ['SOGLASIE_NA_OBR_PERS_DANNIH_OBL'])
            ->where('order_id', '>', 0)
            ->get();

        return $documents;
    }

    public static function getAllDocuments($userId) {

        $documents = self::where('user_id', $userId)
            ->get();

        return $documents;
    }

    public static function getEndRegDocuments($orderId, $type) {

        $documents = self::where('order_id', $orderId)
            ->whereIn('type', $type)
            ->get();

        return $documents;
    }

    public static function getRegDocuments($userId) {

        $documents = self::where('user_id', $userId)
            ->where('order_id', 0)
            ->get();

        return $documents;
    }

    public static function getParamsDocument($hash) {

        $document = self::select('id', 'user_id', 'name', 'template', 'params')
            ->where('hash', $hash)
            ->first();

        return $document;
    }

    public static function createDocsForRegistration($userId, $orderId = null)
    {
        $user = Users::find($userId);
        $contacts = Contacts::getContacts($userId);
        foreach ($contacts as $key => $value) {
            if ($value->type == 'email') {
                $user->email = $value->value;
            }
        }

        $faktaddress = Addresses::find($user->faktaddress_id);
        $regaddress = Addresses::find($user->regaddress_id);

        $user->regaddress  = $regaddress->adressfull;
        $user->faktaddress = $faktaddress->adressfull;

        if(!empty($orderId))
            $order = Orders::find($orderId);
        else
            $order = Orders::getUnfinished($userId);

        $order->order_id = $order->id;
        $code_asp = AspCode::getAsp($userId);
        $user->code_asp = $code_asp;

        $types = [
            'SOGLASIE_NA_OBR_PERS_DANNIH',
            'SOGLASIE_RUKRED_RABOTODATEL',
            'SOGLASIE_RABOTODATEL',
            'SOGLASIE_NA_KRED_OTCHET',
            'ZAYAVLENIE_NA_PERECHISL_CHASTI_ZP',
        ];

        $settlement = OrganisationSettlement::getDefault();

        if ($settlement->id == 2) {
            $types[] = 'SOGLASIE_MINB';
        } else {
            $types[] = 'SOGLASIE_RDB';
        }

        foreach ($types as $key => $type) {
            self::createReg($user, $order, $type);
        }
    }

    public static function createDocsEndRegistrarion($userId, $orderId)
    {
        $user = Users::find($userId);
        $order = Orders::find($orderId);
        $contacts = Contacts::getContacts($userId);

        $payment_schedule = PaymentsSchedules::getSchedule($order->id);
        $order->payment_schedule = $payment_schedule->toArray();
        $order->order_id = $order->id;

        //ООО МКК "РУССКОЕ КРЕДИТНОЕ ОБЩЕСТВО"
        $company = Companies::find(2);
        $order->phys_address = $company->phys_address;

        foreach ($contacts as $key => $value) {
            if ($value->type == 'email') {
                $order->email = $value->value;
            }
        }

        $faktaddress = Addresses::find($user->faktaddress_id);
        $regaddress = Addresses::find($user->regaddress_id);

        $order->regaddress  = $regaddress->adressfull;
        $order->faktaddress = $faktaddress->adressfull;

        $order->passport_serial = $user->passport_serial;

        //todo: подписание отдельно
        $code_asp = AspCode::getAspEndReg($userId, $orderId);
        $order->code_asp = $code_asp;

        $types = [
            'INDIVIDUALNIE_USLOVIA_ONL',
            'GRAFIK_OBSL_MKR',
            'PERECHISLENIE_ZAEMN_SREDSTV',
            'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR',
            'OBSHIE_USLOVIYA'
        ];

        //Счет для выплаты займа
        //$settlement = OrganisationSettlement::getDefault();

        foreach ($types as $key => $type) {
            self::createDefault($user, $order, $type);
        }

    }

    public static function createDocsAfterRegistrarion($userId, $orderId)
    {
        $user = Users::find($userId);
        $order = Orders::find($orderId);
        $contacts = Contacts::getContacts($userId);

        $payment_schedule = PaymentsSchedules::getSchedule($order->id);
        $order->payment_schedule = $payment_schedule->toArray();
        $order->order_id = $order->id;

        //ООО МКК "РУССКОЕ КРЕДИТНОЕ ОБЩЕСТВО"
        $company = Companies::find(2);
        $order->phys_address = $company->phys_address;

        foreach ($contacts as $key => $value) {
            if ($value->type == 'email') {
                $order->email = $value->value;
            }
        }

        $faktaddress = Addresses::find($user->faktaddress_id);
        $regaddress = Addresses::find($user->regaddress_id);

        $order->regaddress  = $regaddress->adressfull;
        $order->faktaddress = $faktaddress->adressfull;

        $order->passport_serial = $user->passport_serial;

        $code_asp = AspCode::getAsp($userId);
        $order->code_asp = $code_asp;

        $types = [
            'SOGLASIE_NA_OBR_PERS_DANNIH',
            'SOGLASIE_RUKRED_RABOTODATEL',
            'SOGLASIE_RABOTODATEL',
            'SOGLASIE_NA_KRED_OTCHET',
            'INDIVIDUALNIE_USLOVIA_ONL',
            'GRAFIK_OBSL_MKR'
        ];

        foreach ($types as $key => $type) {
            self::createDefault($user, $order, $type);
        }

    }

    public static function createDefault($user, $order, $type)
    {
        $params = $order->toArray();
        $arrUser = $user->toArray();

        foreach ($arrUser as $key => $value) {
            if ($key == 'email') {
                continue;
            }
            $params[$key] = $value;
        }

        $data = [
            'contract_id'    => $order->contract_id,
            'name'           => self::$names[$type],
            'template'       => self::$templates[$type],
            'client_visible' => self::$client_visible[$type],
            'params'         => serialize((object)$params),
            'created'        => date('Y-m-d H:i:s'),
            'numeration'     => self::$numeration[$type],
            'hash'           => sha1(rand(11111, 99999))
        ];

        $needAsp = false;
        if ($order->code_asp) {
            $needAsp = true;
        }

        if ($needAsp) {
            $data['asp_id'] = $order->code_asp->id;
        }

        self::updateOrCreate(
            ['user_id' => $user->id, 'order_id' => $order->id, 'type' => $type],
            $data
        );
    }

    public static function createReg($user, $order, $type)
    {
        $params = $user->toArray();
        $arrOrder = $order->toArray();

        foreach ($arrOrder as $key => $value) {
            if ($key == 'email') {
                continue;
            }
            $params[$key] = $value;
        }

        $data = [
            'contract_id'    => 0,
            'name'           => self::$names[$type],
            'template'       => self::$templates[$type],
            'client_visible' => self::$client_visible[$type],
            'params'         => serialize((object)$params),
            'created'        => date('Y-m-d H:i:s'),
            'numeration'     => self::$numeration[$type],
            'hash'           => sha1(rand(11111, 99999))
        ];

        if ($user->code_asp) {
            $data['asp_id'] = $user->code_asp->id;
        }

        if ($type == 'SOGLASIE_NA_OBR_PERS_DANNIH_OBL') {
            $code_asp = AspCode::getFirstAsp($user->id, $order->id);
            if ($code_asp) {
                $data['asp_id'] = $code_asp->id;
            }
        }

        self::updateOrCreate(
            ['user_id' => $user->id, 'type' => $type, 'order_id' => $order->id],
            $data
        );
    }
}
