<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResourceType extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return HasMany<\App\Models\Resource, $this>
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'resourceTypeId', 'id');
    }

    /**
     * @return HasOne<Property, $this>
     */
    public function property(): HasOne
    {
        return $this->hasOne(Property::class, 'id', 'propertyId');
    }
}
