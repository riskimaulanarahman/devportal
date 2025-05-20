<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\MemorandumHis;
use App\Models\Employee;
use App\Models\ApproverListHistory;

class Memorandum extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum';

    protected $guarded = ['id'];

    protected $fillable = [
        'code_id',
        'requestStatus',
        'bu',
        'sysid',
        'parentID',
        'startContract',
        'endContract',
        'user_id',
        'employee_id',        
        'sequence',
        'remarks',
        'created_at',
        'updated_at'
        ];
        
    protected $casts = [
            'user_id' => 'integer',
            'employee_id' => 'integer',
            // 'sysid' => 'string'
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function request_memorandum_his()
    {
        return $this->hasMany(MemorandumHis::class, 'sysid', 'sysid');
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
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
        return $this->belongsTo(Code::class);
    }

}
