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
*   definition="User",
*   required={"name"},
*   @SWG\Property(property="role_id",type="integer", description="Role Id"),
     *              @SWG\Property(property="firstname",type="string", description="User Firstname"),
     *              @SWG\Property(property="lastname",type="string", description="User Lastname"),
     *              @SWG\Property(property="phone_number",type="string", format="080", description="User Phone Number"),
     *              @SWG\Property(property="email",type="string", description="User Email"),
     *              @SWG\Property(property="password",type="string", description="User Password"),
     *              @SWG\Property(property="PIN",type="string", description="User PIN"),
     *              @SWG\Property(property="BVN",type="string", description="User BVN"),
     *              @SWG\Property(property="ip_address",type="string", description="User IP Address"),
     *              @SWG\Property(property="device_type",type="string", description="User Device Type"),
     *              @SWG\Property(property="device_version",type="string", description="User Device Version"),
     *              @SWG\Property(property="confirmation_token",type="string", description="User Confirmation Token"),
* )
*/

class User extends Model implements
    CanResetPasswordContract,
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'firstname',
        'lastname',
        'phone_number',
        'email',
        'password',
        'PIN',
        'BVN',
        'verified',
        'ip_address',
        'device_type',
        'device_version',
        'confirmation_token'
        
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'PIN',
        'BVN',
        'ip_address',
        'remember_token',
        'device_type',
        'device_version',
        'confirmation_token',

    ];

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

    public function seller() {
        return $this->belongsTo('App\Seller', 'seller_id');
    }
    public function account() {
        return $this->hasMany('App\Account', 'user_id');
    }

    public static function getValidationRules () {
        return [
            // 'business_name' => 'required|min:3|unique:sellers',
            // 'business_address' => 'required|min:3',
            // 'business_address_2' => 'required|date',
            // 'business_city' => 'required',
            // 'business_state' => 'required',
            // 'business_country' => 'required',
            // 'business_email' => 'required|email|unique:sellers', 
            // 'siteURL' => 'required|min:4|active_url',
            'firstname' => 'required|min:4',
            'lastname' => 'required|min:4',
            'phone_number' => 'required|min:11|numeric|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'PIN' => 'required|min:4|numeric|confirmed',
            'PIN_confirmation' => 'required|min:4|numeric',
            'ip_address' => 'required|ip',
            'device_type' => 'required',
            'device_version' => 'required'
            
        ];
    }

    public static function getBusinessValidationRules () {
        return [
            'business_name' => 'required|min:3|unique:sellers',
            'business_address' => 'required|min:3',
            'business_address_2' => 'required|date',
            'business_city' => 'required',
            'business_state' => 'required',
            'business_country' => 'required',
            'business_email' => 'required|email|unique:sellers', 
            'siteURL' => 'required|min:4|active_url',
            'BVN' => 'required|min:11|numeric',
        ];
    }
    public static function getResetValidationRules () {
        return [
            'email' => 'required|email',
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

    public static function getResetInputsValidationRules () {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'token' => 'required'
        ];
    }
}
