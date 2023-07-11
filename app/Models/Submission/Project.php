<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Project extends Model
{
    use HasFactory;

    protected $table = 'request_project';

    protected $guarded = ['id'];

    protected $fillable = [
        'nameSystem',
        'description',
        'descOldSystem',
        'descNewSystem',
        'purpose',
        'expecteddate',
        'requestStatus',
        'user_id',
        'priority',
        'progress',
        'projectStatus'
    ];

    protected $casts = [
        'expecteddate' => 'date',
    ];

    public static function getFillableColumns()
    {
        // return (new static)->fillable;
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['priority','progress','projectStatus']);
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
