<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
/** 
*   @SWG\Definition(
*   definition="DeliveryTerm",
*   required={"name"},
*   @SWG\Property(property="user_id",type="integer", format="int11", description="Seller/Merchant Id"),
     *              @SWG\Property(property="body",type="string", description="Delivery Term Details"),
     *              @SWG\Property(property="SLA",type="string", description="SLA Details"),
     *              @SWG\Property(property="logistics_fee",type="string", description="Logistic Fee"),
* )
*/
class DeliveryTerm extends Model implements 
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;
    

    protected $fillable = [
        'user_id',
        'body',
        'SLA',
        'logistics_fee',
    ];

    public function sellers () {
        return $this->belongsTo('App\Seller');
    }

    public static function getValidationRule () {
        return [
            'body'=> 'required',
            'SLA' => 'required',
            'logistics_fee' => 'required|numeric'
        ];
    }

    public function getFillable () {
        return $this->fillable;
    }
}
