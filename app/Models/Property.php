<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'property';

    public function owner()
    {
        return $this->hasOne(User::class, 'id', 'ownerId');
    }
}
