<?php
namespace PersonalAccount\Workers\Analytics;

use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;

abstract class AnalyticsQueriesBase
{
  /** @var Container */
  protected $container;
  /** @var DBWorker */
  protected $dbWorker;

  public function __construct(object $Container)
  {
    $this->container = $Container;
    $this->dbWorker = $this->container->get('DBWorker');
  }

  protected function normalizeSqlResults($results): array
  {
    if ($results === null || $results === false || $results === '') {
      return [];
    }
    if (!is_array($results)) {
      return [$results];
    }
    return $results;
  }

  protected function periodExpression(string $period, string $column): string
  {
    return match ($period) {
      'all' => "'all'",
      'week' => "YEARWEEK({$column}, 1)",
      'month' => "DATE_FORMAT({$column}, '%Y-%m')",
      'quarter' => "CONCAT(YEAR({$column}), '-Q', QUARTER({$column}))",
      'year' => "YEAR({$column})",
    };
  }

  public function durationColumn(array $params, string $metric): string
  {
    $workHours = ($params['timeMode'] ?? 'work_hours') !== 'standard';
    return $workHours ? $metric . '_at_work_hours' : $metric;
  }
}
