<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Submission\Spkl;
use App\Models\Employee;

class SpklDetail extends Model
{
    use HasFactory;

    protected $table = 'request_spkl_detail';

    protected $guarded = ['id'];

    public static function gitFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, []);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public function Spkl()
    {
        return $this->belongsTo(Spkl::class, 'req_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
