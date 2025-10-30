<?php
use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'admin']], function () {
   Route::get('/',[App\Http\Controllers\Admin\DashboardController::class,'index']);
   Route::apiResource('profile', App\Http\Controllers\Admin\ProfileController::class);
});
Route::group(['prefix' => 'owner', 'middleware' => ['auth:sanctum', 'owner']], function () {
   Route::get('/',[App\Http\Controllers\Owner\DashboardController::class,'index']);
   Route::apiResource('property', App\Http\Controllers\Owner\PropertyController::class);
});