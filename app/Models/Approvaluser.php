<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvaluser extends Model
{
    use HasFactory;

    protected $table = 'tbl_approver';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $casts = [
        'employee_id' => 'integer',
        'sequence' => 'integer',
        'approvaltype_id' => 'integer',
        'isFinal' => 'boolean',
        'isActive' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }
    
}
