<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
/** 
*   @SWG\Definition(
*   definition="Seller",
*   required={"name"},
*   @SWG\Property(property="is_marketplace",type="integer", description="If Seller is marketplace admin"),
     *              @SWG\Property(property="marketplace_child",type="integer", description="Marketplace admin parent Id"),
     *              @SWG\Property(property="business_name",type="string", description="Seller Business Name"),
     *              @SWG\Property(property="business_address",type="string", description="Seller Business Address"),
     *              @SWG\Property(property="business_address_2",type="string", description="Seller Business Address Line 2"),
     *              @SWG\Property(property="business_city",type="string", description="Seller Business City"),
     *              @SWG\Property(property="business_state",type="string", description="Seller Business State"),
     *              @SWG\Property(property="business_country",type="string", description="Seller Business Country"),
     *              @SWG\Property(property="siteURL",type="string", format="http://", description="Seller Website"),
* )
*/
class Seller extends Model implements 
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;
    

    protected $fillable = [
        'is_marketplace',
        'marketplace_child',
        'business_name',
        'business_address',
        'business_address_2',
        'business_city',
        'business_state',
        'business_country',
        'business_email',
        'siteURL'
    ];

    public function users()
    {
        return $this->hasOne('App\User', 'seller_id');
    }

    public function delivery_men () {
        return $this->hasMany('App\DeliveryMen', 'added_by');
    }

    public function transactions () {
        return $this->hasMany('App\Transaction');
    }
    public function refundRequest () {
        return $this->hasManyThrough('App\RefundRequest', 'App\Transaction', 'beneficiary_merchant_id', 'transaction_id', 'id', 'id');
    }
    public function delivery_terms () {
        return $this->hasMany('App\DeliveryMen');
    }

    public function account() {
        return $this->hasOne('App\Account', 'user_id');
    }
    public static function getValidationRule () {
        return [
            'is_marketplace' => 'required|max:11',
            'marketplace_child' => 'required|max:11',
            'business_name' => 'required|max:255',
            'business_address' => 'required|max:255',
            'business_address_2' => 'required|max:255',
            'business_city' => 'required|max:255',
            'business_state' => 'required|max: 255',
            'business_country'=> 'required|max:255',
            'business_email'=> 'required|max:255',
            'siteURL'=> 'required',
            'BVN' => 'required|min:11|numeric'
        ];
    }
    public function getFillable () {
        return $this->fillable;
    }
}
