<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;


/** 
*   @SWG\Definition(
*   definition="Transaction",
*   required={"name"},
*   @SWG\Property(property="type",type="integer",format="int11", description="Transaction Type"),
 *              @SWG\Property(property="customer_id",type="integer",format="int11", description="Customer Unique Id"),
 *              @SWG\Property(property="beneficiary_merchant_id",type="integer",format="int11", description="Beneficiary Merchant Unique Id"),
 *              @SWG\Property(property="payment_reference",type="string", description="Payment Reference"),
 *              @SWG\Property(property="amount_payed",type="integer",format="int11", description="Amount Payed"),
 *              @SWG\Property(property="response_code",type="integer",format="int11", description="Respose Code"),
 *              @SWG\Property(property="response_description",type="string", description="Response Description"),
 *              @SWG\Property(property="status",type="integer",format="int11", description="Status Code"),
 *              @SWG\Property(property="product_delivery_status",type="integer",format="int64", description="Delivery Status"),
 *              @SWG\Property(property="transaction_date",type="date", format="yyyy-mm-dd", description="Transaction Date"),
 *              @SWG\Property(property="delivery_date",type="date", format="yyyy-mm-dd", description="Delivery Date"),
* )
*/
class Transaction extends Model implements 
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;
    //

    protected $table = "transactions";

    protected $fillable = [
        'type',
        'customer_id',
        'beneficiary_merchant_id',
        'amount_payed',
        'delivery_date',
        'delivery_location',
        'status',
        'order_code',
    ];

    public function customers () {
        return $this->belongsTo('App\Customer', 'customer_id');
    }
    
    public function sellers () {
        return $this->belongsTo('App\Seller', 'beneficiary_merchant_id');
    }
    
    public function user () {
        return $this->belongsTo('App\User', 'beneficiary_merchant_id');
    }
    public function refundRequest () {
        return $this->hasMany('App\RefundRequest');
    }

    public function delivery_men () {
        return $this->belongsTo('App\DeliveryMan', 'delivery_man');
    }

    public static function getValidationRule () {
        return [
        'type' => 'required|numeric|min:1',
        'customer_id' => 'required|numeric|min:1',
        'beneficiary_merchant_id' => 'required|numeric|min:1',
        'delivery_date' => 'required|date',
        'delivery_location' => 'required|string|max:255',
        'amount_payed' => 'required|numeric|min:3'
        ];
    }

    public function status () {
        return $this->belongsTo('App\TransactionStatus', 'status');
    }

}
