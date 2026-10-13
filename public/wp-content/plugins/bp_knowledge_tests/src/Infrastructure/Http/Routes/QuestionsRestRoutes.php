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

final class QuestionsRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/tests/(?P<testId>\d+)/questions', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'listForTest'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
        ]);

        register_rest_route(self::NS, '/questions/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
        ]);
    }

    public function listForTest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $testId = (int) $request['testId'];
        if ($this->plugin->catalog()->tests->find($testId) === null) {
            return RestError::notFound('Test not found.');
        }
        $questions = $this->plugin->catalog()->questions->listForTest($testId);
        $payload = array_map(static fn ($q) => ResourcePresenter::question($q), $questions);

        return new WP_REST_Response(['questions' => $payload], 200);
    }

    public function create(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $testId = (int) $request['testId'];
        if ($this->plugin->catalog()->tests->find($testId) === null) {
            return RestError::notFound('Test not found.');
        }
        $body = $this->questionBody($request);
        if ($body instanceof WP_Error) {
            return $body;
        }
        $id = $this->plugin->catalog()->questions->create(
            $testId,
            $body['question'],
            $body['explanation'],
            $body['reference'],
        );
        $record = $this->plugin->catalog()->questions->find($id);

        return new WP_REST_Response(['question' => ResourcePresenter::question($record)], 201);
    }

    public function get(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $question = $this->plugin->catalog()->questions->find((int) $request['id']);
        if ($question === null) {
            return RestError::notFound('Question not found.');
        }

        return new WP_REST_Response(['question' => ResourcePresenter::question($question)], 200);
    }

    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = (int) $request['id'];
        $body = $this->questionBody($request);
        if ($body instanceof WP_Error) {
            return $body;
        }
        if (!$this->plugin->catalog()->questions->update($id, $body['question'], $body['explanation'], $body['reference'])) {
            return RestError::notFound('Question not found.');
        }
        $question = $this->plugin->catalog()->questions->find($id);

        return new WP_REST_Response(['question' => ResourcePresenter::question($question)], 200);
    }

    public function delete(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->plugin->catalog()->questions->delete((int) $request['id'])) {
            return RestError::notFound('Question not found.');
        }

        return new WP_REST_Response(['deleted' => true], 200);
    }

    /**
     * @return array{question: string, explanation: string, reference: string}|WP_Error
     */
    private function questionBody(WP_REST_Request $request): array|WP_Error
    {
        $question = trim((string) $request->get_param('question'));
        if ($question === '') {
            return RestError::badRequest('question is required.');
        }

        return [
            'question' => $question,
            'explanation' => (string) $request->get_param('explanation'),
            'reference' => (string) $request->get_param('reference'),
        ];
    }
}
