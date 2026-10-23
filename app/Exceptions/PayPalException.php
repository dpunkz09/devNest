<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class PayPalException extends RuntimeException
{
    /**
     * Build the exception from a failed PayPal response.
     */
    public static function fromResponse(Response $response): self
    {
        $name = (string) $response->json('name');
        $message = (string) $response->json('message');

        if (filled($message)) {
            $details = $response->json('details', []);
            $descriptions = [];

            if (is_array($details)) {
                foreach ($details as $detail) {
                    if (is_array($detail) && filled($detail['description'] ?? null)) {
                        $descriptions[] = (string) $detail['description'];
                    }
                }
            }

            $text = filled($descriptions)
                ? $name.': '.implode(' ', $descriptions)
                : $message;

            return new self($text);
        }

        return new self('PayPal returned an unexpected response. ('.$response->status().')');
    }

    /**
     * Build the exception for a checkout that is not configured.
     */
    public static function notConfigured(): self
    {
        return new self('PayPal is not configured yet.');
    }
}
