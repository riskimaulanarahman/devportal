<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;

class Legal extends Model
{
    use HasFactory;

    protected $table = 'request_legal';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'referenceNo',
        'businessGroup',
        'bu',
        'sector',
        'formGroup',
        'formType',
        'requestType',
        'submissionDate',
        'dateOfDocument',
        'countersigningParty',
        'businessType',
        'titleOfDocument',
        'financialAmount',
        'purpose',        
        'rfcNumber',                
        'employee_id', 
        'skNumber',
        'sk',   
    ];

    protected $casts = [
        // 'submitDate' => 'date',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'submitDate',
            'sk',
            'skNumber',
            'submissionDate',
            'additional_approver',
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

    public function approverHistory()
    {
        return $this->hasMany(ApproverListHistory::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

}
