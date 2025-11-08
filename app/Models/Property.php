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
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = self::generateSlug($property->propertyName);
            }
        });

        static::updating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = self::generateSlug($property->propertyName);
            }
        });
    }

    private static function generateSlug($name)
    {
        $slug  = Str::slug($name);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
