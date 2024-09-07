<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreValue extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi dengan tabel questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Relasi dengan tabel scores
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
    
}
