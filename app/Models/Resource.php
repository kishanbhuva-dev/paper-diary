<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class, 'resourceTypeId', 'id');
    }
    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'resourceId', 'id');
    }
}