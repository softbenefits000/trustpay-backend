<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Account extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;
    protected $fillable = [
        'bank_name',
        'bank_code',
        'bank_account_name',
        'bank_account_number',
        'transfer_recipient_id'
    ];

    public function seller() {
        return $this->belongsTo('App\Seller', 'user_id');
    }

    public static function getValidationRules () {
        return [
            'bank_name' => 'required',
            'bank_code' => 'required|numeric',
            'bank_account_name' => 'required|max:100',
            'bank_account_number' => 'required|numeric',
        ];
    }
}
