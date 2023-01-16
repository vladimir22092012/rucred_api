<?php

use Illuminate\Support\Facades\Route;
use App\Entity\Sms;
use App\Account\Profile;
use App\Info\Products;
use App\Http\Middleware\TokenCheck;
use App\Account\General;
use App\Account\Photos;
use App\Http\Controllers\AccountControllers\LoansOperationsController;
use App\Account\Loans;
use App\Http\Middleware\OrderOwner;
use App\Http\Controllers\AccountControllers\ActiveLoansController;
use App\Http\Controllers\AccountControllers\RequisitesController;
use App\Http\Controllers\AccountControllers\DocumentsController;
use App\Http\Controllers\StepsControllers\MainController;
use App\Http\Controllers\StepsControllers\PassportController;
use App\Http\Controllers\StepsControllers\ContactController;
use App\Http\Controllers\StepsControllers\MailController;
use App\Http\Controllers\StepsControllers\EmployerController;
use App\Info\Employers;
use App\Http\Controllers\StepsControllers\RequisitesController as RegReq;

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
Route::get('/info/employers/get', [Employers::class, 'get']);
Route::get('/info/employers/change', [Employers::class, 'change']);

Route::middleware([TokenCheck::class])->group(function () {
    Route::post('/lk/profile', [Profile::class, 'get']);
    Route::post('/lk/photos', [Photos::class, 'get']);

    Route::post('/lk/general/get_stage', [General::class, 'getStage']);
    Route::post('/lk/general/get_user', [General::class, 'getUser']);
    Route::post('/lk/general/get_default_settlement', [General::class, 'getDefaultSettlement']);

    Route::post('/lk/loans', [Loans::class, 'get']);
    Route::post('/lk/loans/active', [ActiveLoansController::class, 'get']);
    Route::middleware([OrderOwner::class])->post('/lk/loan/operations', [LoansOperationsController::class, 'get']);
    Route::post('/lk/loans/active', [ActiveLoansController::class, 'get']);

    Route::post('/lk/requisites/get', [RequisitesController::class, 'get']);
    Route::post('/lk/requisites/card/add', [RequisitesController::class, 'addCard']);
    Route::post('/lk/requisites/account/add', [RequisitesController::class, 'addAccount']);
    Route::post('/lk/requisites/change', [RequisitesController::class, 'changeRequisites']);

    Route::post('/lk/documents', [DocumentsController::class, 'get']);

    Route::post('/mail/send', [MailController::class, 'send']);
    Route::post('/mail/check', [MailController::class, 'check']);

    Route::post('/step/main', [MainController::class, 'action']);
    Route::post('/step/passport', [PassportController::class, 'action']);
    Route::post('/step/contacts', [ContactController::class, 'action']);
    Route::post('/step/work', [EmployerController::class, 'action']);
    Route::post('/step/requisites/bank/add', [RegReq::class, 'addAccount']);
    Route::post('/step/requisites/card/add', [RegReq::class, 'addCard']);

});
