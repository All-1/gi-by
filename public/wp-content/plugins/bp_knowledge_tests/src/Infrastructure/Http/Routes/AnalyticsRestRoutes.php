<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http\Routes;

use BpKnowledgeTests\Infrastructure\Http\RestError;
use BpKnowledgeTests\Infrastructure\Http\RestPermission;
use BpKnowledgeTests\Plugin;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class AnalyticsRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/analytics/tests', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'listTests'],
            'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
        ]);

        register_rest_route(self::NS, '/analytics/tests/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getTest'],
            'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
        ]);
    }

    public function listTests(): WP_REST_Response
    {
        return new WP_REST_Response(
            ['tests' => $this->plugin->reporting()->analytics->listTestSummaries()],
            200,
        );
    }

    public function getTest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $detail = $this->plugin->reporting()->analytics->testDetail((int) $request['id']);
        if ($detail === null) {
            return RestError::notFound('Test not found.');
        }

        return new WP_REST_Response(['analytics' => $detail], 200);
    }
}
