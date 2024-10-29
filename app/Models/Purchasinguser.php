<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Purchasinguser extends Model
{
    use HasFactory;

    protected $table = 'reference.tbl_purchasing_group';

    protected $guarded = ['id'];

    // protected $casts = [
    // ];

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee','employee_id');
    }

    // public static function getFillableColumns()
    // {
    //     $fillable = (new static)->fillable;
    //     $fillable = array_diff($fillable, [
    //     ]);
    //     return $fillable;
    // }

    // public static function getTableName()
    // {
    //     return (new static)->getTable();
    // }
    

}
