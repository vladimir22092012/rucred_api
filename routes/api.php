<?php

use Illuminate\Support\Facades\Route;
use App\Components\Sms;
use App\Account\Profile;
use App\Info\Products;

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
Route::post('/lk/profile', [Profile::class, 'get']);
Route::get('/info/products', [Products::class, 'get']);
