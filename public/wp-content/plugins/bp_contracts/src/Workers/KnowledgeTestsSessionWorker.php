<?php

namespace PersonalAccount\Workers;

use BpKnowledgeTests\Bootstrap\PluginBootstrap;
use BpKnowledgeTests\Plugin;
use BpKnowledgeTests\Services\CatalogServices;
use PersonalAccount\Core\Container;
use PersonalAccount\Testing\Contract\QuestionSelectionGraderInterface;
use PersonalAccount\Testing\GradedQuestionCounts;
use PersonalAccount\Testing\TestController;

/**
 * bp_knowledge_tests plugin access + in-memory TestController (Phase 4).
 */
class KnowledgeTestsSessionWorker implements QuestionSelectionGraderInterface
{
  /** @var Container */
  private $container;
  /** @var TestController */
  private $testController;
  /** @var Plugin|null */
  private $cachedPlugin;

  public function __construct($Container)
  {
    $this->container = $Container;
    $this->testController = new TestController($this);
    $this->cachedPlugin = null;
  }

  public function testController(): TestController
  {
    return $this->testController;
  }

  public function isPluginAvailable(): bool
  {
    try {
      return $this->resolvePlugin() instanceof Plugin;
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function plugin(): Plugin
  {
    $plugin = $this->resolvePlugin();
    if (!$plugin instanceof Plugin) {
      throw new \RuntimeException('bp_knowledge_tests is not available.');
    }

    return $plugin;
  }

  /**
   * @return list<array{questionId: int, answerIds: list<int>, correctAnswerIds: list<int>}>
   */
  public function loadSessionQuestionRows(int $testId): array
  {
    $plugin = $this->plugin();
    if ($plugin->catalog()->tests->find($testId) === null) {
      throw new \InvalidArgumentException('Test not found.');
    }

    $catalog = $plugin->catalog();
    $rows = [];
    foreach ($catalog->questions->listForTest($testId) as $question) {
      $rows[] = $this->sessionRowForQuestion($catalog, $question->id);
    }

    return $rows;
  }

  public function grade(array $correctAnswerIds, array $selectedAnswerIds): GradedQuestionCounts
  {
    $counts = $this->plugin()->domain()->selection->grade($correctAnswerIds, $selectedAnswerIds);

    return new GradedQuestionCounts(
      $counts->rightAnswers,
      $counts->failedAnswers,
      $counts->missedCorrect,
    );
  }

  private function resolvePlugin(): ?Plugin
  {
    if ($this->cachedPlugin instanceof Plugin) {
      return $this->cachedPlugin;
    }

    if (function_exists('bp_knowledge_tests_plugin')) {
      $plugin = bp_knowledge_tests_plugin();
      if ($plugin instanceof Plugin) {
        $this->cachedPlugin = $plugin;

        return $plugin;
      }

      return null;
    }

    if (!class_exists(PluginBootstrap::class)) {
      return null;
    }

    global $wpdb, $servicesContainer;
    if (!$servicesContainer instanceof Container) {
      return null;
    }

    $this->cachedPlugin = (new PluginBootstrap($wpdb, $servicesContainer))->compose();

    return $this->cachedPlugin;
  }

  private function sessionRowForQuestion(CatalogServices $catalog, int $questionId): array
  {
    $answerIds = [];
    foreach ($catalog->answers->listForQuestion($questionId) as $answer) {
      $answerIds[] = $answer->id;
    }

    return [
      'questionId' => $questionId,
      'answerIds' => $answerIds,
      'correctAnswerIds' => $catalog->answers->listCorrectAnswerIdsForQuestion($questionId),
    ];
  }
}
