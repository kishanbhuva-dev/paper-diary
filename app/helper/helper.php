<?php

use App\Models\Bookings;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Property;

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