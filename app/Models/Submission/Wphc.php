<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\WphcDetail;
use App\Models\Employee;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\CategoryForm;

class Wphc extends Model
{
    use HasFactory;

    protected $table = 'request_wphc';

    protected $guarded = ['id'];

    protected $fillable = [
        'requestStatus',
        'user_id',
        // 'employee_id',
        'bu',
        'sector',
        'Superior',
        'DeptHead',
        'employee_id',
        'category_id',
        'created_at',
        'updated_at'
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'submitDate',
            'submissionDate',
            'Superior',
            'DeptHead',
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

    public function wphc_detail()
    {
        return $this->hasMany(WphcDetail::class, 'req_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(CategoryForm::class,'category_id');
    }

}
