<?php

use App\Models\Bookings;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Property;

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
        ->whereNotIn('id', $bookedResourceIds)
        ->pluck('id');        
    return $availableResourceIds; 
}
function getResourcesAvailableByProperty($propertyId, $arrivalDateTime, $departureDateTime)
{
    $resourceTypes = ResourceType::where('propertyId', $propertyId)->get();
    $result = [];
    foreach ($resourceTypes as $resourceType) {
        $bookedResourceIdsQuery = Bookings::where('resourceTypeId', $resourceType->id);

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
        });
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
    return $property ? $property->email : '';
}