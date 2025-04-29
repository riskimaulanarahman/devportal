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

class MemorandumReq extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum';

    protected $guarded = ['id'];

    protected $fillable = [
            'requestStatus',
            'bu',
            'pa',
            // 'sector',
            // 'prStatus',
            'user_id',
            'employee_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'approveddoc',
            // 'special_requirements',
            // 'special_requirements_others',
        ]);
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
        return $this->hasMany(MemorandumHis::class, 'sys_id', 'sys_id');
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
