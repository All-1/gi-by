<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http\Routes;

use BpKnowledgeTests\Infrastructure\Http\ResourcePresenter;
use BpKnowledgeTests\Infrastructure\Http\RestError;
use BpKnowledgeTests\Infrastructure\Http\RestPermission;
use BpKnowledgeTests\Plugin;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class CriticalUpdateRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/tests/(?P<id>\d+)/critical-update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'execute'],
            'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
        ]);
    }

    public function execute(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $testId = (int) $request['id'];
        try {
            $this->plugin->attempts()->criticalUpdate->execute($testId);
        } catch (\InvalidArgumentException $e) {
            return RestError::notFound($e->getMessage());
        } catch (\Throwable $e) {
            return RestError::fromThrowable($e);
        }
        $test = $this->plugin->catalog()->tests->find($testId);
        if ($test === null) {
            return RestError::notFound('Test not found.');
        }

        return new WP_REST_Response(['test' => ResourcePresenter::test($test)], 200);
    }
}
