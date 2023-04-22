<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\AuthApi\Queries as QueryAuthApi;
use App\Http\Controllers\ConsumeApi\Commands as CommandConsumeApi;
use App\Http\Controllers\ConsumeApi\Queries as QueryConsumeApi;
use App\Http\Controllers\ConsumeApi\CommandsList as CommandConsumeListApi;
use App\Http\Controllers\ConsumeApi\QueriesList as QueryConsumeListApi;
use App\Http\Controllers\PaymentApi\Commands as CommandPaymentApi;
use App\Http\Controllers\PaymentApi\Queries as QueryPaymentApi;
use App\Http\Controllers\ScheduleApi\Commands as CommandScheduleApi;
use App\Http\Controllers\ScheduleApi\Queries as QueryScheduleApi;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ConsumeListController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/v1/login', [CommandAuthApi::class, 'login']);
Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/consume')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/favorite/{favorite}/type/{type}', [QueryConsumeApi::class, 'getAllConsume']);
    Route::get('/total/byfrom', [QueryConsumeApi::class, 'getTotalConsumeByFrom']);
    Route::get('/total/bytype', [QueryConsumeApi::class, 'getTotalConsumeByType']);
    Route::get('/total/bymain', [QueryConsumeApi::class, 'getTotalConsumeByMainIng']);
    Route::get('/total/day/cal/month/{month}/year/{year}', [QueryConsumeApi::class, 'getDailyConsumeCal']);
    Route::delete('/delete/{id}', [CommandConsumeApi::class, 'deleteConsumeById']);
    Route::put('/update/data/{id}', [CommandConsumeApi::class, 'updateConsumeData']);
    Route::put('/update/favorite/{id}', [CommandConsumeApi::class, 'updateConsumeFavorite']);
    Route::post('/create', [CommandConsumeApi::class, 'createConsume']);
});

Route::prefix('/v1/payment')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/total/month/{year}', [QueryPaymentApi::class, 'getTotalSpendMonth']);
    Route::get('/total/month/{month}/year/{year}', [QueryPaymentApi::class, 'getTotalSpendDay']);
});

Route::prefix('/v1/analytic')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/payment/month/{month}/year/{year}', [QueryPaymentApi::class, 'getAnalyticSpendMonth']);
});

Route::prefix('/v1/budget')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/over/{over}', [BudgetController::class, 'getAllBudget']);
    Route::delete('/delete/{id}', [BudgetController::class, 'deleteBudgetById']);
    Route::put('/update/data/{id}', [BudgetController::class, 'updateBudgetData']);
    Route::put('/update/status/{id}', [BudgetController::class, 'updateBudgetStatus']);
    Route::post('/create', [BudgetController::class, 'createBudget']);
});

Route::prefix('/v1/list')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [QueryConsumeListApi::class, 'getAllList']);
    Route::delete('/delete/{id}', [CommandConsumeListApi::class, 'deleteListById']);
    Route::put('/update/data/{id}', [CommandConsumeListApi::class, 'updateListData']);
    Route::post('/create', [CommandConsumeListApi::class, 'createList']);
});

Route::prefix('/v1/schedule')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [QueryScheduleApi::class, 'getAllSchedule']);
    Route::get('/day/{day}', [QueryScheduleApi::class, 'getTodaySchedule']);
    Route::delete('/delete/{id}', [CommandScheduleApi::class, 'deleteScheduleById']);
    Route::put('/update/data/{id}', [CommandScheduleApi::class, 'updateScheduleData']);
    Route::post('/create', [CommandScheduleApi::class, 'createSchedule']);
});
