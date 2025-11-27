<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'borrower_id',
        'total_amount',
        'date',
        'due_date',
        'description',
    ];

    protected $appends = ['paid', 'remaining', 'percent'];

    public function getPaidAttribute()
    {
        return $this->transactions()->sum('amount');
    }

    public function getRemainingAttribute()
    {
        return $this->total_amount - $this->paid;
    }

    public function getPercentAttribute()
    {
        if ($this->total_amount == 0) {
            return 0;
        }
        return round(($this->paid / $this->total_amount) * 100, 2);
    }

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
