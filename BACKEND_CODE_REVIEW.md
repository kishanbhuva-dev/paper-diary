# Backend Code Review - Paper Diary

## Executive Summary

This document provides a comprehensive code review of the Paper Diary Laravel backend application. The codebase is a property booking management system with multi-role authentication (Admin, Owner, User), Stripe subscription integration, and booking management features.

---

## Table of Contents

1. [Critical Security Issues](#1-critical-security-issues)
2. [Architecture & Design Issues](#2-architecture--design-issues)
3. [Code Quality Issues](#3-code-quality-issues)
4. [Database & Migration Issues](#4-database--migration-issues)
5. [API & Route Issues](#5-api--route-issues)
6. [Model Issues](#6-model-issues)
7. [Controller Issues](#7-controller-issues)
8. [Middleware Issues](#8-middleware-issues)
9. [Helper Function Issues](#9-helper-function-issues)
10. [Performance Issues](#10-performance-issues)
11. [Best Practice Violations](#11-best-practice-violations)
12. [Recommendations](#12-recommendations)

---

## 1. Critical Security Issues

### 1.1 Migration Controller Exposed Without Authentication
**File:** `app/Http/Controllers/MigrationController.php`
**Lines:** 9-49
**Severity:** 🔴 CRITICAL

```php
// routes/api.php - Lines 9-10
Route::get('migrations/run', [MigrationController::class, 'runMigrations']);
Route::get('migrations/status', [MigrationController::class, 'checkMigrationStatus']);
```

**Issue:** The migration routes are publicly accessible without any authentication. Anyone can run database migrations or view migration status.

**Impact:** Complete database compromise, data loss, or application downtime.

**Recommendation:** 
- Remove these routes entirely from production
- If needed for deployment, protect behind IP whitelist or admin authentication
- Use deployment scripts instead

---

### 1.2 Stripe Secret Keys Stored and Exposed
**File:** `app/Http/Controllers/AuthController.php`
**Lines:** 183-186

```php
if ($user->role=='owner') {
    $user->stripePublicKey = $request->stripePublicKey;
    $user->stripeSecretKey = $request->stripeSecretKey;
}
```

**Issues:**
1. Stripe secret keys should NEVER be stored in plain text in the database
2. Keys are potentially exposed in API responses
3. `User` model `$fillable` doesn't include these fields but they're being set directly

**File:** `app/Http/Controllers/User/BookingsController.php`
**Lines:** 401-412
```php
$ownerStripeSecret = $property->stripeSecretKey;
// or
$ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
```

**Recommendation:**
- Encrypt sensitive keys using Laravel's `encrypt()`/`decrypt()` functions
- Never expose secret keys in API responses
- Use `$hidden` array in User and Property models

---

### 1.3 Sensitive Data Exposed in Error Messages
**File:** Multiple Controllers
**Pattern:**
```php
return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => null]);
```

**Issue:** Exception messages are returned directly to clients, potentially exposing:
- Database schema details
- File paths
- Internal system information
- Stack traces

**Recommendation:** Log detailed errors internally, return generic messages to users.

---

### 1.4 env() Usage in Controller
**File:** `app/Http/Controllers/AuthController.php`
**Line:** 227

```php
'url' => env('FRONTEND_URL'),
```

**Issue:** `env()` should only be used in config files. It returns `null` when config is cached.

**Recommendation:** Use `config('app.frontend_url')` after defining in config.

---

### 1.5 Stripe Config Exposes Keys
**File:** `app/Http/Controllers/StripeController.php`
**Lines:** 39-47

```php
public function getStripeConfig()
{
    return response()->json([
        'status' => true,
        'data' => [
            'publishableKey' => env('STRIPE_KEY')
        ]
    ]);
}
```

**Issue:** Uses `env()` directly and exposes configuration via public endpoint.

---

## 2. Architecture & Design Issues

### 2.1 Missing Form Request Validation Classes
**Location:** All Controllers
**Issue:** Validation logic is embedded in controllers instead of using Laravel's Form Request classes.

**Current Pattern:**
```php
$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required',
]);
if ($validator->fails()) {
    return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
}
```

**Recommendation:** Create dedicated Form Request classes:
```php
// app/Http/Requests/LoginRequest.php
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }
}
```

---

### 2.2 No Service Layer Pattern
**Issue:** Business logic is embedded directly in controllers, violating Single Responsibility Principle.

**Example:** `StripeController.php` contains ~900 lines of mixed Stripe API calls, database operations, and response formatting.

**Recommendation:** Create service classes:
- `App\Services\StripeSubscriptionService`
- `App\Services\BookingService`
- `App\Services\PropertyService`

---

### 2.3 Missing Repository Pattern
**Issue:** Direct Eloquent queries in controllers make testing and maintenance difficult.

**Recommendation:** Implement repository pattern for complex queries.

---

### 2.4 No API Resource/Transformer Classes
**Issue:** Response formatting is inconsistent and scattered across controllers.

**Recommendation:** Use Laravel API Resources for consistent response transformation.

---

### 2.5 Global Helper Functions
**File:** `app/helper/helper.php`

**Issues:**
1. Functions like `hasActiveSubscription()` duplicate logic from `User` model
2. No namespace, polluting global scope
3. Mixed concerns (booking logic, subscription logic, property lookups)
4. Function `getSubscriptionDetails()` is 110 lines - too long

**Recommendation:** 
- Move to proper service classes
- Or create dedicated helper classes with namespaces

---

## 3. Code Quality Issues

### 3.1 Inconsistent Naming Conventions

**Database Columns (camelCase - Non-standard):**
- `firstName`, `lastName` - should be `first_name`, `last_name`
- `propertyId`, `ownerId` - should be `property_id`, `owner_id`
- `resourceTypeId` - should be `resource_type_id`
- `arrivalDateTime` - should be `arrival_date_time`

**Model Files:**
- `Bookings.php` - plural, should be `Booking.php` (singular)
- Inconsistent with `BookingOrder.php` which is correct

**Variables:**
- Mixed camelCase and snake_case throughout codebase

---

### 3.2 Undefined Variables
**File:** `app/Http/Controllers/User/BookingsController.php`
**Line:** 119

```php
$paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
```

**Issue:** `$stripe` is used but never defined in `bookingStatusUpdate()` method.

**Line:** 134
```php
$stripe = new \Stripe\StripeClient($ownerStripeSecret);
```

**Issue:** `$ownerStripeSecret` is used before it's defined.

---

### 3.3 Dead/Unreachable Code
**File:** `app/Http/Controllers/User/BookingsController.php`
**Lines:** 285-300

```php
$paymentIntent = PaymentIntent::create([...]);
$response = [
    'status'       => true,
    'token'        => (string) $paymentIntent->id,
    'clientSecret' => $paymentIntent->client_secret,
    'publishable'  => base64_encode($ownerStripePublicKey),
    "total"        => $booking->price,
];
return response()->json(['status' => true, 'message' => '', 'data' => ['id' => $bookingOrderId]], 201);
```

**Issue:** `$response` variable is built but never returned. The actual return ignores payment intent data.

---

### 3.4 Commented-Out Code
**Files:** Multiple files contain large blocks of commented code:
- `app/helper/helper.php` - Lines 8-52
- `app/Http/Controllers/Owner/ResourceTypeController.php` - Lines 70-105, 229-282
- `app/Http/Controllers/Admin/BookingsController.php` - Lines 114-157 (empty methods)

**Recommendation:** Remove commented code, use version control for history.

---

### 3.5 Magic Numbers and Strings
**File:** `app/Http/Controllers/StripeController.php`
**Lines:** Multiple

```php
$endsAt = $createdAt + (90 * 24 * 60 * 60); // What is 90?
'Professional Plan' // Hardcoded multiple times
'12.00' // Hardcoded default amount
```

**Recommendation:** Use constants or config values.

---

### 3.6 Inconsistent Response Structure
**Pattern 1:**
```php
return response()->json(['status' => true, 'message' => '', 'data' => $data]);
```

**Pattern 2:**
```php
return response()->json(['success' => true, 'message' => 'Done', 'output' => $output]);
```

**Pattern 3:**
```php
return $response = ['status' => false, 'message' => 'Error'];  // Missing response()->json()
```

---

### 3.7 Duplicate Code

**Admin Owner/User Operations:**
`app/Http/Controllers/Admin/UserAndOwnerController.php`

The following methods are nearly identical with only the role parameter differing:
- `createUser()` / `createOwner()`
- `updateUser()` / `updateOwner()`
- `deleteUser()` / `deleteOwner()`
- `fetchAllUser()` / `fetchAllOwner()`

**Recommendation:** The private methods `store()`, `update()`, `delete()`, `fetchAll()` already exist - use them directly with parameters.

---

### 3.8 Missing Return Statements
**File:** `app/Http/Controllers/Admin/UserAndOwnerController.php`
**Lines:** 302, 329

```php
private function update($request, $role)
{
    // ... code ...
    return response()->json($response);
}
return response()->json($response); // Unreachable
```

---

## 4. Database & Migration Issues

### 4.1 Non-Standard Column Naming
**File:** `database/migrations/0001_01_01_000000_create_users_table.php`

```php
$table->string('firstName');      // Should be: first_name
$table->string('lastName');       // Should be: last_name
$table->longText('address');      // OK
```

**File:** `database/migrations/2025_10_16_055231_create_property_table.php`

```php
$table->foreignId('ownerId')->constrained('users');  // Should be: owner_id
$table->string('propertyName');                       // Should be: property_name
```

---

### 4.2 Inconsistent Table Naming
- `property` (singular) - should be `properties` (plural)
- `property_image` (singular) - should be `property_images`
- `payment` (singular) - should be `payments`
- `booking_orders` (plural) ✓
- `bookings` (plural) ✓

---

### 4.3 Missing Indexes
**File:** `database/migrations/2025_11_14_054731_create_booking_orders_table.php`

```php
$table->dateTime('arrivalDateTime');
$table->dateTime('departureDateTime');
```

**Issue:** No indexes on date columns frequently used in WHERE clauses for availability checks.

**Recommendation:**
```php
$table->index(['arrivalDateTime', 'departureDateTime']);
$table->index('status');
```

---

### 4.4 Missing Foreign Key Indexes
Many foreign keys exist without corresponding indexes, affecting JOIN performance.

---

### 4.5 Enum Values Inconsistency
**File:** `booking_orders` migration

```php
$table->enum('status', ['pending', 'confirm', 'cancelled']);
$table->enum('paymentStatus', ['paid', 'unpaid', 'failed', 'cancelled', 'confirm']);
```

**Issues:**
- `confirm` should be `confirmed` (grammatically consistent)
- `confirm` appears in both status and paymentStatus - confusing
- Consider using separate status table for flexibility

---

### 4.6 Use of LongText for Simple Fields
```php
$table->longText('address');  // varchar(500) would suffice
$table->longText('address2');
```

---

## 5. API & Route Issues

### 5.1 Inconsistent Route Naming
**File:** `routes/api.php`

```php
Route::get('property-detail', ...);        // kebab-case
Route::get('available-resources-types', ...); // kebab-case with typo?
Route::get('stripe/config', ...);          // nested

// Owner routes
Route::get('property-dropdown', ...);
Route::get('property-wise-image', ...);    // Non-standard naming
```

---

### 5.2 Non-RESTful Routes
```php
Route::post('delete-user', ...);    // Should be DELETE /users/{id}
Route::post('delete-owner', ...);   // Should be DELETE /owners/{id}
Route::post('update-user', ...);    // Should be PUT/PATCH /users/{id}
Route::post('booking-cancel', ...); // Should be DELETE or PATCH /bookings/{id}
```

---

### 5.3 Duplicate Route Groups
**File:** `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    // Stripe subscription routes
});

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes (logout, profile)
});
```

**Recommendation:** Combine into single middleware group.

---

### 5.4 Route Prefix Inconsistency
```php
// Some routes have api prefix in base URL, some don't
Route::get('stripe/config', ...);           // /api/stripe/config
Route::get('products', ...);                // /api/products

// But apiResource creates nested paths
Route::apiResource('property', ...);        // /api/admin/property
```

---

### 5.5 Missing API Versioning
No API versioning strategy (`/api/v1/...`), making future changes difficult.

---

## 6. Model Issues

### 6.1 Incomplete $fillable Arrays
**File:** `app/Models/User.php`

```php
protected $fillable = [
    'name',      // Column doesn't exist (firstName, lastName do)
    'email',
    'password',
];
```

**Missing:** `firstName`, `lastName`, `address`, `phone`, `role`, etc.

---

### 6.2 Missing $guarded Property
Most models don't define `$fillable` or `$guarded`, leaving mass assignment unprotected.

**Models without protection:**
- `Bookings`
- `BookingOrder`
- `Property`
- `Resource`
- `ResourceType`
- `Facility`
- `PropertyImage`
- `Payment`

---

### 6.3 Incorrect Relationship Definitions
**File:** `app/Models/Property.php`

```php
public function owner()
{
    return $this->hasOne(User::class, 'id', 'ownerId');
}
```

**Issue:** Should be `belongsTo()`:
```php
public function owner()
{
    return $this->belongsTo(User::class, 'ownerId');
}
```

**File:** `app/Models/Bookings.php`

```php
public function bookingOrder()
{
    return $this->hasMany(BookingOrder::class, 'id', 'bookingOrderId');
}
```

**Issue:** Should be `belongsTo()`:
```php
public function bookingOrder()
{
    return $this->belongsTo(BookingOrder::class, 'bookingOrderId');
}
```

---

### 6.4 Missing Accessors/Mutators
No date casting or accessors for formatting dates consistently.

---

### 6.5 Model Missing Properties Declaration
**File:** `app/Models/Booking.php` (Mail class)

```php
public function __construct($data, string $recipientType)
{
    $this->data = $data;           // Not declared as property
    $this->recipientType = $recipientType; // Not declared as property
}
```

---

## 7. Controller Issues

### 7.1 Controller Size
**File:** `app/Http/Controllers/StripeController.php` - ~920 lines
**File:** `app/Http/Controllers/User/BookingsController.php` - ~462 lines
**File:** `app/Http/Controllers/Owner/PropertyController.php` - ~417 lines

**Recommendation:** Split into smaller controllers or use service classes.

---

### 7.2 Inconsistent Exception Handling
**Pattern 1:** Catching `\Throwable`
```php
} catch (\Throwable $th) {
    return response()->json(['status' => false, 'message' => $th->getMessage()]);
}
```

**Pattern 2:** Catching `\Exception`
```php
} catch (\Exception $e) {
    return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
}
```

**Pattern 3:** No exception handling at all

---

### 7.3 N+1 Query Problems
**File:** `app/Http/Controllers/StripeController.php`
**Method:** `getSubscriptionData()`

```php
foreach ($subscriptions as $sub) {
    $stripeSub = \Stripe\Subscription::retrieve($sub->stripe_id); // API call per subscription
    if ($sub->stripe_price) {
        $price = \Stripe\Price::retrieve($sub->stripe_price);     // Another API call
        $product = \Stripe\Product::retrieve($price->product);    // Another API call
    }
}
```

**Issue:** Multiple Stripe API calls per subscription in a loop.

---

### 7.4 Missing Authorization Checks
**File:** `app/Http/Controllers/Owner/PropertyController.php`
**Method:** `show()`

```php
public function show($id)
{
    $property = Property::where('id', $id)->with('facilities')->first();
    // No check if property belongs to current owner
}
```

**Issue:** Any owner can view any property.

---

### 7.5 Inconsistent Use of Auth
```php
Auth::id()           // Used in most places
Auth::user()->id     // Used in some places
auth()->id()         // Used in other places
auth()->user()->id   // Also used
$request->user()->id // And this too
```

---

### 7.6 Missing Database Transactions
**File:** `app/Http/Controllers/Admin/UserAndOwnerController.php`
**Method:** `delete()`

```php
private function delete($request, $role)
{
    // Multiple delete operations without transaction
    Resource::whereIn('resourceTypeId', $resourceTypeId)->delete();
    ResourceType::whereIn('propertyId', $property)->delete();
    PropertyImage::whereIn('propertyId', $property)->delete();
    Property::where('ownerId', $user->id)->delete();
    $user->delete();
}
```

**Recommendation:** Wrap in `DB::transaction()`.

---

### 7.7 Unused Imports
**File:** `app/Http/Controllers/Admin/BookingsController.php`

```php
use Illuminate\Support\Facades\Auth;  // Never used
use Carbon\Carbon;                     // Imported but using inline
```

**File:** `app/Http/Controllers/Admin/UserAndOwnerController.php`

```php
use App\Models\Bookings;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\BookingOrder;
use App\Models\Resource;
use App\Models\ResourceType;
// All used, but could be imported via model relationships
```

---

## 8. Middleware Issues

### 8.1 Inconsistent Class Naming
**Files:**
- `app/Http/Middleware/IsAdmin.php` - PascalCase ✓
- `app/Http/Middleware/isOwner.php` - camelCase ✗
- `app/Http/Middleware/IsUser.php` - PascalCase ✓

---

### 8.2 Missing HTTP Status Codes
**File:** `app/Http/Middleware/IsAdmin.php`

```php
return response()->json(['status' => false, 'message' => 'Unauthorized']);
// Missing 401 or 403 status code
```

**Should be:**
```php
return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
```

---

### 8.3 Swallowing Exceptions
```php
} catch (\Throwable $th) {
    return response()->json(['status' => false, 'message' => 'Unauthorized']);
}
```

**Issue:** Exception details are lost, no logging.

---

### 8.4 Missing Subscription Check Middleware
The `isOwner` middleware doesn't verify subscription status. An owner without active subscription can access protected routes.

---

## 9. Helper Function Issues

### 9.1 Function `hasActiveSubscription` Duplicates Model Method
**File:** `app/helper/helper.php`

```php
function hasActiveSubscription($user) { ... }
```

**File:** `app/Models/User.php`

```php
public function hasActiveSubscription() { ... }
```

**Issue:** Same logic exists in two places.

---

### 9.2 Missing Type Hints
```php
function getResourcesAvailable($resourceTypesId, $arrivalDateTime, $departureDateTime)
```

**Should be:**
```php
function getResourcesAvailable(int $resourceTypesId, string $arrivalDateTime, string $departureDateTime): Collection
```

---

### 9.3 No Input Validation
```php
function getPropertyName($propertyId)
{
    $property = Property::where('id', $propertyId)->first();
    return $property ? $property->propertyName : '';
}
```

**Issue:** No validation that `$propertyId` is a valid integer.

---

### 9.4 Inefficient Queries
```php
function getPropertyOwnerEmail($propertyId)
{
    $property = Property::where('id', $propertyId)->first();
    $owner = User::where('id', $property->ownerId)->first();
    return $owner ? $owner->email : '';
}
```

**Better:**
```php
function getPropertyOwnerEmail(int $propertyId): ?string
{
    return Property::with('owner:id,email')
        ->find($propertyId)
        ?->owner?->email;
}
```

---

## 10. Performance Issues

### 10.1 No Query Caching
Frequently accessed data like facilities, resource types are queried repeatedly.

---

### 10.2 Stripe API Calls in Loops
**File:** `app/Http/Controllers/StripeController.php`

Each subscription retrieval makes 1-3 Stripe API calls. With 100 subscriptions, that's 100-300 API calls.

---

### 10.3 Missing Eager Loading
**File:** `app/Http/Controllers/Owner/BookingsController.php`

```php
$bookings = BookingOrder::selectRaw(...)->whereIn('propertyId', $propertyIds)
    ->with(['property' => function ($query) { ... }]);
```

**Issue:** Conditionally loads relationships based on search, causing N+1 in some paths.

---

### 10.4 Large Result Sets Without Limits
**File:** `app/Http/Controllers/StripeController.php`
**Method:** `getAdminSubscriptions()`

```php
$subscriptions = \App\Models\Subscription::with('user')
    ->orderBy('created_at', 'desc')
    ->get();  // No limit!
```

---

### 10.5 Inefficient Date Comparisons
**File:** `app/helper/helper.php`

```php
->whereDate('arrivalDateTime', '<=', date('Y-m-d', strtotime($departureDateTime)))
```

**Issue:** `date()` and `strtotime()` called on every query. Use Carbon for consistency.

---

## 11. Best Practice Violations

### 11.1 No Logging
Very few log statements throughout the codebase. Critical operations like:
- Payment processing
- Subscription changes
- User deletions

Should all be logged.

---

### 11.2 No Rate Limiting
Sensitive endpoints like login, password reset have no rate limiting beyond Laravel defaults.

---

### 11.3 Missing PHPDoc Comments
Most methods lack documentation. Example:

```php
public function createSubscription(Request $request)
```

**Should have:**
```php
/**
 * Create a new Stripe subscription for an owner.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 * @throws \Stripe\Exception\ApiErrorException
 */
```

---

### 11.4 No Unit Tests for Critical Logic
**File:** `tests/` directory contains only example tests.

Missing tests for:
- Subscription logic
- Booking availability checks
- Payment processing
- Authorization logic

---

### 11.5 Hardcoded Values
```php
$perPage = $request->per_page ?? 10;  // Default pagination
$timeout = 30000;                      // API timeout
$currency = "GBP";                     // Currency
```

**Recommendation:** Move to config files.

---

### 11.6 No API Documentation
No OpenAPI/Swagger documentation for API endpoints.

---

## 12. Recommendations

### Immediate Actions (Security Critical)

1. **Remove or protect migration routes** - HIGH PRIORITY
2. **Encrypt Stripe secret keys** in database
3. **Hide sensitive fields** in API responses
4. **Add proper HTTP status codes** to error responses
5. **Replace env() calls** in controllers with config()

### Short-term Improvements

1. Create Form Request validation classes
2. Implement Service Layer for business logic
3. Add database indexes for frequently queried columns
4. Standardize naming conventions (snake_case for DB)
5. Fix incorrect Eloquent relationships
6. Add database transactions for multi-table operations
7. Implement consistent error handling and logging

### Medium-term Improvements

1. Add comprehensive test suite
2. Implement API versioning
3. Add API documentation (Swagger/OpenAPI)
4. Create API Resource classes for response formatting
5. Implement proper caching strategy
6. Add subscription check middleware for owner routes

### Long-term Architecture Changes

1. Consider Repository pattern for complex queries
2. Implement Event/Listener pattern for side effects (emails, notifications)
3. Use Queue for Stripe operations and email sending
4. Consider splitting into microservices if scaling needed
5. Implement proper audit logging

---

## File-by-File Issue Summary

| File | Critical | High | Medium | Low |
|------|----------|------|--------|-----|
| MigrationController.php | 1 | 0 | 0 | 0 |
| AuthController.php | 1 | 2 | 3 | 2 |
| StripeController.php | 0 | 3 | 5 | 4 |
| User/BookingsController.php | 0 | 2 | 4 | 3 |
| Owner/PropertyController.php | 0 | 1 | 3 | 2 |
| Owner/BookingsController.php | 0 | 1 | 2 | 1 |
| Admin/UserAndOwnerController.php | 0 | 0 | 3 | 2 |
| helper/helper.php | 0 | 1 | 4 | 3 |
| Models/* | 0 | 2 | 5 | 3 |
| Middleware/* | 0 | 1 | 2 | 1 |
| Migrations/* | 0 | 0 | 4 | 3 |
| routes/api.php | 1 | 1 | 3 | 2 |

---

---

## 13. File Handling Issues

### 13.1 Image Storage Path Inconsistency
**File:** `app/Http/Controllers/Owner/PropertyController.php`

```php
$fileName = $file->hashName();
$file->move(storage_path('app/public/property/images'), $fileName);
$propertyImage->image = $fileName;
```

**Issue:** 
1. Using `move()` instead of Laravel's `store()` method
2. Path handling inconsistency - using raw `storage_path()` instead of `Storage` facade
3. No file validation for dimensions, size, or type beyond basic mimes check

**Recommendation:**
```php
$path = $file->store('property/images', 'public');
$propertyImage->image = basename($path);
```

---

### 13.2 Missing File Cleanup on Deletion
**File:** `app/Http/Controllers/Owner/PropertyController.php`
**Method:** `deletePropertyImage()`

```php
public function deletePropertyImage(Request $request)
{
    $imageId = $request->imageId;
    $propertyImage = PropertyImage::whereIn('id', $imageId)->delete();
    return response()->json(['status' => true, 'message' => 'Image deleted successfully', 'data' => null]);
}
```

**Issue:** Database records deleted but actual files remain on disk, causing storage bloat.

**Recommendation:**
```php
public function deletePropertyImage(Request $request)
{
    $images = PropertyImage::whereIn('id', $request->imageId)->get();
    foreach ($images as $image) {
        Storage::disk('public')->delete("property/images/{$image->image}");
        $image->delete();
    }
    return response()->json(['status' => true, 'message' => 'Image deleted successfully']);
}
```

---

### 13.3 No File Size Limits in Validation
**File:** `app/Http/Controllers/Owner/PropertyController.php`

```php
$request->validate([
    'images.*' => 'mimes:jpeg,png,jpg,gif,svg',
]);
```

**Issue:** No maximum file size validation. Users could upload extremely large files.

**Recommendation:**
```php
$request->validate([
    'images.*' => 'mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
]);
```

---

### 13.4 Hardcoded Storage Paths
**Files:** Multiple controllers

```php
storage_path('app/public/property/images')
"/storage/property/images/{$image->image}"
```

**Issue:** Hardcoded paths throughout codebase. Should be configurable.

**Recommendation:** Create config for storage paths:
```php
// config/filestorage.php
return [
    'property_images' => 'property/images',
    'avatars' => 'avatars',
];
```

---

### 13.5 No Image Optimization
**Issue:** No image optimization or resizing on upload. Large images stored as-is.

**Recommendation:** Use Intervention Image for resizing:
```php
$image = Image::make($file)
    ->resize(1920, null, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    })
    ->encode('jpg', 85);
```

---

## 14. File Location & Organization Issues

### 14.1 Inconsistent Controller Namespacing
**Current Structure:**
```
app/Http/Controllers/
├── Admin/
│   ├── BookingsController.php    
│   ├── DashboardController.php   
│   ├── FacilityController.php    
│   ├── ProfileController.php     
│   ├── PropertyController.php    
│   └── UserAndOwnerController.php  <- Combined, inconsistent
├── Owner/
│   ├── BookingsController.php    
│   ├── DashboardController.php   
│   ├── FacilityController.php    
│   ├── PropertyController.php    
│   ├── ResourceController.php    
│   └── ResourceTypeController.php
├── User/
│   └── BookingsController.php    <- Only one controller, inconsistent depth
├── AuthController.php            <- Should be in Auth/ folder
├── Controller.php
├── MigrationController.php       <- Should be in Console/ or removed
└── StripeController.php          <- Should be in separate folder
```

**Recommended Structure:**
```
app/Http/Controllers/
├── Admin/
│   ├── BookingController.php     # Singular naming
│   ├── DashboardController.php
│   ├── FacilityController.php
│   ├── OwnerController.php       # Separate from user
│   ├── PropertyController.php
│   └── UserController.php        # Separate from owner
├── Auth/
│   └── AuthController.php
├── Owner/
│   └── ...
├── Payment/
│   └── StripeController.php
└── User/
    ├── BookingController.php
    └── ProfileController.php
```

---

### 14.2 Helper File Location
**Current:** `app/helper/helper.php`

**Issues:**
1. Lowercase folder name (`helper` vs `Helper`)
2. Single file with global functions
3. Non-standard Laravel location

**Recommendation:**
```
app/
├── Helpers/
│   ├── BookingHelper.php      # Class-based
│   ├── SubscriptionHelper.php
│   └── PropertyHelper.php
```

Or use proper Service classes.

---

### 14.3 Missing Folders
**Missing standard Laravel folders:**
- `app/Events/` - No event classes
- `app/Listeners/` - No listener classes  
- `app/Exceptions/` - No custom exceptions (Handler exists)
- `app/Jobs/` - No queue jobs
- `app/Notifications/` - No notification classes
- `app/Policies/` - No authorization policies
- `app/Rules/` - No custom validation rules
- `app/Http/Requests/` - No form request classes

---

### 14.4 Email Templates Location
**Current:** `resources/views/emails/`

Files:
- `reset-password.blade.php`
- `booking-confirmation.blade.php` (missing from read)
- `booking-owner.blade.php` (likely exists)

**Issue:** Email templates have inconsistent naming and no organization by type.

**Recommendation:**
```
resources/views/emails/
├── auth/
│   ├── reset-password.blade.php
│   └── welcome.blade.php
├── bookings/
│   ├── confirmation-guest.blade.php
│   └── confirmation-owner.blade.php
└── layouts/
    └── base.blade.php
```

---

### 14.5 Model Naming Issues
**File:** `app/Models/Bookings.php`

**Issue:** Plural model name. Laravel conventions dictate singular.

**Related Files Impacted:**
- All controllers using `Bookings::` 
- All relationships referencing `Bookings`
- Helper functions

**Recommendation:** Rename to `Booking.php` and update all references.

---

## 15. Additional Code Optimizations

### 15.1 Query Optimization in Availability Check
**File:** `app/helper/helper.php`

```php
function getResourcesAvailable($resourceTypesId, $arrivalDateTime, $departureDateTime)
{
    $bookedResourceIds = Bookings::where('resourceTypeId', $resourceTypesId)
        ->whereIn('status', ['pending', 'confirmed'])
        ->where(function ($query) use ($arrivalDateTime, $departureDateTime) {
            $query->whereDate('arrivalDateTime', '<=', date('Y-m-d', strtotime($departureDateTime)))
                  ->whereDate('departureDateTime', '>=', date('Y-m-d', strtotime($arrivalDateTime)));
        })
        ->pluck('resourceId');

    $availableResources = Resource::where('resourceTypeId', $resourceTypesId)
        ->whereNotIn('id', $bookedResourceIds)
        ->get();
```

**Issues:**
1. Two separate queries when one would suffice
2. No index usage optimization
3. Using `date()` and `strtotime()` instead of Carbon

**Optimized Version:**
```php
use Carbon\Carbon;

function getResourcesAvailable(int $resourceTypesId, string $arrivalDateTime, string $departureDateTime): Collection
{
    $arrival = Carbon::parse($arrivalDateTime)->startOfDay();
    $departure = Carbon::parse($departureDateTime)->endOfDay();

    return Resource::where('resourceTypeId', $resourceTypesId)
        ->whereDoesntHave('bookings', function ($q) use ($arrival, $departure) {
            $q->whereIn('status', ['pending', 'confirmed'])
              ->where('arrivalDateTime', '<=', $departure)
              ->where('departureDateTime', '>=', $arrival);
        })
        ->get();
}
```

---

### 15.2 Subscription Check Optimization
**File:** `app/helper/helper.php`
**Lines:** 53-163

The `getSubscriptionDetails()` function is 110 lines and makes multiple Stripe API calls.

**Issues:**
1. Function is too long (should be < 50 lines)
2. Multiple Stripe API calls for single check
3. No caching of subscription data

**Recommendation:**
1. Cache subscription status for 5-10 minutes
2. Split into smaller functions
3. Use webhook events to update subscription status proactively

```php
function getSubscriptionDetails(User $user): array
{
    return Cache::remember(
        "subscription:{$user->id}",
        now()->addMinutes(5),
        fn() => $this->fetchSubscriptionFromStripe($user)
    );
}
```

---

### 15.3 Booking Calculation Optimization
**File:** `app/Http/Controllers/User/BookingsController.php`

```php
$resourceTypeCount = ResourceType::whereIn('propertyId', $propertyIds)->count();
$resourceCount = Resource::whereIn('resourceTypeId', function($query) use ($propertyIds) {
    $query->select('id')->from('resource_types')->whereIn('propertyId', $propertyIds);
})->count();
```

**Issue:** Two queries for dashboard counts.

**Optimized:**
```php
$stats = DB::table('resource_types as rt')
    ->leftJoin('resources as r', 'r.resourceTypeId', '=', 'rt.id')
    ->whereIn('rt.propertyId', $propertyIds)
    ->selectRaw('COUNT(DISTINCT rt.id) as type_count, COUNT(r.id) as resource_count')
    ->first();
```

---

### 15.4 Stripe API Call Batching
**File:** `app/Http/Controllers/StripeController.php`

```php
foreach ($subscriptions as $sub) {
    $stripeSub = \Stripe\Subscription::retrieve($sub->stripe_id);
    if ($sub->stripe_price) {
        $price = \Stripe\Price::retrieve($sub->stripe_price);
        $product = \Stripe\Product::retrieve($price->product);
    }
}
```

**Issue:** N+1 API calls to Stripe (potentially 3 calls per subscription).

**Optimized:**
```php
// Collect all IDs first
$subIds = $subscriptions->pluck('stripe_id');
$priceIds = $subscriptions->pluck('stripe_price')->filter();

// Batch fetch from Stripe
$stripeSubscriptions = \Stripe\Subscription::all(['ids' => $subIds->toArray()]);
$prices = \Stripe\Price::all(['ids' => $priceIds->toArray()]);
$products = \Stripe\Product::all(['ids' => $prices->pluck('product')->toArray()]);

// Map data
// ...
```

---

### 15.5 Database Transaction Patterns
**File:** `app/Http/Controllers/Owner/PropertyController.php`

```php
public function store(Request $request)
{
    // Creates Property
    $property = Property::create([...]);
    
    // Then images
    foreach ($request->images as $image) {
        PropertyImage::create([...]);
    }
    
    // Then facilities
    $property->facilities()->sync([...]);
}
```

**Issue:** No transaction. If image upload fails mid-way, orphaned records remain.

**Optimized:**
```php
public function store(Request $request)
{
    return DB::transaction(function () use ($request) {
        $property = Property::create([...]);
        
        foreach ($request->images as $image) {
            PropertyImage::create([...]);
        }
        
        $property->facilities()->sync([...]);
        
        return $property;
    });
}
```

---

### 15.6 Eager Loading Patterns
**File:** `app/Http/Controllers/Admin/PropertyController.php`

```php
public function index(Request $request)
{
    $properties = Property::query();
    // ... filtering
    $properties = $properties->paginate($perPage);
    
    // Later in response or view:
    foreach ($properties as $p) {
        $p->owner->name;        // N+1
        $p->facilities->count; // N+1
    }
}
```

**Optimized:**
```php
$properties = Property::with(['owner:id,firstName,lastName', 'facilities'])
    ->withCount('facilities')
    ->paginate($perPage);
```

---

### 15.7 String Concatenation in Queries
**File:** `app/Http/Controllers/Admin/DashboardController.php`

```php
->selectRaw("DATE_FORMAT(created_at, '%a') as day")
```

**Issue:** Database-specific syntax. Won't work if switching databases.

**Recommendation:** Use Carbon for formatting or create accessor in model.

---

### 15.8 Unused Method Parameters
**File:** `app/Http/Controllers/Admin/BookingsController.php`

```php
public function update(Request $request)
{
    // Method is empty
}

public function destroy(Request $request)
{
    // Method is empty
}
```

**Issue:** Empty methods declared. Either implement or remove.

---

### 15.9 Configuration Caching Consideration
**Issue:** With `env()` calls in controllers, config caching (`php artisan config:cache`) will break the application.

All `env()` calls must be moved to config files:
- `AuthController.php` line 227: `env('FRONTEND_URL')`
- `StripeController.php` line 44: `env('STRIPE_KEY')`

---

### 15.10 Model Events for Side Effects
**Issue:** Side effects (emails, notifications) handled directly in controllers.

**Recommendation:** Use model observers:
```php
// app/Observers/BookingOrderObserver.php
class BookingOrderObserver
{
    public function created(BookingOrder $booking)
    {
        Mail::to($booking->guestEmail)->send(new BookingConfirmation($booking));
    }
}
```

---

## 16. Email Template Issues

### 16.1 Reset Password Template
**File:** `resources/views/emails/reset-password.blade.php`

```php
<a href="{{ url('/reset-password?token=') . $token }}" ...>
```

**Issues:**
1. Using `url()` instead of `route()` helper
2. Token exposed in plain URL
3. No expiration display for user

---

### 16.2 Missing Email Templates
**Expected but not found:**
- Booking cancellation email
- Password changed confirmation
- Owner booking notification
- Payment failure notification

---

## 17. Test Coverage Issues

### 17.1 Existing Tests
**Files in `tests/`:**
- `Feature/ExampleTest.php` - Default Laravel test
- `Feature/OwnerMiddlewareTest.php` - One middleware test
- `Unit/ExampleTest.php` - Default Laravel test

### 17.2 Missing Critical Tests
- Authentication flow tests
- Booking creation/cancellation tests
- Payment processing tests
- Subscription lifecycle tests
- Authorization policy tests
- API endpoint tests

---

*Review completed: January 12, 2026*
*Reviewer: Senior Developer Code Review*
*Updated: January 12, 2026 - Added file handling, location, and optimization sections*