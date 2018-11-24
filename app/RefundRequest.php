<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class RefundRequest extends Model implements 
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable; 
    protected $fillable = [
        'transaction_id',
        'status',
        'reason'
    ];

    public function transactions () {
        return $this->belongsTo('App\Transaction', 'transaction_id');
    }
}
