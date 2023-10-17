<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryForm extends Model
{
    use HasFactory;

    protected $table = 'tbl_categoryform';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    public function module()
    {
        return $this->belongsTo('App\Models\Module','module_id');
    }
}
