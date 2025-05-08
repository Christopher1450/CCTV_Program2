<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CctvPosition extends Model
{
    protected $fillable = ['name'];

    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }
}
