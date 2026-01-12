<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingOrder extends Model
{
    public function property()
    {
        return $this->belongsTo(Property::class, 'propertyId');
    }

    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class, 'resourceTypeId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resourceId');
    }

    public function booking()
    {
        return $this->HasMany(Bookings::class, 'bookingOrderId', 'id');
    }
}
