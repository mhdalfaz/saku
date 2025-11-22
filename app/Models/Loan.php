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

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }
}
