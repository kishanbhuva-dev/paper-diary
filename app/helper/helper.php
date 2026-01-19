<?php

use App\Models\Bookings;
use App\Models\Property;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

/**
 * @return Collection<int, int>
 */
function getResourcesAvailable(int $resourceTypesId, string $arrivalDateTime, string $departureDateTime): Collection
{
    $resourceType = ResourceType::where('id', $resourceTypesId)->first();
    if (! $resourceType) {
        return collect();
    }

    $bookedResourceIdsQuery = Bookings::where('resourceTypeId', $resourceTypesId)
        ->where('status', 'confirmed');

    if ($resourceType->slot === 'hourly') {
        $bookedResourceIdsQuery->where('arrivalDateTime', '<=', $departureDateTime)
            ->where('departureDateTime', '>=', $arrivalDateTime);
    } else {
        $bookedResourceIdsQuery->whereDate('arrivalDateTime', '<=', date('Y-m-d', strtotime($departureDateTime)))
            ->whereDate('departureDateTime', '>=', date('Y-m-d', strtotime($arrivalDateTime)));
    }

    $bookedResourceIds = $bookedResourceIdsQuery->pluck('resourceId')->unique();

    $availableResourceIds = Resource::where('resourceTypeId', $resourceTypesId)
        ->whereNotIn('id', $bookedResourceIds)->where('status', 1)
        ->pluck('id');

    return $availableResourceIds;
}
/**
 * @return Collection<int, array{
 *     resourceTypeId: int,
 *     resourceTypeName: string,
 *     availableResources: Illuminate\Database\Eloquent\Collection<int, App\Models\Resource>
 * }>
 */
function getResourcesAvailableByProperty(int $propertyId, string $arrivalDateTime, string $departureDateTime): Collection
{
    $resourceTypes = ResourceType::where('propertyId', $propertyId)->get();

    $result = [];

    foreach ($resourceTypes as $resourceType) {
        $bookedResourceIdsQuery = Bookings::where('resourceTypeId', $resourceType->id)
            ->where('status', 'confirmed');

        if ($resourceType->slot === 'hourly') {
            $bookedResourceIdsQuery
                ->where('arrivalDateTime', '<=', $departureDateTime)
                ->where('departureDateTime', '>=', $arrivalDateTime);
        } else {
            $bookedResourceIdsQuery
                ->whereDate('arrivalDateTime', '<=', date('Y-m-d', strtotime($departureDateTime)))
                ->whereDate('departureDateTime', '>=', date('Y-m-d', strtotime($arrivalDateTime)));
        }

        $bookedResourceIds = $bookedResourceIdsQuery
            ->pluck('resourceId')
            ->unique();

        $availableResources = Resource::where('resourceTypeId', $resourceType->id)
            ->whereNotIn('id', $bookedResourceIds)
            ->get();

        $result[] = [
            'resourceTypeId'     => $resourceType->id,
            'resourceTypeName'   => $resourceType->name,
            'availableResources' => $availableResources,
        ];
    }

    return collect($result);
}
/**
 * @return Collection<int, ResourceType>
 */
function getResourcesTypeAvailableByProperty(int $propertyId, string $arrivalDateTime, string $departureDateTime, int $totalResourcesNeeded): Collection
{
    if (! $propertyId || ! $arrivalDateTime || ! $departureDateTime || ! $totalResourcesNeeded) {
        return collect();
    }

    $availableResourceTypes = ResourceType::where('propertyId', $propertyId)->with(['resources' => function ($query) use ($totalResourcesNeeded) {
        $query->where('status', 1)->orderBy('id', 'asc')->limit($totalResourcesNeeded);
    }])
        ->get()
        ->filter(function ($resourceType) use ($arrivalDateTime, $departureDateTime, $totalResourcesNeeded) {
            $availableCount = getResourcesAvailable($resourceType->id, $arrivalDateTime, $departureDateTime)->count();

            return $availableCount >= $totalResourcesNeeded;
        })->values();

    return $availableResourceTypes;
}
function getPropertyName(int $propertyId): string
{
    $property = Property::where('id', $propertyId)->first();

    return $property ? $property->propertyName : '';
}
function getResourceTypeName(int $resourceTypeId): string
{
    $resourceType = ResourceType::where('id', $resourceTypeId)->first();

    return $resourceType ? $resourceType->name : '';
}
function getPropertyOwnerEmail(int $propertyId): string
{
    $property = Property::where('id', $propertyId)->first();
    $owner = User::where('id', $property->ownerId)->first();

    return $owner ? $owner->email : '';
}
function hasActiveSubscription(User $user): bool
{
    if ($user->role !== 'owner') {
        return false;
    }

    /** @var Subscription|null $subscription */
    $subscription = $user->subscriptions()
        ->where('stripe_status', 'active')
        ->where(function ($query) {
            $query->whereNull('ends_at')
                ->orWhere('ends_at', '>', now());
        })
        ->first();

    if ($subscription) {
        try {
            Stripe::setApiKey(config('cashier.secret'));
            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_id);

            return $stripeSubscription->status === 'active'
                && $subscription->stripe_status === 'active'
                && ($subscription->ends_at === null || $subscription->ends_at->isFuture());
        } catch (Exception $e) {
            return $subscription->stripe_status === 'active'
                && ($subscription->ends_at === null || $subscription->ends_at->isFuture());
        }
    }

    return false;
}

/**
 * @return array<string, mixed>
 */
function getSubscriptionDetails(int $user_id): array
{
    try {
        /** @var User|null $user */
        $user = User::where('id', $user_id)->first();

        if (! $user || $user->role !== 'owner') {
            return [];
        }

        /** @var Subscription|null $subscription */
        $subscription = $user->subscriptions()
            ->with('user')
            ->latest()
            ->first();

        if (! $subscription) {
            return [];
        }

        Stripe::setApiKey(config('cashier.secret'));

        /** @var StripeSubscription $stripeSub */
        $stripeSub = StripeSubscription::retrieve($subscription->stripe_id);

        $isDbActive = $subscription->stripe_status === 'active'
            && ($subscription->ends_at === null || $subscription->ends_at->isFuture());

        return [
            'database' => [
                'id'            => $subscription->id,
                'stripe_id'     => $subscription->stripe_id,
                'status'        => $subscription->stripe_status,
                'price_id'      => $subscription->stripe_price,
                'quantity'      => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at'       => $subscription->ends_at,
                'created_at'    => $subscription->created_at?->toDateTimeString(),
                'updated_at'    => $subscription->updated_at?->toDateTimeString(),
            ],

            'stripe' => [
                'id'                   => $stripeSub->id,
                'status'               => $stripeSub->status ?? 'unknown',
                'current_period_start' => $stripeSub->current_period_start ?? null,
                'current_period_end'   => $stripeSub->current_period_end ?? null,
                'cancel_at_period_end' => $stripeSub->cancel_at_period_end ?? false,
                'ended_at'             => $stripeSub->ended_at ?? null,
                'trial_start'          => $stripeSub->trial_start ?? null,
                'trial_end'            => $stripeSub->trial_end ?? null,
                'payment_status'       => 'activated',
                'refund_status'        => 'not_refunded',
            ],

            'is_active' => $stripeSub->status,
            'user'      => [
                'id'    => $user->id,
                'name'  => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ];
    } catch (Throwable $e) {
        Log::warning('Could not retrieve Stripe subscription', [
            'subscription_id' => 'unknown',
            'user_id'         => $user_id,
            'error'           => $e->getMessage(),
        ]);

        return [];
    }
}

/**
 * @return array{
 *     name: string,
 *     amount: string,
 *     interval: string
 * }
 */
function getPlanDetailsFromPriceId(string $priceId): array
{
    try {
        Stripe::setApiKey(config('cashier.secret'));
        $price = Price::retrieve($priceId);
        $product = Product::retrieve($price->product);
        $intervalCount = $price->recurring->interval_count;
        $interval = $price->recurring->interval;

        return ['name' => $product->name ?: 'Professional Plan', 'amount' => number_format($price->unit_amount / 100, 2), 'interval' => $intervalCount === 1 ? 'per ' . $interval : 'every ' . $intervalCount . ' ' . $interval . 's'];
    } catch (Throwable $e) {
        return ['name' => 'No Plan Data', 'amount' => '0.00', 'interval' => 'No interval'];
    }
}
