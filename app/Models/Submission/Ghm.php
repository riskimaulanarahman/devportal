<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Employee;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\Ghm_room;

class Ghm extends Model
{
    use HasFactory;

    protected $table = 'request_ghm';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'employee_id',
        'code_id',
        'requestStatus',
        'bu',
        'sector',
        'ghm_room_id',
        'employee',
        'description',
        'text',
        'guest',
        // 'approveddoc',
        'family',
        'startDate',
        'endDate'
    ];
    protected $dates = [
        'startDate',
        'endDate',
    ];

    protected $casts = [
        'ghm_room_id' => 'integer',
        'employee' => 'array',
        'guest' => 'array',
        'family' => 'array',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['codeno']);
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
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

    public function ghm_room()
    {
        return $this->belongsTo(Ghm_room::class,'gh_room_id');
    }

}
