<?php

namespace App\Http\Responses;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Builds the error page of the single page application.
 *
 * Without it a failed request drops the visitor out of the application and
 * onto the plain error page of the framework.
 */
class ErrorPageResponse
{
    /**
     * Render the error page for a request that failed.
     *
     * Returns null when the error cannot be rendered as a page, so the
     * framework keeps its own handling. That is the case for the errors that
     * carry a response of their own, like a failed validation or a request
     * that was never signed in.
     */
    public static function make(Request $request, Throwable $exception): ?Response
    {
        if (self::carriesItsOwnResponse($exception)) {
            return null;
        }

        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        if ($status < 400) {
            return null;
        }

        try {
            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Whether the framework already knows how to answer this error.
     */
    protected static function carriesItsOwnResponse(Throwable $exception): bool
    {
        return $exception instanceof ValidationException
            || $exception instanceof AuthenticationException
            || $exception instanceof HttpResponseException;
    }
}
