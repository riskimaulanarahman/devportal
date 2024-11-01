<?php

namespace App\Models\Submission\Ecatalog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;

class MaterialReq extends Model
{
    use HasFactory;

    protected $table = 'request_material';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'prStatus',
        'bu',
        'special_requirements',
        'special_requirements_others',
        'approveddoc',
    ];

    protected $casts = [
        'special_requirements' => 'integer',
        'user_id' => 'integer',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'approveddoc',
            'special_requirements',
            'special_requirements_others',
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
