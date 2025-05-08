<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpCamAccount extends Model
{
    protected $fillable = ['email'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
