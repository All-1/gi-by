<?php

namespace PersonalAccount\Workers;

use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\FinishedExplanationRecord;
use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Domain\Record\TestRecord;
use BpKnowledgeTests\Domain\RetakeEligibility;
use BpKnowledgeTests\Plugin;
use PersonalAccount\Core\Container;
use PersonalAccount\Testing\SubmitQuestionResult;
use PersonalAccount\Testing\TestController;

/** Knowledge-tests WebSocket payloads (Phase 4). */
class KnowledgeTestsWsWorker
{
  /** @var Container */
  private $container;
  /** @var KnowledgeTestsSessionWorker */
  private $sessionWorker;

  public function __construct($Container)
  {
    $this->container = $Container;
    $this->sessionWorker = $this->container->get('KnowledgeTestsSessionWorker');
  }

  /** @param array<string, mixed> $body */
  public function showTests(int $userId, array $body): array
  {
    if (!$this->sessionWorker->isPluginAvailable()) {
      return $this->error('validation', ['plugin_unavailable']);
    }

    $page = max(1, (int) ($body['page'] ?? 1));
    $perPage = max(1, min(100, (int) ($body['perPage'] ?? 20)));
    $all = $this->plugin()->catalog()->tests->listAll();
    $slice = array_slice($all, ($page - 1) * $perPage, $perPage);
    $tests = [];
    foreach ($slice as $test) {
      $tests[] = $this->testListRow($userId, $test);
    }

    return [
      'ok' => true,
      'tests' => $tests,
      'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => count($all)],
      'navFailedHighlight' => $this->navFailedHighlight($userId, $all),
    ];
  }

  /** @param array<string, mixed> $body */
  public function startTest(int $userId, array $body): array
  {
    $testId = $this->requiredTestId($body);
    if ($testId === null || !$this->sessionWorker->isPluginAvailable()) {
      return $this->error('validation', ['missing_test_id']);
    }

    $controller = $this->testController();
    if ($controller->hasSession($userId, $testId)) {
      return $this->questionPage($userId, $testId, null);
    }

    $crossBlock = $this->crossTestInProgressError($userId, $testId);
    if ($crossBlock !== null) {
      return $crossBlock;
    }

    $retake = $this->plugin()->attempts()->retake->evaluate($userId, $testId);
    if (!$retake->canStart) {
      return $this->retakeError($retake);
    }

    try {
      $controller->startOrResume($userId, $testId, $this->sessionWorker->loadSessionQuestionRows($testId));
    } catch (\InvalidArgumentException $e) {
      return $this->error('invalid_test', ['invalid_test']);
    }

    return $this->questionPage($userId, $testId, null);
  }

  /** @param array<string, mixed> $body */
  public function submitTestQuestion(int $userId, array $body): array
  {
    if (!$this->sessionWorker->isPluginAvailable()) {
      return $this->error('validation', ['plugin_unavailable']);
    }
    $testId = $this->requiredTestId($body);
    if ($testId === null) {
      return $this->error('validation', ['missing_test_id']);
    }

    try {
      $result = $this->testController()->submitQuestion($userId, $testId, $this->selectedAnswerIds($body));
    } catch (\InvalidArgumentException $e) {
      return $this->error('validation', ['validation']);
    }
    if ($result === null) {
      return $this->error('no_session', ['no_session']);
    }

    return $this->questionPage($userId, $testId, $result);
  }

  /** @param array<string, mixed> $body */
  public function finishTest(int $userId, array $body): array
  {
    $testId = $this->requiredTestId($body);
    if ($testId === null || !$this->sessionWorker->isPluginAvailable()) {
      return $this->error('no_session', ['no_session']);
    }

    $lines = $this->testController()->buildFinishPayload($userId, $testId);
    if ($lines === []) {
      return $this->error('no_session', ['no_session']);
    }

    try {
      $attempt = $this->plugin()->attempts()->complete->finish($userId, $testId, $lines);
    } catch (\InvalidArgumentException $e) {
      return $this->error('validation', ['validation']);
    }

    $this->testController()->clear($userId, $testId);

    return [
      'ok' => true,
      'attempt' => $this->attemptPayload($attempt),
      'medalId' => $this->plugin()->attempts()->findMedalIdForUserAndTest($userId, $testId),
      'materialsRequired' => $attempt->isFailed()
        && $this->plugin()->attempts()->materials->hasPendingMaterials($userId, $testId),
    ];
  }

  /** @param array<string, mixed> $body */
  public function chatStarsForUsers(int $requestingUserId, array $body): array
  {
    if (!$this->sessionWorker->isPluginAvailable()) {
      return $this->error('validation', ['plugin_unavailable']);
    }
    $raw = $body['userIds'] ?? [];
    if (!is_array($raw)) {
      return $this->error('validation', ['validation']);
    }
    $userIds = array_values(array_unique(array_map('intval', $raw)));
    $userIds = array_values(array_filter($userIds, static fn (int $id): bool => $id > 0));
    if ($userIds === []) {
      return ['ok' => true, 'starsByUserId' => []];
    }

    return [
      'ok' => true,
      'starsByUserId' => $this->plugin()->attempts()->achievements->chatStarsForUserIds($userIds),
    ];
  }

  /** @param array<string, mixed> $body */
  public function abandonTest(int $userId, array $body): array
  {
    $testId = $this->requiredTestId($body);
    if ($testId !== null) {
      $this->testController()->clear($userId, $testId);
    }

    return $this->showTests($userId, []);
  }

  /** @param array<string, mixed> $body */
  public function getTestMaterials(int $userId, array $body): array
  {
    $testId = $this->requiredTestId($body);
    if ($testId === null || !$this->sessionWorker->isPluginAvailable()) {
      return $this->error('validation', ['validation']);
    }

    $plugin = $this->plugin();
    $items = $plugin->attempts()->materials->listPendingForUserAndTest($userId, $testId);
    $attemptId = isset($items[0]) ? $items[0]->attemptId : 0;
    $mapped = [];
    foreach ($items as $row) {
      $mapped[] = $this->materialItem($row, $plugin);
    }

    return [
      'ok' => true,
      'testId' => $testId,
      'attemptId' => $attemptId,
      'items' => $mapped,
    ];
  }

  /** @param array<string, mixed> $body */
  public function confirmTestMaterialsExamined(int $userId, array $body): array
  {
    $testId = $this->requiredTestId($body);
    $attemptId = (int) ($body['attemptId'] ?? 0);
    if ($testId === null || $attemptId <= 0 || !$this->sessionWorker->isPluginAvailable()) {
      return $this->error('validation', ['validation']);
    }

    $this->plugin()->attempts()->materials->confirmExamined($userId, $attemptId);

    return $this->getTestMaterials($userId, ['testId' => $testId]);
  }

  /** @return array<string, mixed>|null */
  private function crossTestInProgressError(int $userId, int $testId): ?array
  {
    $active = $this->testController()->inProgressTestId($userId);
    if ($active !== null && $active !== $testId) {
      return $this->error('test_in_progress', ['test_in_progress']);
    }

    return null;
  }

  private function canStartTest(int $userId, int $testId, RetakeEligibility $retake): bool
  {
    $controller = $this->testController();
    if ($controller->hasSession($userId, $testId)) {
      return false;
    }
    $active = $controller->inProgressTestId($userId);

    return $retake->canStart && ($active === null || $active === $testId);
  }

  private function testController(): TestController
  {
    return $this->sessionWorker->testController();
  }

  private function plugin(): Plugin
  {
    return $this->sessionWorker->plugin();
  }

  /** @param list<TestRecord> $catalog */
  private function navFailedHighlight(int $userId, array $catalog): bool
  {
    foreach ($catalog as $test) {
      $row = $this->testListRow($userId, $test);
      if (($row['displayStatus'] ?? '') === 'failed') {
        return true;
      }
    }

    return false;
  }

  /** @return array<string, mixed> */
  private function testListRow(int $userId, TestRecord $test): array
  {
    $testId = $test->id;
    $controller = $this->testController();
    $latest = $this->plugin()->attempts()->findLatestAttempt($userId, $testId);
    $displayStatus = 'not_started';
    if ($controller->hasSession($userId, $testId)) {
      $displayStatus = 'in_progress';
    } elseif ($latest !== null) {
      $displayStatus = $latest->status;
    }

    $retake = $this->plugin()->attempts()->retake->evaluate($userId, $testId);

    return [
      'testId' => $testId,
      'title' => $test->title,
      'displayStatus' => $displayStatus,
      'score' => $latest?->score,
      'medalId' => $this->plugin()->attempts()->findMedalIdForUserAndTest($userId, $testId),
      'canStart' => $this->canStartTest($userId, $testId, $retake),
      'canOpenMaterials' => $this->plugin()->attempts()->materials->hasPendingMaterials($userId, $testId),
      'achievementArea' => $test->achievementArea,
    ];
  }

  private function questionPage(int $userId, int $testId, ?SubmitQuestionResult $submit): array
  {
    $session = $this->testController()->getSession($userId, $testId);
    if ($session === null) {
      return $this->error('no_session', ['no_session']);
    }

    $questionId = $submit !== null ? $submit->questionId : $session->currentQuestionId();
    if ($questionId === null) {
      return [
        'ok' => true,
        'testId' => $testId,
        'progress' => $session->progress(),
        'sessionComplete' => true,
      ];
    }

    $question = $this->plugin()->catalog()->questions->find($questionId);
    if ($question === null) {
      return $this->error('validation', ['validation']);
    }

    $payload = [
      'ok' => true,
      'testId' => $testId,
      'progress' => $session->progress(),
      'question' => $this->questionPayload($question, $session->shuffledAnswerIds($questionId)),
      'sessionComplete' => $submit !== null ? $submit->sessionComplete : $session->isSessionComplete(),
    ];
    if ($submit !== null) {
      $payload['feedback'] = $this->feedbackPayload(
        $submit,
        $session->correctAnswerIds($questionId),
        $question,
      );
    }

    return $payload;
  }

  /** @param list<int> $shuffledAnswerIds */
  private function questionPayload(QuestionRecord $question, array $shuffledAnswerIds): array
  {
    $plugin = $this->plugin();
    $options = [];
    foreach ($shuffledAnswerIds as $answerId) {
      $answer = $plugin->catalog()->answers->find((int) $answerId);
      if ($answer === null) {
        continue;
      }
      $options[] = ['answerId' => $answer->id, 'text' => $answer->answer];
    }

    return [
      'questionId' => $question->id,
      'text' => $question->question,
      'answerOptions' => $options,
    ];
  }

  /** @param list<int> $correctAnswerIds */
  private function feedbackPayload(
    SubmitQuestionResult $submit,
    array $correctAnswerIds,
    QuestionRecord $question,
  ): array {
    $correctSet = array_fill_keys($correctAnswerIds, true);
    $right = [];
    $wrong = [];
    foreach ($submit->lastSelectedAnswerIds as $id) {
      if (isset($correctSet[$id])) {
        $right[] = $id;
      } else {
        $wrong[] = $id;
      }
    }
    $show = $submit->graded->failedAnswers > 0 || $submit->graded->missedCorrect > 0;

    return [
      'show' => true,
      'rightAnswerIds' => $right,
      'wrongAnswerIds' => $wrong,
      'explanation' => $show ? $question->explanation : '',
      'reference' => $show ? $question->reference : '',
    ];
  }

  private function materialItem(FinishedExplanationRecord $row, Plugin $plugin): array
  {
    $question = $plugin->catalog()->questions->find($row->questionId);

    return [
      'explanationId' => $row->id,
      'questionId' => $row->questionId,
      'questionText' => $question !== null ? $question->question : '',
      'explanation' => $row->explanation,
      'reference' => $row->reference,
    ];
  }

  private function attemptPayload(AttemptRecord $attempt): array
  {
    return [
      'attemptId' => $attempt->id,
      'testId' => $attempt->testId,
      'testVersion' => $attempt->testVersion,
      'score' => $attempt->score,
      'status' => $attempt->status,
      'validAnswers' => $attempt->validAnswers,
      'invalidAnswers' => $attempt->invalidAnswers,
      'dateFinished' => $attempt->dateFinished,
    ];
  }

  private function retakeError(RetakeEligibility $retake): array
  {
    $code = !empty($retake->reasons) ? $retake->reasons[0] : 'validation';

    return $this->error($code, $retake->reasons);
  }

  /** @param list<string> $reasons */
  private function error(string $code, array $reasons): array
  {
    return ['ok' => false, 'error' => ['code' => $code, 'reasons' => $reasons]];
  }

  /** @param array<string, mixed> $body */
  private function requiredTestId(array $body): ?int
  {
    $testId = (int) ($body['testId'] ?? 0);

    return $testId > 0 ? $testId : null;
  }

  /** @param array<string, mixed> $body @return list<int> */
  private function selectedAnswerIds(array $body): array
  {
    $raw = $body['selectedAnswerIds'] ?? [];

    return is_array($raw) ? array_map('intval', $raw) : [];
  }
}
