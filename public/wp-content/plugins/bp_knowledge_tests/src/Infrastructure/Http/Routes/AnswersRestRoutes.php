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

final class AnswersRestRoutes
{
    private const NS = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        register_rest_route(self::NS, '/questions/(?P<questionId>\d+)/answers', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'listForQuestion'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create'],
                'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
            ],
        ]);

        register_rest_route(self::NS, '/answers/(?P<id>\d+)', [
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

        register_rest_route(self::NS, '/questions/(?P<questionId>\d+)/correct-answer-ids', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [$this, 'setCorrectIds'],
            'permission_callback' => static fn (): bool => RestPermission::canManageCatalog(),
        ]);
    }

    public function listForQuestion(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $questionId = (int) $request['questionId'];
        if ($this->plugin->catalog()->questions->find($questionId) === null) {
            return RestError::notFound('Question not found.');
        }
        $answers = $this->plugin->catalog()->answers->listForQuestion($questionId);
        $payload = array_map(static fn ($a) => ResourcePresenter::answer($a), $answers);
        $correctIds = $this->plugin->catalog()->answers->listCorrectAnswerIdsForQuestion($questionId);

        return new WP_REST_Response(['answers' => $payload, 'correctAnswerIds' => $correctIds], 200);
    }

    public function create(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $questionId = (int) $request['questionId'];
        if ($this->plugin->catalog()->questions->find($questionId) === null) {
            return RestError::notFound('Question not found.');
        }
        $text = trim((string) $request->get_param('answer'));
        if ($text === '') {
            return RestError::badRequest('answer is required.');
        }
        $isCorrect = (bool) $request->get_param('isCorrect');
        $id = $this->plugin->catalog()->answers->create($questionId, $text, $isCorrect);
        $record = $this->plugin->catalog()->answers->find($id);

        return new WP_REST_Response(['answer' => ResourcePresenter::answer($record)], 201);
    }

    public function get(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $answer = $this->plugin->catalog()->answers->find((int) $request['id']);
        if ($answer === null) {
            return RestError::notFound('Answer not found.');
        }

        return new WP_REST_Response(['answer' => ResourcePresenter::answer($answer)], 200);
    }

    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = (int) $request['id'];
        $text = trim((string) $request->get_param('answer'));
        if ($text === '') {
            return RestError::badRequest('answer is required.');
        }
        if (!$this->plugin->catalog()->answers->update($id, $text)) {
            return RestError::notFound('Answer not found.');
        }
        if ($request->has_param('isCorrect')) {
            $this->plugin->catalog()->answers->setIsCorrect($id, (bool) $request->get_param('isCorrect'));
        }
        $answer = $this->plugin->catalog()->answers->find($id);

        return new WP_REST_Response(['answer' => ResourcePresenter::answer($answer)], 200);
    }

    public function delete(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        if (!$this->plugin->catalog()->answers->delete((int) $request['id'])) {
            return RestError::notFound('Answer not found.');
        }

        return new WP_REST_Response(['deleted' => true], 200);
    }

    public function setCorrectIds(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $questionId = (int) $request['questionId'];
        if ($this->plugin->catalog()->questions->find($questionId) === null) {
            return RestError::notFound('Question not found.');
        }
        $ids = $request->get_param('answerIds');
        if (!is_array($ids)) {
            return RestError::badRequest('answerIds must be an array.');
        }
        try {
            $this->plugin->catalog()->answers->setCorrectAnswerIdsForQuestion(
                $questionId,
                array_map(static fn ($id): int => (int) $id, $ids),
            );
        } catch (\InvalidArgumentException $e) {
            return RestError::badRequest($e->getMessage());
        }
        $correctIds = $this->plugin->catalog()->answers->listCorrectAnswerIdsForQuestion($questionId);

        return new WP_REST_Response(['correctAnswerIds' => $correctIds], 200);
    }
}
