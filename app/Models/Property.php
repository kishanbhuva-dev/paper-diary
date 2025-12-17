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

    // 1. ON CREATE (Store)
    // Generate slug if it wasn't manually provided
    static::creating(function ($property) {
        if (empty($property->slug)) {
            $property->slug = self::generateSlug($property->propertyName);
        }
    });

    // 2. ON UPDATE
    // Only regenerate the slug IF 'propertyName' has changed
    static::updating(function ($property) {
        if ($property->isDirty('propertyName')) {
            $property->slug = self::generateSlug($property->propertyName);
        }
    });
}

// 3. SLUG GENERATION LOGIC
private static function generateSlug($name)
{
    $slug = Str::slug($name);
    
    // Check for existing slugs starting with this string
    $count = static::where('slug', 'LIKE', "{$slug}%")->count();

    // If duplicates exist, append the count (e.g., name-1)
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
}