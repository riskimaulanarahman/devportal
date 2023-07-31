<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SideMenu extends Model
{
    use HasFactory;

    protected $table = 'side_menus';
    
    protected $guarded = ['id'];

    // protected $fillable = [
    //     'title', 'route', 'icon_id', 'parent_id', 'sequence_id', 'is_secondary_menu', 'is_active', 'is_admin', 'is_parent', 'must_full_title'
    // ];

    protected $casts = [
        'icon_id' => 'integer',
        'parent_id' => 'integer',
        'modules' => 'integer',
        'sequence_id' => 'integer',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'is_parent' => 'boolean',
        'is_secondary_menu' => 'boolean',
        'must_full_title' => 'boolean',
    ];
}