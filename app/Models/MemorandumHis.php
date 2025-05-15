<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
// use App\Models\Submission\MemorandumReq;

class MemorandumHis extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum_his';

    protected $guarded = ['id'];

    // protected $fillable = [
    //     'req_id' => 'integer',
    //     'cs' => 'integer',
    //     'module_id' => 'integer',
    //     'employee_idr' => 'integer',
    //     'startContract' => 'date',
    //     'endContract' => 'date',        
    //     'remarks' => 'string',
    // ];
    protected $casts = [
        'req_id' => 'integer',
        'module_id' => 'integer',
        'sequence' => 'integer',
        'startContract' => 'date',
        'endContract' => 'date',        
        'remarks' => 'string',
        'user_id' => 'integer', 
        'sysid' => 'string',
        'employee_idr' => 'integer',
        'requestStatus' => 'integer',
    ];
    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['approveddoc']);        
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
    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function approverHistory()
    {
        return $this->hasMany(ApproverListHistory::class,'req_id');
    }
    public function approver()
    {
        return $this->hasMany(approver::class,'req_id');
    }
    public function approvaltype()
    {
        return $this->hasMany(approvaltype::class,'req_id');
    }
}
