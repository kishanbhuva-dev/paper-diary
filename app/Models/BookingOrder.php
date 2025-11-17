<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingOrder extends Model
{
    function property()
    {
        return $this->belongsTo(Property::class, 'propertyId');
    }
    function resourceType()
    {
        return $this->belongsTo(ResourceType::class, 'resourceTypeId');
    }
    function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
    
}
