<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\AuthApi\Queries as QueryAuthApi;
use App\Http\Controllers\ConsumeApi\Commands as CommandConsumeApi;
use App\Http\Controllers\ConsumeApi\Queries as QueryConsumeApi;
use App\Http\Controllers\ConsumeApi\CommandsList as CommandConsumeListApi;
use App\Http\Controllers\ConsumeApi\QueriesList as QueryConsumeListApi;
use App\Http\Controllers\ConsumeApi\QueriesAllergic as QueryAllergicApi;
use App\Http\Controllers\ConsumeApi\CommandsAllergic as CommandAllergicApi;
use App\Http\Controllers\PaymentApi\Commands as CommandPaymentApi;
use App\Http\Controllers\PaymentApi\Queries as QueryPaymentApi;
use App\Http\Controllers\ScheduleApi\Commands as CommandScheduleApi;
use App\Http\Controllers\ScheduleApi\Queries as QueryScheduleApi;
use App\Http\Controllers\BudgetApi\Queries as QueryBudgetApi;
use App\Http\Controllers\BudgetApi\Commands as CommandsBudgetApi;
use App\Http\Controllers\CountApi\QueriesCalorie as QueryCountApi;
use App\Http\Controllers\CountApi\CommandsCalorie as CommandsCountCalorie;
use App\Http\Controllers\UserApi\Queries as QueryUserApi;
use App\Http\Controllers\UserApi\QueriesBodyInfo as QueryBodyInfoApi;
use App\Http\Controllers\UserApi\CommandsBodyInfo as CommandBodyInfoApi;
use App\Http\Controllers\UserApi\Commands as CommandUserApi;
use App\Http\Controllers\TagApi\Queries as QueryTagApi;
use App\Http\Controllers\TagApi\Commands as CommandTagApi;
use App\Http\Controllers\ReminderApi\Queries as QueryReminderApi;
use App\Http\Controllers\ReminderApi\Commands as CommandReminderApi;

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ConsumeListController;

Route::post('/v1/login', [CommandAuthApi::class, 'login']);
Route::post('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/consume')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/limit/{page_limit}/order/{order}/favorite/{favorite}/type/{type}/provide/{provide}/calorie/{calorie}', [QueryConsumeApi::class, 'getAllConsume']);
    Route::get('/detail/{slug}', [QueryConsumeApi::class, 'getConsumeDetailBySlug']);
    Route::post('/by/context/{ctx}/{target}', [QueryConsumeApi::class, 'getConsumeByContext']);
    Route::get('/total/byfrom', [QueryConsumeApi::class, 'getTotalConsumeByFrom']);
    Route::get('/total/bytype', [QueryConsumeApi::class, 'getTotalConsumeByType']);
    Route::get('/total/bymain', [QueryConsumeApi::class, 'getTotalConsumeByMainIng']);
    Route::get('/total/byprovide', [QueryConsumeApi::class, 'getTotalConsumeByProvide']);
    Route::get('/total/day/cal/month/{month}/year/{year}', [QueryConsumeApi::class, 'getDailyConsumeCal']);
    Route::get('/calorie/maxmin', [QueryConsumeApi::class, 'getMaxMinCalorie']);
    Route::get('/calorie/bytype/{view}', [QueryConsumeApi::class, 'getCalorieTotalByConsumeType']);
    Route::get('/list/select', [QueryConsumeApi::class, 'getListConsume']);
    
    Route::delete('/delete/{id}', [CommandConsumeApi::class, 'deleteConsumeById']);
    Route::put('/update/data/{id}', [CommandConsumeApi::class, 'updateConsumeData']);
    Route::put('/update/favorite/{id}', [CommandConsumeApi::class, 'updateConsumeFavorite']);
    Route::post('/create', [CommandConsumeApi::class, 'createConsume']);
});

Route::prefix('/v1/payment')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/total/month/{year}', [QueryPaymentApi::class, 'getTotalSpendMonth']);
    Route::get('/total/month/{month}/year/{year}', [QueryPaymentApi::class, 'getTotalSpendDay']);
    Route::get('/detail/month/{month}/year/{year}', [QueryPaymentApi::class, 'getMonthlySpend']);
});

Route::prefix('/v1/analytic')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/payment/month/{month}/year/{year}', [QueryPaymentApi::class, 'getAnalyticSpendMonth']);
    Route::get('/allergic', [QueryAllergicApi::class, 'getAllAllergic']);
    Route::put('/allergic/{id}', [CommandAllergicApi::class, 'updateAllergic']);
});

Route::prefix('/v1/tag')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueryTagApi::class, 'getAllTag']);
    Route::get('/my', [QueryTagApi::class, 'getMyTag']);
    Route::post('/add', [CommandTagApi::class, 'createMyTag']);
    Route::delete('/{id}', [CommandTagApi::class, 'deleteTagById']);
});

Route::prefix('/v1/reminder')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueryReminderApi::class, 'getListReminder']);
    Route::post('/rel', [CommandReminderApi::class, 'createReminderRel']);
    Route::post('/add', [CommandReminderApi::class, 'createReminder']);
    Route::delete('/rel/{id}', [CommandReminderApi::class, 'deleteReminderRel']);
    Route::delete('/delete/{id}', [CommandReminderApi::class, 'deleteReminder']);
});

Route::prefix('/v1/count')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/calorie', [QueryCountApi::class, 'getLastCountCalorie']);
    Route::post('/calorie', [CommandsCountCalorie::class, 'createCountCalorie']);
    Route::get('/calorie/fulfill/{date}', [QueryCountApi::class, 'getFulfillCalorie']);
    Route::get('/payment', [QueryPaymentApi::class, 'getLifetimeSpend']);

    Route::delete('/calorie/{id}', [CommandsCountCalorie::class, 'deleteCountCalorie']);
});

Route::prefix('/v1/budget')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/dashboard', [QueryBudgetApi::class, 'getBudgetDashboard']);
    Route::get('/by/{year}', [QueryBudgetApi::class, 'getAllBudgetByYear']);
    Route::post('/create', [CommandsBudgetApi::class, 'createBudget']);
});

Route::prefix('/v1/list')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/limit/{page_limit}/order/{order}', [QueryConsumeListApi::class, 'getAllList']);
    Route::delete('/delete/{id}', [CommandConsumeListApi::class, 'deleteListById']);
    Route::put('/update/data/{id}', [CommandConsumeListApi::class, 'updateListData']);
    Route::post('/create', [CommandConsumeListApi::class, 'createList']);
});

Route::prefix('/v1/schedule')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueryScheduleApi::class, 'getMySchedule']);
    Route::get('/day/{day}', [QueryScheduleApi::class, 'getTodaySchedule']);
    Route::delete('/delete/{id}', [CommandScheduleApi::class, 'deleteScheduleById']);
    Route::put('/update/data/{id}', [CommandScheduleApi::class, 'updateScheduleData']);
    Route::post('/create', [CommandScheduleApi::class, 'createSchedule']);
});

Route::prefix('/v1/user')->group(function () {
    Route::get('/', [QueryUserApi::class, 'getMyProfile'])->middleware(['auth:sanctum']);
    Route::get('/body_info', [QueryBodyInfoApi::class, 'getMyLatestBodyInfo'])->middleware(['auth:sanctum']);
    Route::get('/my_body_history', [QueryBodyInfoApi::class, 'getMyBodyHistory'])->middleware(['auth:sanctum']);

    Route::put('/edit', [CommandUserApi::class, 'updateUser'])->middleware(['auth:sanctum']);
    Route::put('/edit_telegram_id', [CommandUserApi::class, 'updateTelegramId'])->middleware(['auth:sanctum']);
    Route::put('/edit_telegram_id_qrcode', [CommandUserApi::class, 'updateTelegramIdQRCode']);
    Route::put('/edit_timezone', [CommandUserApi::class, 'updateTimezone'])->middleware(['auth:sanctum']);
    Route::put('/image', [CommandUserApi::class, 'updateImage'])->middleware(['auth:sanctum']);
    
    Route::post('/create', [CommandUserApi::class, 'createUser']);
    Route::post('/body_info/create', [CommandBodyInfoApi::class, 'createBodyInfo'])->middleware(['auth:sanctum']);;

    Route::delete('/body_info/delete/{id}', [CommandBodyInfoApi::class, 'deleteBodyInfo'])->middleware(['auth:sanctum']);;
});
