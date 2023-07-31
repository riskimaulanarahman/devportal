<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApproverListHistory extends Model
{
    use HasFactory;

    protected $table = 'tbl_approverListHistory';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'module_id' => 'integer',
        'req_id' => 'integer',
        'approvalDate' => 'date',
    ];
    
}
