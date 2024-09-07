<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi dengan tabel core_values
    public function coreValue()
    {
        return $this->belongsTo(CoreValue::class);
    }

    // Relasi dengan tabel employees
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
}
