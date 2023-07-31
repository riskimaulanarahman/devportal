<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'tbl_employee';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $with = ['department','designation','location','level','company'];

    public function department()
    {
        return $this->belongsTo('App\Models\Department','department_id');
    }

    public function designation()
    {
        return $this->belongsTo('App\Models\Designation','designation_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\Location','location_id');
    }

    public function level()
    {
        return $this->belongsTo('App\Models\Level','level_id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','employee_id');
    }

}
