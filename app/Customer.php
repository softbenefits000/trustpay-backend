<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
/** 
*   @SWG\Definition(
*   definition="Customer",
*   required={"name"},
*                   @SWG\Property(property="firstname",type="string", description="Customer Firstname"),
     *              @SWG\Property(property="lastname",type="string", description="Customer Lastname"),
     *              @SWG\Property(property="phone_number",type="string", description="Customer Phone Number"),
     *              @SWG\Property(property="email_address",type="string", description="Customer Email Address"),
* )
*/
class Customer extends Model implements 
    CanResetPasswordContract,
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    protected $fillable = [
        'firstname',
        'lastname',
        'phone_number',
        'email',
        'password',
        'PIN',
        'status',
        'ip_address',
        'device_type',
        'device_version',
        'confirmation_token',
    ];

    protected $hidden = [
        'confirmation_token',
        'password',
        'PIN',
        'ip_address',
        'device_type',
        'device_version',
        'confirmation_token',
    ];

    public function getFillable () {
        return $this->fillable;
    }

    public function transactions () {
        return $this->hasMany('App\Transaction');
    }

    public function refundRequest () {
        return $this->hasManyThrough('App\RefundRequest', 'App\Transaction', 'customer_id', 'transaction_id', 'id', 'id');
    }

    public static function loginValidation () {
        return 
        [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ];
    }

    public static function getValidation () {
        return 
        [
            'firstname' => 'required|min:3',
            'lastname' => 'required|min:3',
            'phone_number' => 'required|numeric',
            'email' => 'required|email'
        ];
    }

    public static function getPasswordValidation () {
        return 
        [
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ];
    }

    public static function getRegisterValidation () {
        return 
        [
            'firstname' => 'required|min:3',
            'lastname' => 'required|min:3',
            'phone_number' => 'required|numeric|unique:customers',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required', 
            'PIN' => 'required|min:4|numeric',
            'ip_address' => 'required|ip',
            'device_type' => 'required',
            'device_version' => 'required'
        ];
    }

        /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
