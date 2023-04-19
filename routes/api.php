<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\AuthApi\Queries as QueryAuthApi;
use App\Http\Controllers\ConsumeController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ConsumeListController;
use App\Http\Controllers\ScheduleController;

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

Route::prefix('/v1/consume')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/favorite/{favorite}/type/{type}', [ConsumeController::class, 'getAllConsume']);
    Route::get('/total/byfrom', [ConsumeController::class, 'getTotalConsumeByFrom']);
    Route::get('/total/bytype', [ConsumeController::class, 'getTotalConsumeByType']);
    Route::delete('/delete/{id}', [ConsumeController::class, 'deleteConsumeById']);
    Route::put('/update/data/{id}', [ConsumeController::class, 'updateConsumeData']);
    Route::put('/update/favorite/{id}', [ConsumeController::class, 'updateConsumeFavorite']);
    Route::post('/create', [ConsumeController::class, 'createConsume']);
});

Route::prefix('/v1/budget')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/over/{over}', [BudgetController::class, 'getAllBudget']);
    Route::delete('/delete/{id}', [BudgetController::class, 'deleteBudgetById']);
    Route::put('/update/data/{id}', [BudgetController::class, 'updateBudgetData']);
    Route::put('/update/status/{id}', [BudgetController::class, 'updateBudgetStatus']);
    Route::post('/create', [BudgetController::class, 'createBudget']);
});

Route::prefix('/v1/list')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [ConsumeListController::class, 'getAllList']);
    Route::delete('/delete/{id}', [ConsumeListController::class, 'deleteListById']);
    Route::put('/update/data/{id}', [ConsumeListController::class, 'updateListData']);
    Route::post('/create', [ConsumeListController::class, 'createList']);
});

Route::prefix('/v1/schedule')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [ScheduleController::class, 'getAllSchedule']);
    Route::delete('/delete/{id}', [ScheduleController::class, 'deleteScheduleById']);
    Route::put('/update/data/{id}', [ScheduleController::class, 'updateScheduleData']);
    Route::post('/create', [ScheduleController::class, 'createSchedule']);
});
