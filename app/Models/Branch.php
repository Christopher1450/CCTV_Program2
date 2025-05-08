<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'internet_provider_id',
        'internet_customer_id',
        'cctv_type',
        'ip_cam_account_id'
    ];
    public function provider()
    {
        return $this->belongsTo(InternetProvider::class, 'internet_provider_id');
    }

    public function ipCamAccount()
    {
        return $this->belongsTo(IpCamAccount::class);
    }

    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }


}
