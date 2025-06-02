<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Code;
use App\Models\User;
use App\Models\Employee;

class MemorandumHis extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum_his';

    protected $guarded = ['id'];

    protected $fillable = [
        'req_id',
        'sequence',
        'module_id',
        'startContract',
        'endContract',        
        'remarks',
        'code_id',
        'superiorName',
        'requestStatus',
        'user_id',
        'sysid'
    ];
    protected $casts = [      
        'code_id' => 'integer',
        'user_id' => 'integer', 
        'sysid' => 'string',
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

    public function Memorandum()
    {
        return $this->belongsTo('App\Models\Submission\Memorandum', 'sys_id', 'sys_id');
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
    public function code()
    {
        return $this->belongsTo(Code::class, 'code_id');
    }
}
