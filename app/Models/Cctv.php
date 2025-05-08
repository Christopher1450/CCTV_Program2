<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cctv extends Model
{
    protected $fillable = ['branch_id', 'cctv_position_id','name'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function position()
    {
        return $this->belongsTo(CctvPosition::class, 'cctv_position_id');
    }

    public function notes()
    {
        return $this->hasMany(CctvNote::class);
    }
}
