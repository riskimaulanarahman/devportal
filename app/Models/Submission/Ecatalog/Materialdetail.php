<?php

namespace App\Models\Submission\Ecatalog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Materialdetail extends Model
{
    use HasFactory;

    protected $table = 'request_material_detail';

    protected $guarded = ['id'];
    protected $casts = [
        'req_id' => 'integer',
        'catalog_id' => 'integer',
        'required' => 'integer',
        'available' => 'integer',
        'order' => 'integer',
        'unit_price' => 'integer',
        'amount' => 'integer',
    ];
    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, []);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public function catalog()
    {
        return $this->belongsTo('App\Models\Ecatalog','catalog_id','id');
    }

}
