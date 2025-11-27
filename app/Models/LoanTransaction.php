<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanTransaction extends Model
{
    protected $fillable = [
        'loan_id',
        'amount',
        'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
