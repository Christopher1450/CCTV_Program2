<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = ['branch_id', 'cctv_id', 'title', 'description'];
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function notes()
    {
        return $this->hasMany(WorkOrderNote::class);
    }
}
