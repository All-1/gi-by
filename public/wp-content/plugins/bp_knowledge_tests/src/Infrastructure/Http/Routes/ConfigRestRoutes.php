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

final class ConfigRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/config', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getAll'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'set'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
        ]);
    }

    public function getAll(): WP_REST_Response
    {
        return new WP_REST_Response(
            ['settings' => $this->plugin->catalog()->config->all()],
            200,
        );
    }

    public function set(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $settings = $request->get_param('settings');
        if (is_array($settings)) {
            foreach ($settings as $name => $value) {
                if (!is_string($name) || !is_scalar($value)) {
                    return RestError::badRequest('settings must be string keys with scalar values.');
                }
                $this->plugin->catalog()->config->set($name, (string) $value);
            }

            return new WP_REST_Response(
                ['settings' => $this->plugin->catalog()->config->all()],
                200,
            );
        }

        $name = $request->get_param('name');
        $value = $request->get_param('value');
        if (!is_string($name) || $name === '' || !is_scalar($value)) {
            return RestError::badRequest('Provide settings object or name and value.');
        }
        $this->plugin->catalog()->config->set($name, (string) $value);

        return new WP_REST_Response(
            ['settings' => $this->plugin->catalog()->config->all()],
            200,
        );
    }
}
