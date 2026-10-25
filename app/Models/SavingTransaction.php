<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'saving_id',
        'type',
        'retained',
        'amount',
        'note',
        'date',
    ];

    protected $casts = [
        'retained' => 'boolean',
        'amount' => 'integer',
    ];

    public function parentSaving()
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }
}
