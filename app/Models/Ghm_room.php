<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ghm_room extends Model
{
    use HasFactory;

    protected $table = 'request_ghm_room';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
