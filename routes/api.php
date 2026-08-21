<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\FoodsController;
use App\Http\Controllers\Api\GoalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| پیشوند /api توسط bootstrap/app.php اعمال می‌شود (apiPrefix: 'api').
| این مسیرها دقیقاً معادل endpoint های نسخه NestJS هستند.
|--------------------------------------------------------------------------
*/

// احراز هویت عمومی
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// کتابخانه غذاها (عمومی)
Route::get('/foods', [FoodsController::class, 'index']);

// مسیرهای محافظت‌شده با توکن (Bearer)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/entries', [EntryController::class, 'index']);
    Route::post('/entries', [EntryController::class, 'store']);
    // باید قبل از '/{id}' ثبت شود تا با آن اشتباه گرفته نشود
    Route::delete('/entries', [EntryController::class, 'clear']);
    Route::delete('/entries/{id}', [EntryController::class, 'destroy']);

    Route::get('/goal', [GoalController::class, 'show']);
    Route::put('/goal', [GoalController::class, 'update']);
});
