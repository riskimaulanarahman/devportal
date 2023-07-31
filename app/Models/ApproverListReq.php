<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApproverListReq extends Model
{
    use HasFactory;

    protected $table = 'tbl_approverListReq';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'approver_id' => 'integer',
        'approvalAction' => 'integer',
        'module_id' => 'integer',
        'req_id' => 'integer',
        'approvalDate' => 'date',
    ];

    public function approvaluser()
    {
        return $this->belongsTo('App\Models\Approvaluser','approver_id');
    }

}
