<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employee.tbl_employee';
    
    protected $guarded = ['id'];

    protected $casts = [
        'company_id' => 'integer',
        'department_id' => 'integer',
        'designation_id' => 'integer',
        'location_id' => 'integer',
        'level_id' => 'integer',
        'isActive' => 'integer',
        'isTerminate' => 'integer',
        'isNotHC' => 'integer',
    ];
    
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
        return $this->belongsTo('App\Models\Company','company_id');
    }

    // public function users()
    // {
    //     return $this->belongsTo('App\Models\User','LoginName','username');
    // }

    // Relasi dengan tabel answers
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // Relasi dengan tabel scores
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

}
