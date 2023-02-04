<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/consume')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/favorite/{favorite}', [ConsumeController::class, 'getAllConsume']);
    Route::get('/total/byfrom', [ConsumeController::class, 'getTotalConsumeByFrom']);
    Route::get('/total/bytype', [ConsumeController::class, 'getTotalConsumeByType']);
    Route::delete('/delete/{id}', [ConsumeController::class, 'deleteConsumeById']);
    Route::put('/update/data/{id}', [ConsumeController::class, 'updateConsumeData']);
    Route::put('/update/favorite/{id}', [ConsumeController::class, 'updateConsumeFavorite']);
    Route::post('/create', [ConsumeController::class, 'createConsume']);
});

Route::prefix('/budget')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/over/{over}', [BudgetController::class, 'getAllBudget']);
    Route::delete('/delete/{id}', [BudgetController::class, 'deleteBudgetById']);
    Route::put('/update/data/{id}', [BudgetController::class, 'updateBudgetData']);
    Route::put('/update/status/{id}', [BudgetController::class, 'updateBudgetStatus']);
});

Route::prefix('/list')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [ConsumeListController::class, 'getAllList']);
    Route::delete('/delete/{id}', [ConsumeListController::class, 'deleteListById']);
    Route::put('/update/data/{id}', [ConsumeListController::class, 'updateListData']);
});

Route::prefix('/schedule')->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [ScheduleController::class, 'getAllSchedule']);
    Route::delete('/delete/{id}', [ScheduleController::class, 'deleteScheduleById']);
    Route::put('/update/data/{id}', [ScheduleController::class, 'updateScheduleData']);
});
