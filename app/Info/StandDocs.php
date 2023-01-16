<?php

namespace App\Info;

use App\Models\OrganisationSettlement;
use Illuminate\Http\Request;

class StandDocs extends Info
{

    static function get(Request $request)
    {
        $res = [
            [
                'name' => 'Согласие на обработку персональных данных',
                'link' => $_ENV['URL_API'] . 'pdf/04.05._Согласие_на_обработку_персональных_данных.pdf'
            ],
            [
                'name' => 'Согласие на обработку персональных данных РуКредом и распространение работодателю',
                'link' => $_ENV['URL_API'] . 'pdf/04.06._Согласие_на_обработку_персональных_данных_РуКредом_и_распространение_работодателю_.pdf'
            ],
            [
                'name' => 'Согласие работодателю на распространение персональных данных',
                'link' => $_ENV['URL_API'] . 'pdf/03_03_Согласие_работодателю_на_распространение_персональных_данных.pdf'
            ],
            [
                'name' => 'Согласие заемщика на получение кредитного отчета',
                'link' => $_ENV['URL_API'] . 'pdf/04.07._Согласие_заемщика_на_получение_кредитного_отчета.pdf'
            ],
            [
                'name' => 'Обязательство о подаче заявления в адрес работодателя на перечисление части зп',
                'link' => $_ENV['URL_API'] . 'pdf/04.09._Обязательство_о_подаче_заявления_в_адрес_работодателя_на_перечисление_части_зп.pdf'
            ]
        ];

        //Счет для выплаты займа
        $settlement = OrganisationSettlement::getDefault();

        if ($settlement->id == 2) {
            $res[] =
                [
                    'name' => 'Согласие на обработку персональных данных и упрощенную идентификацию через МИнБ',
                    'link' => $_ENV['URL_API'] . 'pdf/04.05.1_Согласие_на_обработку_персональных_данных_и_УПРИД_(МИнБ).pdf'
                ];
        } else {
            $res[] =
                [
                    'name' => 'Согласие на обработку персональных данных и упрощенную идентификацию через РДБ',
                    'link' => $_ENV['URL_API'] . 'pdf/04.05.2_Согласие_на_обработку_персональных_данных_и_УПРИД_(РДБ).pdf'
                ];
        }

        return response($res, 200);
    }
}