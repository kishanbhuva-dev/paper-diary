<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Property extends Model
{
    protected $table = 'property';

    /**
     * @return HasOne<User, $this>
     */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'ownerId');
    }

    /**
     * @return BelongsTo<ResourceType, $this>
     */
    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class, 'propertyId', 'id');
    }

    /**
     * @return HasMany<ResourceType, $this>
     */
    public function resourceTypes(): HasMany
    {
        return $this->hasMany(ResourceType::class, 'propertyId', 'id');
    }

    /**
     * @return HasMany<PropertyImage, $this>
     */
    public function propertyImage(): HasMany
    {
        return $this->hasMany(PropertyImage::class, 'propertyId', 'id');
    }

    /**
     * @return BelongsToMany<Facility, $this>
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_property', 'propertyId', 'facilityId');
    }

    /**
     * @return HasMany<BookingOrder, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(BookingOrder::class, 'propertyId', 'id');
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

    private static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);

        $count = static::where('slug', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
