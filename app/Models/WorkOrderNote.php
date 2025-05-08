<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderNote extends Model
{
    protected $fillable = ['work_order_id', 'note', 'created_by'];

    public $timestamps = false;

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
