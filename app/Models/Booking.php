<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    public function resource()
    {
        return $this->hasMany(Resource::class, 'id', 'resourcesId');
    }
}
