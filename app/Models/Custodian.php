<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Custodian extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    protected $appends = [
        'avatar',
    ];

    public function getAvatarAttribute()
    {
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=random&size=128";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savings()
    {
        return $this->hasMany(Saving::class);
    }
}
