<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayPal Environment
    |--------------------------------------------------------------------------
    | "sandbox" talks to the PayPal sandbox environment, "live" to production.
    | The credentials of both environments live in the developer dashboard.
    |
    */

    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Credentials
    |--------------------------------------------------------------------------
    | The client id and secret of the REST app. Leave them empty to keep the
    | checkout closed: the storefront then explains that payments are not
    | available instead of failing with an error.
    |
    */

    'client_id' => env('PAYPAL_CLIENT_ID'),

    'client_secret' => env('PAYPAL_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    | The id of the webhook that is registered in the PayPal dashboard. It is
    | used to verify that incoming webhook calls really come from PayPal.
    |
    */

    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    | The currency PayPal charges the buyer in. Every app is priced in this
    | currency, so a mismatch is rejected before the buyer is sent over.
    |
    */

    'currency' => env('PAYPAL_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | API Base Url
    |--------------------------------------------------------------------------
    | Overrides the endpoint that is derived from the mode. Useful for a local
    | mock of the PayPal API.
    |
    */

    'base_url' => env('PAYPAL_BASE_URL'),

];
