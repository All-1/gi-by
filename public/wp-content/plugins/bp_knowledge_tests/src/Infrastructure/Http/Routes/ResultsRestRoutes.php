<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http\Routes;

use BpKnowledgeTests\Infrastructure\Http\RestPermission;
use BpKnowledgeTests\Plugin;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class ResultsRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/results', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'listResults'],
            'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
        ]);
    }

    public function listResults(WP_REST_Request $request): WP_REST_Response
    {
        $testId = $this->optionalPositiveInt($request->get_param('testId'));
        $userId = $this->optionalPositiveInt($request->get_param('userId'));
        $status = $this->optionalStatus($request->get_param('status'));
        $page = max(1, (int) $request->get_param('page') ?: 1);
        $perPage = (int) $request->get_param('perPage') ?: 50;

        $payload = $this->plugin->reporting()->results->list($testId, $userId, $status, $page, $perPage);

        return new WP_REST_Response($payload, 200);
    }

    private function optionalPositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private function optionalStatus(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        $allowed = ['passed', 'failed', 'outdated'];

        return in_array($value, $allowed, true) ? $value : null;
    }
}
