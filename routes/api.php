<?php

use Illuminate\Support\Facades\Route;

Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('forget-password', [App\Http\Controllers\AuthController::class, 'forgetPassword']);
Route::post('reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);
Route::get('property-detail', [App\Http\Controllers\User\BookingsController::class, 'propertyDetails']);
Route::get('migrations/run', [App\Http\Controllers\MigrationController::class, 'runMigrations']);
Route::get('migrations/status', [App\Http\Controllers\MigrationController::class, 'checkMigrationStatus']);
Route::get('available-resources-types', [App\Http\Controllers\User\BookingsController::class, 'getAvailableResourcesTypes']);
Route::get('products', [App\Http\Controllers\StripeController::class, 'getProducts']);
Route::get('stripe/config', [App\Http\Controllers\StripeController::class, 'getStripeConfig']);
Route::get('icalendar/{resourceId}/feed.ics', [App\Http\Controllers\IcalController::class, 'index']);
Route::get('cancel-pending-bookings', [App\Http\Controllers\Admin\BookingsController::class, 'bookingCancel']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('stripe/subscription', [App\Http\Controllers\StripeController::class, 'createSubscription']);
    Route::get('stripe/subscription/status', [App\Http\Controllers\StripeController::class, 'checkSubscriptionStatus']);
    Route::post('stripe/subscription/retry', [App\Http\Controllers\StripeController::class, 'retrySubscriptionPayment']);
    Route::post('stripe/subscription/sync', [App\Http\Controllers\StripeController::class, 'syncSubscriptionStatus']);
    Route::post('stripe/payment/complete', [App\Http\Controllers\StripeController::class, 'completePayment']);

    // Combined subscription management endpoint
    Route::get('stripe/subscription-data', [App\Http\Controllers\StripeController::class, 'getSubscriptionData']);
    Route::post('stripe/subscription/cancel', [App\Http\Controllers\StripeController::class, 'cancelSubscription']);
    Route::post('stripe/subscription/reactivate', [App\Http\Controllers\StripeController::class, 'reactivateSubscription']);
});

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
    Route::apiResource('owner', App\Http\Controllers\Admin\OwnerController::class);
    Route::apiResource('property', App\Http\Controllers\Admin\PropertyController::class);

    // Admin subscription management routes
    Route::get('subscriptions', [App\Http\Controllers\StripeController::class, 'getAdminSubscriptions']);
    Route::post('subscriptions/{id}/cancel', [App\Http\Controllers\StripeController::class, 'cancelAdminSubscription']);
});
Route::group(['prefix' => 'owner', 'middleware' => ['auth:sanctum', 'owner']], function () {
    Route::get('/', [App\Http\Controllers\Owner\DashboardController::class, 'index']);
    Route::get('owner-details', [App\Http\Controllers\Owner\DashboardController::class, 'ownerDetails']);
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
    Route::get('resource-wise-list', [App\Http\Controllers\Owner\ResourceController::class, 'resourceWiseList']);
    Route::post('booking-cancel', [App\Http\Controllers\Owner\BookingsController::class, 'bookingCancel']);
});
Route::group(['prefix' => 'user', 'middleware' => ['auth:sanctum', 'user']], function () {
    Route::apiResource('booking', App\Http\Controllers\User\BookingsController::class);
    Route::get('bookings', [App\Http\Controllers\User\BookingsController::class, 'booking']);
    Route::post('booking-cancel', [App\Http\Controllers\User\BookingsController::class, 'bookingCancel']);
    Route::post('booking-cancel-delete', [App\Http\Controllers\User\BookingsController::class, 'bookingCancelDelete']);
    Route::post('booking-delete', [App\Http\Controllers\User\BookingsController::class, 'bookingDelete']);
    Route::post('booking-status-update', [App\Http\Controllers\User\BookingsController::class, 'bookingStatusUpdate']);
    Route::get('property-details', [App\Http\Controllers\User\BookingsController::class, 'propertyDetails']);
    Route::get('available-resources-types', [App\Http\Controllers\User\BookingsController::class, 'getAvailableResourcesTypes']);
    Route::post('create-payment-intent', [App\Http\Controllers\User\BookingsController::class, 'createPaymentIntent']);
    Route::post('complete-payment', [App\Http\Controllers\User\BookingsController::class, 'completePayment']);
});
