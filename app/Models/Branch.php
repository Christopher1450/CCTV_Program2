<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
   protected $fillable = [
    'internet_provider_id',
    'internet_customer_id',
    'cctv_type',
    'ip_cam_account_id',
    'create_by',
    'updated_by'
    ];

    public function provider()
    {
        return $this->belongsTo(InternetProvider::class, 'internet_provider_id');
    }

    public function ipCamAccount()
    {
        return $this->belongsTo(IpCamAccount::class, 'ip_cam_account_id');
    }
    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }
    public function WorkOrder()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function cctv_type()
    {
        return $this->belongsTo(Branch::class, 'cctv_type');
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
