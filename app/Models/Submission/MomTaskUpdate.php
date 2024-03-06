<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomTaskUpdate extends Model
{
    use HasFactory;

    protected $table = 'request_momTaskUpdate';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'task_id' => 'integer',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, []);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

}
