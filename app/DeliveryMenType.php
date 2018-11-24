<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class DeliveryMenType extends Model implements 
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = "delivery_men_types";
    protected $fillable = [
        'title',
        'description'
    ];

    public function delivery_men () {
        return $this->hasMany('App\DeliveryMan');
    }

    public static function getValidationRule () {
        return [
            'title' => 'required|min:4',
            'description' => 'required|min:5'
        ];
    }

    public function getFillable () {
        return $this->fillable;
    }


}
