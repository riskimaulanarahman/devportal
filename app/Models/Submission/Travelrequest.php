<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travelrequest extends Model
{
    use HasFactory;

    protected $table = 'tbl_tr';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $casts = [
        'jobfinishdate' => 'date',
    ];

}
 