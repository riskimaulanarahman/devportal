<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stackholders extends Model
{
    use HasFactory;

    protected $table = 'tbl_stackholders';

    protected $guarded = ['id'];
    
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee','employee_id');
    }
}
