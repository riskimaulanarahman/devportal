<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Jdi extends Model
{
    use HasFactory;

    protected $table = 'request_jdi';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'noRegistration',
        'title',
        'submitDate',
        'pencetuside_id',
        'bu',
        'sector',
        'department_id',
        'objective',
        'ranking',
        'anggota1_id',
        'anggota2_id',
        'depthead_id',
        'htk',
        'perbaikan',
        'benefit',
        'category_id',
        'isSaving',
        'isNotWasteful',
        'reasonNotWasteful',
        'savingFormula',
        'totalSaving',
        'isRollout',
        'sevenWaste',
        'status_jdi',
        'savingInfo'
    ];

    protected $casts = [
        'submitDate' => 'date',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'noRegistration',
            'submitDate',
            'objective',
            'ranking',
            'isRollout',
            'savingInfo',
            'category_id',
            'reasonNotWasteful',
            'savingFormula',
            'totalSaving',
            'anggota1_id',
            'anggota2_id',
            'status_jdi',
            'sevenWaste',
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

}
