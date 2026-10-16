<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http;

use BpKnowledgeTests\Infrastructure\Http\Routes\AnalyticsRestRoutes;
use BpKnowledgeTests\Infrastructure\Http\Routes\AnswersRestRoutes;
use BpKnowledgeTests\Infrastructure\Http\Routes\ConfigRestRoutes;
use BpKnowledgeTests\Infrastructure\Http\Routes\CriticalUpdateRestRoutes;
use BpKnowledgeTests\Infrastructure\Http\Routes\QuestionsRestRoutes;
use BpKnowledgeTests\Infrastructure\Http\Routes\ResultsRestRoutes;
use BpKnowledgeTests\Infrastructure\Http\Routes\TestsRestRoutes;
use BpKnowledgeTests\Plugin;

final class AdminRestApi
{
    public const NAMESPACE = 'bp-knowledge-tests/v1';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        add_action('rest_api_init', function (): void {
            (new TestsRestRoutes($this->plugin))->register();
            (new QuestionsRestRoutes($this->plugin))->register();
            (new AnswersRestRoutes($this->plugin))->register();
            (new ConfigRestRoutes($this->plugin))->register();
            (new CriticalUpdateRestRoutes($this->plugin))->register();
            (new ResultsRestRoutes($this->plugin))->register();
            (new AnalyticsRestRoutes($this->plugin))->register();
        });
    }
}
