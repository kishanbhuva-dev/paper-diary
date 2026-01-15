<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resource extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return HasOne<ResourceType, $this>
     */
    public function resourceType(): HasOne
    {
        return $this->hasOne(ResourceType::class, 'id', 'resourceTypeId');
    }

    /**
     * @return HasMany<Bookings, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Bookings::class, 'resourceId', 'id');
    }
}
