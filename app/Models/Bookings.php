<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    public function resource()
    {
        return $this->hasMany(Resource::class, 'id', 'resourceId');
    }
    public function bookingOrder()
    {
        return $this->hasMany(BookingOrder::class, 'id', 'bookingOrderId');
    }
}
