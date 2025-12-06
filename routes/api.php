<?php
use Illuminate\Support\Facades\Route;

Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('forget-password', [App\Http\Controllers\AuthController::class, 'forgetPassword']);
Route::post('reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [App\Http\Controllers\AuthController::class, 'logOut']);
    Route::post('profile', [App\Http\Controllers\AuthController::class, 'profileUpdate']);
    Route::post('change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);
});
Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'admin']], function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::apiResource('profile', App\Http\Controllers\Admin\ProfileController::class);
    Route::post('email-login', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'emailWiseLogin']);
    Route::get('fetch-all-user', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'fetchAllUser']);
    Route::post('create-user', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'createUser']);
    Route::post('update-user', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'updateUser']);
    Route::post('delete-user', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'deleteUser']);
    Route::get('fetch-all-owner', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'fetchAllOwner']);
    Route::post('create-owner', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'createOwner']);
    Route::post('update-owner', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'updateOwner']);
    Route::post('delete-owner', [App\Http\Controllers\Admin\UserAndOwnerController::class, 'deleteOwner']);
    Route::apiResource('facility', App\Http\Controllers\Admin\FacilityController::class);
    Route::apiResource('booking', App\Http\Controllers\Admin\BookingsController::class);
});
Route::group(['prefix' => 'owner', 'middleware' => ['auth:sanctum', 'owner']], function () {
    Route::get('/', [App\Http\Controllers\Owner\DashboardController::class, 'index']);
    Route::apiResource('property', App\Http\Controllers\Owner\PropertyController::class);
    Route::get('property-dropdown', [App\Http\Controllers\Owner\PropertyController::class, 'propertyDropdown']);
    Route::apiResource('resource-type', App\Http\Controllers\Owner\ResourceTypeController::class);
    Route::post('resource-type-multiple-store', [App\Http\Controllers\Owner\ResourceTypeController::class, 'multipleStore']);
    Route::post('resource-type-multiple-update', [App\Http\Controllers\Owner\ResourceTypeController::class, 'multipleUpdate']);
    Route::apiResource('resource', App\Http\Controllers\Owner\ResourceController::class);
    Route::post('resource-multiple-store', [App\Http\Controllers\Owner\ResourceController::class, 'multipleStore']);
    Route::post('resource-multiple-update', [App\Http\Controllers\Owner\ResourceController::class, 'multipleUpdate']);
    Route::post('property-image-store', [App\Http\Controllers\Owner\PropertyController::class, 'addMultipleImage']);
    Route::post('property-image-update', [App\Http\Controllers\Owner\PropertyController::class, 'updateImage']);
    Route::post('single-property-image-delete', [App\Http\Controllers\Owner\PropertyController::class, 'deleteImage']);
    Route::post('property-image-delete', [App\Http\Controllers\Owner\PropertyController::class, 'deleteMultipleImage']);
    Route::post('property-image-position-change', [App\Http\Controllers\Owner\PropertyController::class, 'changeImagePosition']);
    Route::get('property-wise-image', [App\Http\Controllers\Owner\PropertyController::class, 'propertyWiseImage']);
    Route::post('add-facility-property', [App\Http\Controllers\Owner\FacilityController::class, 'addFacilityProperty']);
    Route::get('facility', [App\Http\Controllers\Owner\FacilityController::class, 'getFacility']);
    Route::apiResource('bookings', App\Http\Controllers\Owner\BookingsController::class);

});
Route::group(['prefix' => 'user', 'middleware' => ['auth:sanctum', 'user']], function () {
    Route::apiResource('booking', App\Http\Controllers\User\BookingsController::class);
});
