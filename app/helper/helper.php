<?php

use App\Models\Bookings;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Property;
use App\Models\User;
// function getResourcesAvailable($resourceTypesId, $arrivalDateTime, $departureDateTime)
// {
//     $resourceType = ResourceType::where('id', $resourceTypesId)->first();
//     if (!$resourceType) {
//         return collect();
//     }

//     $bookedResourceIdsQuery = Bookings::where('resourceTypeId', $resourceTypesId);

//     if ($resourceType->slot === 'hourly') {
//         $bookedResourceIdsQuery->where('arrivalDateTime', '<=', $departureDateTime)
//             ->where('departureDateTime', '>=', $arrivalDateTime);
//     } else {
//         $bookedResourceIdsQuery->whereDate('arrivalDateTime', '<=', date('Y-m-d', strtotime($departureDateTime)))
//             ->whereDate('departureDateTime', '>=', date('Y-m-d', strtotime($arrivalDateTime)));
//     }

//     $bookedResourceIds = $bookedResourceIdsQuery->pluck('resourceId')->unique();

//     $availableResourceIds = Resource::where('resourceTypeId', $resourceTypesId)
//         ->whereNotIn('id', $bookedResourceIds)
//         ->pluck('id');        
//     return $availableResourceIds;
// }
// function getResourcesTypeAvailable($arrivalDateTime, $departureDateTime,$totalResourcesNeeded,$adults)
// {
//     $availableResourceTypes = ResourceType::where('capacity', '>=', $adults)
//         ->get()
//         ->filter(function ($resourceType) use ($arrivalDateTime, $departureDateTime, $totalResourcesNeeded) {
//             $availableCount = getResourcesAvailable($resourceType->id, $arrivalDateTime, $departureDateTime)->count();
//             return $availableCount >= $totalResourcesNeeded;
//         })
//         ->map(function ($resourceType) {
//             return [
//             'id' => $resourceType->id,
//             'name' => $resourceType->name,
//             'capacity' => $resourceType->capacity,
//             'slot' => $resourceType->slot,
//             'message' => 'Available',
//             'details' => 'This resource type meets your requirements'
//             ];
//         });
        

//     return $availableResourceTypes;
// }
function getResourcesAvailable($resourceTypesId, $arrivalDateTime, $departureDateTime) 
{ 
    $resourceType = ResourceType::where('id', $resourceTypesId)->first(); 
    if (!$resourceType) { 
        return collect(); 
    } 

    $bookedResourceIdsQuery = Bookings::where('resourceTypeId', $resourceTypesId); 

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
function getResourcesAvailableByProperty($propertyId, $arrivalDateTime, $departureDateTime)
{
    $resourceTypes = ResourceType::where('propertyId', $propertyId)->get();
    $result = [];
    foreach ($resourceTypes as $resourceType) {
        $bookedResourceIdsQuery = Bookings::where('resourceTypeId', $resourceType->id)->where('status', 'confirmed');

        if ($resourceType->slot === 'hourly') {
            $bookedResourceIdsQuery->where('arrivalDateTime', '<=', $departureDateTime)
                ->where('departureDateTime', '>=', $arrivalDateTime);
        } else {
            $bookedResourceIdsQuery->whereDate('arrivalDateTime', '<=', date('Y-m-d', strtotime($departureDateTime)))
                ->whereDate('departureDateTime', '>=', date('Y-m-d', strtotime($arrivalDateTime)));
        }

        $bookedResourceIds = $bookedResourceIdsQuery->pluck('resourceId')->unique();
        $availableResources = Resource::where('resourceTypeId', $resourceType->id)
            ->whereNotIn('id', $bookedResourceIds)
            ->get();
        $result[] = [
            'resourceTypeId' => $resourceType->id,
            'resourceTypeName' => $resourceType->name,
            'availableResources' => $availableResources
        ];
    }
    return $result;
}
function getResourcesTypeAvailableByProperty($propertyId, $arrivalDateTime, $departureDateTime, $totalResourcesNeeded)
{
    if (!$propertyId || !$arrivalDateTime || !$departureDateTime || !$totalResourcesNeeded) {
        return collect();
    }

    $availableResourceTypes = ResourceType::where('propertyId', $propertyId)->with(['resources'=>function($query) use($totalResourcesNeeded){
        $query->where('status', 1)->orderBy('id','asc')->limit($totalResourcesNeeded);
    }])
        ->get()
        ->filter(function ($resourceType) use ($arrivalDateTime, $departureDateTime, $totalResourcesNeeded) {
            $availableCount = getResourcesAvailable($resourceType->id, $arrivalDateTime, $departureDateTime)->count();
            return $availableCount >= $totalResourcesNeeded;
        })->values();
    return $availableResourceTypes;
}
function getPropertyName($propertyId)
{
    $property = Property::where('id', $propertyId)->first();
    return $property ? $property->propertyName : '';
}
function getResourceTypeName($resourceTypeId)
{
    $resourceType = ResourceType::where('id', $resourceTypeId)->first();
    return $resourceType ? $resourceType->name : '';
}
function getPropertyOwnerEmail($propertyId)
{
    $property = Property::where('id', $propertyId)->first();
    $owner = User::where('id', $property->ownerId)->first();
    return $owner ? $owner->email : '';
}
function hasActiveSubscription($user)
    {
        if ($user->role !== 'owner') {
            return false;
        }
        $subscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->whereHas('user', function ($query) use ($user) {
                $query->where('id', $user->id)
                   ->where('role', 'owner');
            })
            ->first();
        if ($subscription) {
            try {
                \Stripe\Stripe::setApiKey(config('cashier.secret'));
                $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
                return $stripeSubscription->status === 'active' && 
                       $subscription->stripe_status === 'active' &&
                       ($subscription->ends_at === null || $subscription->ends_at->isFuture());
            } catch (\Exception $e) {
                return $subscription->stripe_status === 'active' &&
                       ($subscription->ends_at === null || $subscription->ends_at->isFuture());
            }
        }

        return false;
    }

function getSubscriptionDetails($user)
{
    if ($user->role !== 'owner') {
        return null;
    }
    
    $subscription = $user->subscriptions()
        ->with(['user'])
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$subscription) {
        return null;
    }
    
    $isSubscriptionActive = function($subscription, $stripeSubscription = null) {
        return $subscription->stripe_status === 'active' &&
               ($subscription->ends_at === null || $subscription->ends_at->isFuture());
    };
    
    try {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));
        
        $stripeSubscription = null;
        $stripeStatus = 'unknown';
        
        // Safely try to retrieve Stripe subscription
        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
            if ($stripeSubscription) {
                $stripeStatus = $stripeSubscription->status;
            }
        } catch (\Exception $stripeException) {
            \Log::warning('Could not retrieve Stripe subscription', [
                'subscription_id' => $subscription->stripe_id,
                'error' => $stripeException->getMessage()
            ]);
            $stripeSubscription = null;
            $stripeStatus = 'unknown';
        }
        
        return [
            'database' => [
                'id' => $subscription->id,
                'stripe_id' => $subscription->stripe_id,
                'status' => $subscription->stripe_status,
                'price_id' => $subscription->stripe_price,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'created_at' => $subscription->created_at,
                'updated_at' => $subscription->updated_at,
            ],
            'stripe' => [
                'id' => $subscription->stripe_id,
                'status' => $stripeStatus,
                'current_period_start' => $stripeSubscription ? $stripeSubscription->current_period_start : null,
                'current_period_end' => $stripeSubscription ? $stripeSubscription->current_period_end : null,
                'cancel_at_period_end' => $stripeSubscription ? $stripeSubscription->cancel_at_period_end : false,
                'ended_at' => $stripeSubscription ? $stripeSubscription->ended_at : null,
                'trial_start' => $stripeSubscription ? $stripeSubscription->trial_start : null,
                'trial_end' => $stripeSubscription ? $stripeSubscription->trial_end : null,
                'payment_status' => 'activated',
                'refund_status' => 'not_refunded',
            ],
            'is_active' => $isSubscriptionActive($subscription, $stripeSubscription),
            'user' => [
                'id' => $user->id,
                'name' => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];
    } catch (\Exception $e) {
        \Log::error('Error fetching Stripe subscription details: ' . $e->getMessage());
        
        return [
            'database' => [
                'id' => $subscription->id,
                'stripe_id' => $subscription->stripe_id,
                'status' => $subscription->stripe_status,
                'price_id' => $subscription->stripe_price,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'created_at' => $subscription->created_at,
                'updated_at' => $subscription->updated_at,
            ],
            'stripe' => [
                'id' => $subscription->stripe_id,
                'status' => $subscription->stripe_status,
                'current_period_start' => null,
                'current_period_end' => null,
                'cancel_at_period_end' => false,
                'ended_at' => null,
                'trial_start' => null,
                'trial_end' => null,
                'payment_status' => 'activated',
                'refund_status' => 'not_refunded',
            ],
            'is_active' => $isSubscriptionActive($subscription),
            'user' => [
                'id' => $user->id,
                'name' => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];
    }
}

function getPlanDetailsFromPriceId($priceId)
{
    $defaults = [
        'name' => 'No Plan Data',
        'amount' => '0.00',
        'interval' => 'No interval',
    ];

    if (!$priceId) {
        return $defaults;
    }

    try {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));
        $price = \Stripe\Price::retrieve($priceId);
        $product = \Stripe\Product::retrieve($price->product);

        $intervalCount = $price->recurring->interval_count;
        $interval = $price->recurring->interval;

        return [
            'name' => $product->name ?: 'Professional Plan',
            'amount' => number_format($price->unit_amount / 100, 2),
            'interval' => $intervalCount == 1 ? 'per ' . $interval : 'every ' . $intervalCount . ' ' . $interval . 's',
        ];
    } catch (\Exception $e) {
        // If Stripe API fails, return default values
        return $defaults;
    }
}