<?php
use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/forget-password', [App\Http\Controllers\AuthController::class, 'forgetPassword']);
Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile', [App\Http\Controllers\AuthController::class, 'profileUpdate']);
    Route::post('logout', [App\Http\Controllers\AuthController::class, 'logOut']);
    Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);
});
Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'admin']], function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::apiResource('profile', App\Http\Controllers\Admin\ProfileController::class);
});
Route::group(['prefix' => 'owner', 'middleware' => ['auth:sanctum', 'owner']], function () {
    Route::get('/', [App\Http\Controllers\Owner\DashboardController::class, 'index']);
    Route::apiResource('property', App\Http\Controllers\Owner\PropertyController::class);
    Route::apiResource('resource-type', App\Http\Controllers\Owner\ResourceTypeController::class);
    Route::post('resource-type-multiple-store', [App\Http\Controllers\Owner\ResourceTypeController::class, 'multipleStore']);
    Route::post('resource-type-multiple-update', [App\Http\Controllers\Owner\ResourceTypeController::class, 'multipleUpdate']);
    Route::get('/property-dropdown', [App\Http\Controllers\Owner\PropertyController::class, 'propertyDropdown']);
});
