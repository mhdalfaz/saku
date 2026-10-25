<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saving extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'custodian_id',
        'status',
        'date',
        'description',
    ];

    protected $appends = [
        'total_deposit',
        'total_withdrawal',
        'total_profit',
        'profit_retained',
        'balance',
    ];

    public function getTotalDepositAttribute()
    {
        return (int) $this->transactions()->where('type', 'deposit')->sum('amount');
    }

    public function getTotalWithdrawalAttribute()
    {
        return (int) $this->transactions()->where('type', 'withdrawal')->sum('amount');
    }

    public function getTotalProfitAttribute()
    {
        return (int) $this->transactions()->where('type', 'profit')->sum('amount');
    }

    public function getProfitRetainedAttribute()
    {
        return (int) $this->transactions()->where('type', 'profit')->where('retained', true)->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->total_deposit + $this->profit_retained - $this->total_withdrawal;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function custodian()
    {
        return $this->belongsTo(Custodian::class);
    }

    public function transactions()
    {
        return $this->hasMany(SavingTransaction::class);
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
