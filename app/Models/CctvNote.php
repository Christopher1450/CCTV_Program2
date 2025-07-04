<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CctvNote extends Model
{
    protected $fillable = ['cctv_id', 'notes', 'created_by'];

    public $timestamps = false;

    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
