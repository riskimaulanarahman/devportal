<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi dengan tabel questions
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Relasi dengan tabel employees
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
}
