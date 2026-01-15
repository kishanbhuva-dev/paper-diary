<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bookings extends Model
{
    /**
     * @return HasMany<\App\Models\Resource, $this>
     */
    public function resource(): HasMany
    {
        return $this->hasMany(Resource::class, 'id', 'resourceId');
    }

    /**
     * @return HasMany<BookingOrder, $this>
     */
    public function bookingOrder(): HasMany
    {
        return $this->hasMany(BookingOrder::class, 'id', 'bookingOrderId');
    }
}
