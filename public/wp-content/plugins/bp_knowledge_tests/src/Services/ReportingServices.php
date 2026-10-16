<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AnswerRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptAdminRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptAnalyticsRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Services\Reporting\ResultsListQuery;
use BpKnowledgeTests\Services\Reporting\TestAnalyticsQuery;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class ReportingServices
{
    public readonly ResultsListQuery $results;
    public readonly TestAnalyticsQuery $analytics;

    public function __construct(Container $servicesContainer, CatalogServices $catalog)
    {
        $db = $servicesContainer->get('DBWorker');
        $dbUtilities = $servicesContainer->get('DBUtilities');
        $mapper = new RecordMapper();
        $clock = new DatabaseClock();
        $attemptAdmin = new AttemptAdminRepository($db, $mapper);
        $attemptAnalytics = new AttemptAnalyticsRepository($db, $mapper);
        $questionRepository = new QuestionRepository($db, $dbUtilities, $clock, $mapper);
        $answerRepository = new AnswerRepository($db, $dbUtilities, $clock, $mapper);

        $this->results = new ResultsListQuery($attemptAdmin, $mapper);
        $this->analytics = new TestAnalyticsQuery(
            $attemptAnalytics,
            $catalog,
            $questionRepository,
            $answerRepository,
        );
    }
}
