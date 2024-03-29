<?php

$style = [
    /* Layout ------------------------------ */

    'body' => 'margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;',
    'email-wrapper' => 'width: 100%; margin: 0; padding: 0; background-color: #002060;',

    /* Masthead ----------------------- */

    'email-masthead' => 'padding: 25px 0; text-align: center;',
    'email-masthead_name' => 'font-size: 16px; font-weight: 400; color: #FFFFFF; text-decoration: none; text-shadow: 0 1px 0 white;',

    'email-body' => 'width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;',
    'email-body_inner' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0;',
    'email-body_cell' => 'padding: 35px;',

    'email-footer' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0; text-align: center;',
    'email-footer_cell' => 'color: #ffffff; padding: 35px; text-align: center;',

    /* Body ------------------------------ */

    'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
    'body_sub' => 'margin-top: 25px; padding-top: 25px; border-top: 1px solid #EDEFF2;',

    /* Type ------------------------------ */

    'anchor' => 'color: #231f20;',
    'header-1' => 'margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;',
    'paragraph' => 'margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em;',
    'paragraph-sub' => 'margin-top: 0; color: #74787E; font-size: 12px; line-height: 1.5em;',
    'paragraph-center' => 'text-align: center;',

    /* Buttons ------------------------------ */

    'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                 background-color: #3869D4; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',

    'button--green' => 'background-color: #22BC66;',
    'button--red' => 'background-color: #dc4d2f;',
    'button--blue' => 'background-color: #231f20;color:#002060;',
];
?>

<?php $fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;'; ?>

<body style="{{ $style['body'] }}">
    <table width="80%" style="margin: 0 auto" cellpadding="0" cellspacing="0">
        <tr>
            <td style="{{ $style['email-wrapper'] }}" align="center">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <!-- Logo -->
                    <tr>
                        <td style="{{ $style['email-masthead'] }}">
                            <a style="{{ $fontFamily }} {{ $style['email-masthead_name'] }}" href="{{ url('/') }}" target="_blank">
                                {{--  <img src="{{asset("user-assets/img/logo.png")}}" alt="poco a poco">  --}}
                                Stanbic TrustPay Escrow Deposit
                            </a>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td style="{{ $style['email-body'] }}" width="100%">
                            <table style="{{ $style['email-body_inner'] }}" align="left" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">
                                        <!-- Greeting -->
                                        <h1 style="{{ $style['header-1'] }}">
                                            Dear , {{$firstname}}
                                        </h1>

                                        <!-- Intro -->

                                            <p style="{{ $style['paragraph'] }}">
                                                Thank you for using the Stanbic TrustPay Service.
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                We have received {{$amount}} as deposit for full payment for order No. {{$order_code}}.  
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                This money will be paid to your account once the Buyer accepts that the item(s) were delivered in/ are in good condition at point of exchange between the Buyer and the delivery man. If the order is rejected, the funds will be sent back to the Buyer.    
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                Follow these 5 simple steps claim your money : 
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                <ol>
                                                    <li style="{{ $style['paragraph-sub'] }}">
                                                    	At point of exchange of the item(s), the Buyer shall give the delivery man his/ her 6-digit code
                                                    </li>
                                                    <li style="{{ $style['paragraph-sub'] }}">
                                                        Delivery man enters the code in their phone and Buyer gets notification on their phone to accept or reject the item
                                                    </li>
                                                    <li style="{{ $style['paragraph-sub'] }}">
                                                        If Buyer accepts the item, the funds will be released by us and paid to your account 
                                                    </li>
                                                    <li style="{{ $style['paragraph-sub'] }}">
                                                        If Buyer rejects, your delivery man will get a notification and is expected to confirm that s/he is still in possession of the item(s). Upon confirmation, the Buyer’s funds will be paid back.
                                                    </li>
                                                    <li style="{{ $style['paragraph-sub'] }}">
                                                        If your delivery man does not confirm possession of the item(s) after 24 hours, the Buyer’s funds will be paid back shortly afterwards.
                                                    </li>
                                                </ol> 
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                                For further clarification, please email support@trustpayonline.com or call us on 0700TRUSTPAY.
                                            </p>

                                           

                                            <p style="{{ $style['paragraph'] }}">
                                                Thank you again for your patronage.
                                            </p>

                                            <p style="{{ $style['paragraph'] }}">
                                               For any assistance please call 0700 TRUSTPAY or email help@trustpayonline.com
                                            </p>


                                        <!-- Salutation -->
                                        <p style="{{ $style['paragraph'] }}">
                                            Best Regards,<br>StanbicIBTC TrustPay Team
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td>
                            <table style="{{ $style['email-footer'] }}" align="center" width="570" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-footer_cell'] }}">
                                        <p style="{{ $style['paragraph-sub'] }}">
                                            &copy; {{ date('Y') }}
                                            <a style="{{ $style['anchor'] }}" href="{{ url('/') }}" target="_blank">TrustPay</a>.
                                            All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
