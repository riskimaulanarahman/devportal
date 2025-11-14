<?php

namespace App\Models\Submission\IT;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\Project;
use App\Models\ApproverListReq;
use App\Models\Employee;

class ActiveDirectory extends Model
{
    use HasFactory;

    protected $table = 'request_it_activedirectory';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'employee_id',
        'requestType',
        'accessType',
        'accountType',
        'isVip',
        'validFrom',
        'validTo',
        'depthead_id',
        'approveddoc',
        'bu',
        'pic_empid',
        'remarks',
        'username_temp',
        'password_temp'
    ];

    protected $casts = [
        'validFrom' => 'date',
        'validTo' => 'date',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'requestType',
            'accessType',
            'accountType',
            'validFrom',
            'approveddoc',
            'validTo',
            'pic_empid',
            'username_temp',
            'password_temp'
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

    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
