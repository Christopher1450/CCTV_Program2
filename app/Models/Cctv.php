<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cctv extends Model
{
    protected $fillable = ['branch_id', 'cctv_position_id','name','ipCamAccount','created_by', 'updated_by'];

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
    public function ipCamAccount()
    {
        return $this->belongsTo(IpCamAccount::class, 'ip_cam_account_id');
    }

    public function type()
    {
        return $this->belongsTo(Branch::class, 'cctv_type');
    }
    public function activeWorkOrders()
    {
        return $this->hasMany(WorkOrder::class)->whereIn('status', [1, 2]); // pending or on progress
    }
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
