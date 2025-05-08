<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['branch_id', 'log','created_at', 'created_by'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
