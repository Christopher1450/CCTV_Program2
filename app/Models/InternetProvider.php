<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetProvider extends Model
{
    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
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
