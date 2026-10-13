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

final class TestsRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/tests', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'listTests'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'createTest'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
        ]);

        register_rest_route(self::NS, '/tests/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getTest'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'updateTest'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'deleteTest'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
        ]);
    }

    public function listTests(): WP_REST_Response
    {
        $tests = $this->plugin->catalog()->tests->listAll();
        $payload = array_map(static fn ($t) => ResourcePresenter::test($t), $tests);

        return new WP_REST_Response(['tests' => $payload], 200);
    }

    public function createTest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $title = trim((string) $request->get_param('title'));
        if ($title === '') {
            return RestError::badRequest('title is required.');
        }
        $area = $request->get_param('achievementArea');
        $id = $this->plugin->catalog()->tests->create($title, is_string($area) ? $area : null);
        $test = $this->plugin->catalog()->tests->find($id);

        return new WP_REST_Response(['test' => ResourcePresenter::test($test)], 201);
    }

    public function getTest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $test = $this->plugin->catalog()->tests->find((int) $request['id']);
        if ($test === null) {
            return RestError::notFound('Test not found.');
        }

        return new WP_REST_Response(['test' => ResourcePresenter::test($test)], 200);
    }

    public function updateTest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = (int) $request['id'];
        $title = trim((string) $request->get_param('title'));
        if ($title === '') {
            return RestError::badRequest('title is required.');
        }
        $area = $request->get_param('achievementArea');
        if (!$this->plugin->catalog()->tests->update($id, $title, is_string($area) ? $area : null)) {
            return RestError::notFound('Test not found.');
        }
        $test = $this->plugin->catalog()->tests->find($id);

        return new WP_REST_Response(['test' => ResourcePresenter::test($test)], 200);
    }

    public function deleteTest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->plugin->catalog()->tests->delete((int) $request['id'])) {
            return RestError::notFound('Test not found.');
        }

        return new WP_REST_Response(['deleted' => true], 200);
    }
}
