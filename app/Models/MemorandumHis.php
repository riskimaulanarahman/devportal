<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Submission\MemorandumReq;

class MemorandumHis extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum_his';

    protected $guarded = ['id'];
    protected $casts = [
        'req_id' => 'integer',
        'module_id' => 'integer',
        'cs' => 'integer',
        'startContract' => 'date',
        'endContract' => 'date',
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

    public function MemorandumReq()
    {
        return $this->belongsTo('App\Models\Submission\MemorandumReq', 'sys_id', 'sys_id');
    }
}
