<?php

use Illuminate\Http\Request;

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | The application sits behind a proxy in production: a load balancer, a
    | reverse proxy or a tunnel. Without trusting that proxy the application
    | sees the address of the proxy instead of the address of the visitor,
    | which breaks the rate limiters and the secure cookie.
    |
    | Leave this empty when the application is reached directly. Use a comma
    | separated list of addresses or ranges to trust only the proxies you
    | know, for example "10.0.0.1,10.0.0.2". Use "*" only when the
    | application cannot be reached without the proxy, because it lets a
    | visitor fake the forwarded address.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

    /*
    |--------------------------------------------------------------------------
    | Trusted Headers
    |--------------------------------------------------------------------------
    |
    | The headers the proxy uses to pass on the address and the scheme of the
    | original request.
    |
    */

    'headers' => Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB,

];
