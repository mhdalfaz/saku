<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    protected $appends = [
        'avatar'
    ];

    public function getAvatarAttribute()
    {
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=random&size=128";
    }
}
