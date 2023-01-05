<?php

use Illuminate\Support\Facades\Route;
use App\Components\Sms;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => ['cors'],
    'namespace' => $this->namespace,
    'prefix' => 'api',
], function ($router) {
    Route::post('/sms/send', [Sms::class, 'send']);
    Route::post('/sms/check', [Sms::class, 'check']);
});


