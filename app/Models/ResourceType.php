<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceType extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',

    ];
    public function resources()
    {
        return $this->hasMany(Resource::class, 'resourceTypeId', 'id');
    }
    public function property()
    {
        return $this->hasOne(Property::class, 'id', 'propertyId');
    }
}
