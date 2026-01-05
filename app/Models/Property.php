<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Property extends Model
{
    protected $table = 'property';
    public function owner()
    {
        return $this->hasOne(User::class, 'id', 'ownerId');
    }
    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class, 'propertyId', 'id');
    }
    public function resourceTypes()
    {
        return $this->hasMany(ResourceType::class, 'propertyId', 'id');
    }
    protected static function boot()
{
    parent::boot();

    static::creating(function ($property) {
        if (empty($property->slug)) {
            $property->slug = self::generateSlug($property->propertyName);
        }
    });

    static::updating(function ($property) {
        if ($property->isDirty('propertyName')) {
            $property->slug = self::generateSlug($property->propertyName);
        }
    });
}

private static function generateSlug($name)
{
    $slug = Str::slug($name);
    
    $count = static::where('slug', 'LIKE', "{$slug}%")->count();

    return $count ? "{$slug}-{$count}" : $slug;
}
    public function propertyImage()
    {
        return $this->hasMany(PropertyImage::class, 'propertyId', 'id');
    }
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_property', 'propertyId', 'facilityId');
    }
    public function bookings()
    {
        return $this->hasMany(BookingOrder::class, 'propertyId', 'id');
    }
}