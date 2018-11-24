<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class DeliveryMan extends Model implements 
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;
    //
    protected $fillable = [
        'type',
        'business_name',
        'business_email',
        'business_address',
        'business_city',
        'business_state',
        'business_country',
        'business_phone',
        'siteURL',
        'added_by'
    ];

    public function seller () {
        return $this->belongsTo('App\Seller', 'added_by');
    }

    public function delivery_men_type () {
        return $this->belongsTo('App\DeliveryMenType', 'type');
    }
    public function transaction () {
        return $this->hasMany('App\Transaction');
    }
    public function getFillable () {
        return $this->fillable;
    }

    public static function getValidationRules () {
        return [
            'type' => 'required|numeric',
            'business_name' => 'required|min:3',
            'business_email' => 'email|required|unique:delivery_men,business_email',
            'business_address' => 'required|min:5|max:255',
            'business_city' => 'required|min:2',
            'business_state' => 'required|min:2',
            'business_country' => 'required|min:2',
            'business_phone' => 'required|numeric|unique:delivery_men,business_phone',
            'siteURL' => 'required|url|max:255'
            
        ];
    }
}
