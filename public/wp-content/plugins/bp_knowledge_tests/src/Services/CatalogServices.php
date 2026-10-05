<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AnswerRepository;
use BpKnowledgeTests\Infrastructure\Repository\ConfigRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;
use BpKnowledgeTests\Services\Catalog\AnswerCatalog;
use BpKnowledgeTests\Services\Catalog\ConfigCatalog;
use BpKnowledgeTests\Services\Catalog\QuestionCatalog;
use BpKnowledgeTests\Services\Catalog\TestCatalog;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class CatalogServices
{
    public readonly TestCatalog $tests;
    public readonly QuestionCatalog $questions;
    public readonly AnswerCatalog $answers;
    public readonly ConfigCatalog $config;

    public function __construct(Container $servicesContainer)
    {
        $db = $this->dbWorker($servicesContainer);
        $dbUtilities = $this->dbUtilities($servicesContainer);
        $clock = new DatabaseClock();
        $mapper = new RecordMapper();

        $testRepository = new TestRepository($db, $dbUtilities, $clock, $mapper);
        $questionRepository = new QuestionRepository($db, $dbUtilities, $clock, $mapper);
        $answerRepository = new AnswerRepository($db, $dbUtilities, $clock, $mapper);
        $configRepository = new ConfigRepository($db, $dbUtilities);

        $this->tests = new TestCatalog($testRepository);
        $this->questions = new QuestionCatalog($questionRepository, $testRepository);
        $this->answers = new AnswerCatalog($answerRepository, $questionRepository, $testRepository);
        $this->config = new ConfigCatalog($configRepository);
    }

    private function dbWorker(Container $servicesContainer): DBWorker
    {
        $worker = $servicesContainer->get('DBWorker');
        if (!$worker instanceof DBWorker) {
            throw new \RuntimeException('DBWorker is not registered in $servicesContainer.');
        }

        return $worker;
    }

    private function dbUtilities(Container $servicesContainer): DBUtilities
    {
        $utilities = $servicesContainer->get('DBUtilities');
        if (!$utilities instanceof DBUtilities) {
            throw new \RuntimeException('DBUtilities is not registered in $servicesContainer.');
        }

        return $utilities;
    }
}
