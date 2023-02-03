<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConsumeController;

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
    Route::put('/update/{id}', [ConsumeController::class, 'updateConsumeData']);
    Route::put('/update/{id}', [ConsumeController::class, 'updateConsumeFavorite']);
    Route::post('/create', [ConsumeController::class, 'createConsume']);
});
