<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function coreValue()
    {
        return $this->belongsTo(CoreValue::class);
    }

    // Relasi dengan tabel answers
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    
}
