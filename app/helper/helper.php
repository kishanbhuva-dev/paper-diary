<?php

use App\Models\Booking;
use App\Models\Resource;
use App\Models\ResourceType;

/**
 * Get available resources for a given resource type and time frame.
 *
 * @param int $resourcesTypeId
 * @param string $arrivalDateTime
 * @param string $departureDateTime
 * @return \Illuminate\Database\Eloquent\Collection
 */
// function getResourceAvailable($resourceTypesId, $arrivalDateTime, $departureDateTime)
// {
//     // return $resourcesTypeId;
//     $bookedResourceIds = [];
//     $bookedResourceIds = Booking::where('arrivalDateTime', '<', $departureDateTime)
//         ->where('departureDateTime', '>', $arrivalDateTime)
//         ->pluck('resourcesId')
//         ->unique();

//     $availableResources = Resource::where('resourceTypeId', $resourceTypesId)
//         ->whereNotIn('id', $bookedResourceIds)
//         ->pluck('id');

//     return $availableResources;
// }
// function getResourcesAvailable(array $resourceTypesIds, $arrivalDateTime, $departureDateTime)
// {
//     $bookedResourceIds = Booking::where('arrivalDateTime', '<', $departureDateTime)
//         ->where('departureDateTime', '>', $arrivalDateTime)
//     // Ensure we only check bookings for the requested types
//         ->whereHas('resource', fn($query) =>
//             $query->whereIn('resourceTypeId', $resourceTypesIds)
//         )
//         ->pluck('resourcesId')
//         ->unique();

//     return Resource::whereIn('resourceTypeId', $resourceTypesIds)
//         ->whereNotIn('id', $bookedResourceIds)
//         ->get();
// }
function getResourcesAvailable(array $resourceTypesIds, $arrivalDateTime, $departureDateTime)
{
    $resourceTypes      = ResourceType::whereIn('id', $resourceTypesIds)->get();
    $availableResources = collect();

    foreach ($resourceTypes as $resourceType) {
        $query = Booking::whereHas('resource', function ($query) use ($resourceType) {
            $query->where('resourceTypesId', $resourceType->id);
        });
        if ($resourceType->slot === 'hourly') {
            $query->where('departureDateTime', '<', $departureDateTime)
                ->where('arrivalDateTime', '>', $arrivalDateTime);
        } else {
            $query->whereDate('departureDateTime', '>', date('Y-m-d', strtotime($arrivalDateTime)))
                ->whereDate('arrivalDateTime', '<', date('Y-m-d', strtotime($departureDateTime)));
        }
        $bookedResourceIds = $query->pluck('resourcesId')->unique();

        $resources = Resource::where('resourceTypeId', $resourceType->id)
            ->whereNotIn('id', $bookedResourceIds)
            ->get();
        $availableResources = $availableResources->merge($resources);
    }
    return $availableResources;
}
