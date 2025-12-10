<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\SpklDetail;
use App\Models\Employee;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\CategoryForm;

class Spkl extends Model
{
    use HasFactory;

    protected $table = 'request_spkl';

    protected $guarded = ['id'];

    protected $fillable = [
        'employee_id',
        'work_date',        
        'requestStatus',
        'DeptHead',
        'tms',
        'category_id',
        'Superior',
        'remarks',
        'user_id',
        'module_id',
        'bu',
        'created_at',
        'updated_at'
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'submitDate',
            'submissionDate',
            'DeptHead',
            'Superior',
        ]);
        return $fillable;
    }
    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function approverHistory()
    {
        return $this->hasMany(ApproverListHistory::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

    public function spkl_detail()
    {
        return $this->hasOne(SpklDetail::class, 'req_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(CategoryForm::class,'category_id');
    }
}
