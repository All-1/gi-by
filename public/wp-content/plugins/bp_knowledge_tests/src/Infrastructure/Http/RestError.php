<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http;

use WP_Error;

final class RestError
{
    public static function notFound(string $message): WP_Error
    {
        return new WP_Error('bp_kt_not_found', $message, ['status' => 404]);
    }

    public static function badRequest(string $message): WP_Error
    {
        return new WP_Error('bp_kt_bad_request', $message, ['status' => 400]);
    }

    public static function fromThrowable(\Throwable $e): WP_Error
    {
        if ($e instanceof \InvalidArgumentException) {
            return self::badRequest($e->getMessage());
        }

        return new WP_Error('bp_kt_error', $e->getMessage(), ['status' => 500]);
    }
}
