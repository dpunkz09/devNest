<?php

namespace App\Services\PayPal;

use App\Exceptions\PayPalException;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PayPalClient
{
    /**
     * The hosts PayPal signs its webhooks with.
     *
     * @var array<int, string>
     */
    protected const TRUSTED_CERT_HOSTS = [
        'api.paypal.com',
        'api-m.paypal.com',
        'api-m.sandbox.paypal.com',
        'api.sandbox.paypal.com',
    ];

    /**
     * Whether the storefront is able to take payments.
     */
    public function isConfigured(): bool
    {
        return filled(config('paypal.client_id')) && filled(config('paypal.client_secret'));
    }

    /**
     * Create a PayPal order for the given order.
     *
     * @return array{id: string, approval_url: string}
     */
    public function createOrder(Order $order, string $returnUrl, string $cancelUrl): array
    {
        if (strtoupper($order->currency) !== strtoupper((string) config('paypal.currency'))) {
            throw new PayPalException("PayPal can only charge in {$this->currency()}.");
        }

        $response = $this->client()->post($this->baseUrl().'/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'custom_id' => (string) $order->id,
                    'invoice_id' => $order->reference,
                    'description' => $order->product->name,
                    'amount' => [
                        'currency_code' => $order->currency,
                        'value' => number_format($order->amount / 100, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ]);

        if ($response->failed()) {
            throw PayPalException::fromResponse($response);
        }

        $approvalUrl = $this->approvalUrl($response->json('links', []));

        if ($approvalUrl === null) {
            throw new PayPalException('PayPal did not return an approval link.');
        }

        return [
            'id' => (string) $response->json('id'),
            'approval_url' => $approvalUrl,
        ];
    }

    /**
     * The link that sends the buyer to the PayPal approval page.
     */
    protected function approvalUrl(mixed $links): ?string
    {
        if (! is_array($links)) {
            return null;
        }

        $approval = null;

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $href = (string) ($link['href'] ?? '');

            if ($href === '') {
                continue;
            }

            if (($link['rel'] ?? null) === 'payer-action') {
                return $href;
            }

            if (($link['rel'] ?? null) === 'approve' && $approval === null) {
                $approval = $href;
            }
        }

        return $approval;
    }

    /**
     * Capture an approved PayPal order.
     *
     * @return array<string, mixed>
     */
    public function captureOrder(string $paypalOrderId): array
    {
        $response = $this->client()->post($this->baseUrl()."/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($response->failed()) {
            throw PayPalException::fromResponse($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Verify that a webhook call really comes from PayPal.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhookSignature(Request $request, array $payload): bool
    {
        if (blank(config('paypal.webhook_id'))) {
            return false;
        }

        $certUrl = (string) $request->header('PAYPAL-CERT-URL', '');

        if (! $this->isTrustedCertUrl($certUrl)) {
            return false;
        }

        $response = $this->client()->post($this->baseUrl().'/v1/notifications/verify-webhook-signature', [
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'cert_url' => $certUrl,
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'webhook_id' => config('paypal.webhook_id'),
            'webhook_event' => $payload,
        ]);

        if ($response->failed()) {
            return false;
        }

        return $response->json('verification_status') === 'SUCCESS';
    }

    /**
     * The HTTP client used for every PayPal call.
     */
    protected function client(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson();
    }

    /**
     * Fetch an access token and keep it until it expires.
     */
    protected function accessToken(): string
    {
        $key = 'paypal.access_token.'.config('paypal.mode');

        if (filled($token = Cache::get($key))) {
            return (string) $token;
        }

        $response = Http::asForm()
            ->withBasicAuth((string) config('paypal.client_id'), (string) config('paypal.client_secret'))
            ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw PayPalException::fromResponse($response);
        }

        $token = (string) $response->json('access_token');
        $expiresIn = max(60, (int) $response->json('expires_in', 300) - 60);

        Cache::put($key, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    /**
     * The API endpoint of the configured environment.
     */
    protected function baseUrl(): string
    {
        if (filled(config('paypal.base_url'))) {
            return rtrim((string) config('paypal.base_url'), '/');
        }

        return $this->isLive()
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Whether the live environment is used.
     */
    protected function isLive(): bool
    {
        return config('paypal.mode') === 'live';
    }

    /**
     * The currency PayPal charges in.
     */
    protected function currency(): string
    {
        return strtoupper((string) config('paypal.currency', 'USD'));
    }

    /**
     * Whether the certificate of a webhook call comes from PayPal.
     */
    protected function isTrustedCertUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && in_array($host, self::TRUSTED_CERT_HOSTS, true);
    }
}
