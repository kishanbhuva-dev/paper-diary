<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'facility_property', 'facilityId', 'propertyId');
    }
}
