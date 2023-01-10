<?php

use Illuminate\Support\Facades\Route;
use App\Entity\Sms;
use App\Account\Profile;
use App\Info\Products;
use App\Entity\Cookies;
use App\Http\Middleware\TokenCheck;
use App\Account\General;
use App\Account\Photos;
use App\Http\Controllers\LoansOperationsController;
use App\Account\Loans;

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

Route::post('/sms/send', [Sms::class, 'send']);
Route::post('/sms/check', [Sms::class, 'check']);

Route::get('/info/products', [Products::class, 'get']);

Route::post('/cookies/check_token', [Cookies::class, 'checkToken']);
Route::post('/cookies/set_token', [Cookies::class, 'setToken']);
Route::post('/cookies/do_expire_tokens/{userId}', [Cookies::class, 'doExpireTokens']);


Route::middleware([TokenCheck::class])->group(function () {
    Route::post('/lk/profile', [Profile::class, 'get']);
    Route::post('/lk/photos', [Photos::class, 'get']);
    Route::post('/lk/general/get_stage', [General::class, 'getStage']);
    Route::post('/lk/general/get_user', [General::class, 'getUser']);
    Route::post('/lk/general/get_default_settlement', [General::class, 'getDefaultSettlement']);
    Route::post('/lk/loans', [Loans::class, 'get']);
    Route::post('/lk/loan/operations', [LoansOperationsController::class, 'get']);
});
