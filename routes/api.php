<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Http\Controllers\Buyer\BuyerController;

$api = $app->make(Dingo\Api\Routing\Router::class);

/**
 * @SWG\Resource(
 *     apiVersion="1.0.0",
 *     swaggerVersion="3.0",
 *     resourcePath="/api",
 *     basePath="http://localhost:8000"
 * )
 */

$api->version('v1', function ($api) {

     $api->get('/', [
            'uses' => 'App\Http\Controllers\APIController@getIndex',
            'as' => 'api.index'
        ]);

    //marketplace, seller, deliverymen and customer login point
    $api->post('/{role}/login', [
        'as' => 'api.user.login',
        'uses' => 'App\Http\Controllers\Auth\AuthController@postLogin',
    ]);

    //Change password route
    $api->post('/{role}/changePassword', [
        'as' => 'api.user.change.password',
        'uses' => 'App\Http\Controllers\Auth\AuthController@changePassword',
        'middleware' => ['api.auth']
    ]);

    //Forgot password route
    $api->post('/{role}/forgot', [
        'as' => 'api.user.forgot',
        'uses' => 'App\Http\Controllers\Auth\ForgotPasswordController@getResetToken',
    ]);

    //Reset Forgot password route
    $api->post('/{role}/forgot/reset', [
        'as' => 'api.user.reset',
        'uses' => 'App\Http\Controllers\Auth\ForgotPasswordController@reset',
    ]);



    //mailTesting endpoint Uncomment for mail test
    // $api->get('/mail', [
    //     'uses' => 'App\Http\Controllers\OrderController@viewMail'
    // ]);

    // Libreary endpoint Uncomment for  test
    $api->post('/smsCallback', [
        'uses' => 'App\Http\Controllers\SMSController@smsCallback'
    ]);
    
    $api->post('/testVerify', [
        'uses' => 'App\Http\Controllers\SMSController@verifiedTest'
    ]);
    
    $api->group([
        'middleware' => 'api.auth',
        'prefix' => 'auth'
    ], function ($api) {
        $api->get('/', [
            'uses' => 'App\Http\Controllers\APIController@getIndex',
            'as' => 'api.index'
        ]);
        $api->get('/{role}/user', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@getUser',
            'as' => 'api.auth.user'
        ]);
        $api->patch('/{role}/refresh', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@patchRefresh',
            'as' => 'api.auth.refresh'
        ]);
        $api->delete('/{role}/invalidate', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@deleteInvalidate',
            'as' => 'api.auth.invalidate'
        ]);
    });

    $api->group([
        'prefix' => 'customer'
    ], function($api) {

        //route does not require authentication
        $api->post('/register', [
            'uses' => 'App\Http\Controllers\BuyerController@register'
        ]);

        $api->get('/confirmation/{token}', [
            'uses' => 'App\Http\Controllers\BuyerController@confirmBuyer'
        ]);

        
        //following routes require authentication
        $api->group([
            'middleware' => ['api.auth']
        ], function($api) {

            //return customer details
            $api->get('/', [
                'uses' => 'App\Http\Controllers\BuyerController@getCustomerDetails'
            ]);
            
            //update customer details
            $api->put('/update', [
                'uses' => 'App\Http\Controllers\BuyerController@updateCustomer'
            ]);

            
            //get all orders associated with customer
            $api->get('/orders', [
                'uses' => 'App\Http\Controllers\BuyerController@orders'
            ]);

            $api->group(['prefix' => 'order'], function($api){

                $api->post('/', [
                    'uses' => 'App\Http\Controllers\BuyerController@order'
                ]);

                $api->post('/purchase', [
                    'uses' => 'App\Http\Controllers\BuyerController@purchase'
                ]);

                $api->get('/disputes', [
                    'uses' => 'App\Http\Controllers\BuyerController@disputes'
                ]);
                
                $api->group(['prefix' => 'dispute'], function($api){
                    $api->post('/', [
                        'uses' => 'App\Http\Controllers\BuyerController@dispute'
                    ]);

                    $api->post('/raise', [
                        'uses' => 'App\Http\Controllers\BuyerController@raiseDispute'
                    ]);
                    $api->post('/cancel', [
                        'uses' => 'App\Http\Controllers\BuyerController@cancelDispute'
                    ]);
                });
            });
        });
    });


    // DeliveryMan Endpoints
    $api->group([
        'prefix' => 'deliveryman',
        'middleware' => ['api.auth']
    ], function($api) {
        $api->post('/create', [
            'uses' => 'App\Http\Controllers\DeliveryManController@create'
        ]);
        $api->put('/update/{id}', [
            'uses' => 'App\Http\Controllers\DeliveryManController@update'
        ]);
        $api->get('/view', [
            'uses' => 'App\Http\Controllers\DeliveryManController@view'
        ]);
        $api->post('/viewDetails', [
            'uses' => 'App\Http\Controllers\DeliveryManController@viewDetails'
        ]);
        $api->delete('/delete/{id}', [
            'uses' => 'App\Http\Controllers\DeliveryManController@delete'
        ]);
    });

    //Escrow Transaction Endpoints
    // Please Note that the escrow is not authenticated
    // Uncomment the middleware if there is need for authenticating all endpoints under the escrow

    $api->group([
        'prefix' => 'escrow',
        // 'middleware' => ['api.auth']
    ], function($api) {

        $api->post('/account/create', [
            'uses' => 'App\Http\Controllers\EscrowController@payoutDetails'
        ]);
        // $api->post('/paySeller', [
        //     'uses' => 'App\Http\Controllers\EscrowController@paySeller'
        // ]);
        // $api->post('/refund', [
        //     'uses' => 'App\Http\Controllers\EscrowController@refund'
        // ]);
    });

    //Order or Transaction Endpoints
    $api->group([
        'prefix' => 'order',
    ], function($api) {
        $api->get('/allStatus', [
            'uses' => 'App\Http\Controllers\OrderController@allStatus'
        ]);
        //should not be authenticated
        $api->get('/payment/callback', [
            'uses' => 'App\Http\Controllers\OrderController@orderPaymentCallback'
        ]);
            //Order or Transaction Endpoints
        $api->group([
            'middleware' => ['api.auth']
        ], function($api) {
            $api->post('/create', [
                'uses' => 'App\Http\Controllers\OrderController@create'
            ]);
            $api->post('/pay', [
                'uses' => 'App\Http\Controllers\OrderController@orderPayment'
            ]);
            $api->post('/payment/verify', [
                'uses' => 'App\Http\Controllers\OrderController@orderVerifyPayment'
            ]);
            // $api->get('/payment/callback', [
            //     'uses' => 'App\Http\Controllers\OrderController@orderPaymentCallback'
            // ]);
            $api->post('/setStatus', [
                'uses' => 'App\Http\Controllers\OrderController@setTransactionStatus'
            ]);
            $api->post('/setDelivery', [
                'uses' => 'App\Http\Controllers\OrderController@setDeliveryStatus'
            ]);
            $api->put('/update', [
                'uses' => 'App\Http\Controllers\OrderController@update'
            ]);
            $api->post('/assign', [
                'uses' => 'App\Http\Controllers\OrderController@assign'
            ]);
            $api->post('/view', [
                'uses' => 'App\Http\Controllers\OrderController@getTransaction'
            ]);
        });
    });


    $api->group([
        'prefix' => 'deliverymantype'
    ], function($api) {
        $api->post('/create', [
            'uses' => 'App\Http\Controllers\DeliveryManController@createDeliveryManType'
        ]);
        $api->put('/update/{id}', [
            'uses' => 'App\Http\Controllers\DeliveryManController@editDeliveryManType'
        ]);
        $api->get('/view', [
            'uses' => 'App\Http\Controllers\DeliveryManController@viewDeliveryManType'
        ]);
        $api->delete('/delete/{id}', [
            'uses' => 'App\Http\Controllers\DeliveryManController@deleteDeliveryManType'
        ]);
    });

    $api->group([
        'prefix' => 'seller'
    ], function($api) {

        //route does not require authentication
        $api->post('/register', [
            'uses' => 'App\Http\Controllers\SellerController@register'
        ]);

        //route does not require authentication
        $api->get('/confirmation/{token}', [
            'uses' => 'App\Http\Controllers\SellerController@confirmSeller'
        ]);

        //route to reset and sent reset token
        $api->post('/reset', [
            'uses' => 'App\Http\Controllers\SellerController@reset'
        ]);

        //route to reset and sent reset token
        $api->post('/reset/new', [
            'uses' => 'App\Http\Controllers\SellerController@resetNew'
        ]);

        //route to get terms
        $api->get('/terms', [
            'uses' => 'App\Http\Controllers\SellerController@getTerms'
        ]);

        $api->group(['prefix' => 'term'], function($api){
            $api->post('/create', [
                'uses' => 'App\Http\Controllers\SellerController@createTerm'
            ]);

            $api->put('/edit/{termId}', [
                'uses' => 'App\Http\Controllers\SellerController@editTerm'
            ]);
            $api->post('/cancel', [
                'uses' => 'App\Http\Controllers\SellerController@cancelDispute'
            ]);
        });

        //following routes require authentication
        $api->group([
            'middleware' => ['api.auth']
        ], function($api) {

            //return seller details
            $api->get('/account', [
                'uses' => 'App\Http\Controllers\SellerController@getSellerAccountDetails'
            ]);
            //return seller details
            $api->get('/', [
                'uses' => 'App\Http\Controllers\SellerController@getSellerDetails'
            ]);

            //update seller details
            $api->put('/business', [
                'uses' => 'App\Http\Controllers\SellerController@update_business'
            ]);

             //update seller details
             $api->put('/account/update', [
                'uses' => 'App\Http\Controllers\SellerController@updateSellerAccount'
            ]);
            
            //verify seller bvn
            $api->post('/verify', [
                'uses' => 'App\Http\Controllers\SellerController@getVerified'
            ]);
            
            
            //get all orders associated with seller
            $api->get('/orders', [
                'uses' => 'App\Http\Controllers\SellerController@orders'
            ]);
            
            //get all deliverymen registered by seller
            $api->get('/deliverymen', [
                'uses' => 'App\Http\Controllers\SellerController@getDeliveryMen'
            ]);

            $api->group(['prefix' => 'order'], function($api){

                $api->post('/', [
                    'uses' => 'App\Http\Controllers\SellerController@order'
                ]);

                $api->post('/assign', [
                    'uses' => 'App\Http\Controllers\SellerController@setDeliveryMan'
                ]);

                $api->get('/disputes', [
                    'uses' => 'App\Http\Controllers\SellerController@disputes'
                ]);
                
                $api->group(['prefix' => 'dispute'], function($api){
                    $api->post('/', [
                        'uses' => 'App\Http\Controllers\SellerController@dispute'
                    ]);

                    $api->post('/raise', [
                        'uses' => 'App\Http\Controllers\SellerController@raiseDispute'
                    ]);
                    $api->post('/cancel', [
                        'uses' => 'App\Http\Controllers\SellerController@cancelDispute'
                    ]);
                });
            });

            $api->group(['prefix' => 'payout'], function($api){

                $api->post('/create', [
                    'uses' => 'App\Http\Controllers\SellerController@payOutCreate'
                ]);

                $api->get('/view', [
                    'uses' => 'App\Http\Controllers\SellerController@payOutView'
                ]);

                $api->delete('/delete/{id}', [
                    'uses' => 'App\Http\Controllers\SellerController@payOutDelete'
                ]);
            });
        });
    });
});
