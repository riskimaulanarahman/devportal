<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Address;
use App\Models\Bank;
use App\Models\Communication;
// use App\Models\Document;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Family;
use App\Models\Language;
use App\Models\Reference;
use App\Models\Size;
use App\Models\Skill;
use App\Models\SocialMedia;
use App\Models\Tax;
// use App\Models\Module\JobApplyment;

class PersonalData extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvjp';

    protected $table = 'personal_data';

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'willing_duty_bound' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function socialMedia()
    {
        return $this->hasMany(SocialMedia::class);
    }

    public function families()
    {
        return $this->hasMany(Family::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function sizes()
    {
        return $this->hasMany(Size::class);
    }

    public function banks()
    {
        return $this->hasMany(Bank::class);
    }

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    // public function documents()
    // {
    //     return $this->hasMany(Document::class);
    // }

    public function references()
    {
        return $this->hasMany(Reference::class);
    }

    // public function jobApplyments()
    // {
    //     return $this->hasMany(JobApplyment::class, 'personal_data_id');
    // }
}
