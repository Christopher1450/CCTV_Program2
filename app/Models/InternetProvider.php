<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetProvider extends Model
{
    protected $fillable = ['name'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
