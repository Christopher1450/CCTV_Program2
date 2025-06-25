<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'branch_id',
        'cctv_id',
        'title',
        'description',
        'problem_type',
        'notes',
        'status',
        'takenBy'
    ];

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
    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
