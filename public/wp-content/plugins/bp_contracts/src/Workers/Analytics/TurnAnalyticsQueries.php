<?php
namespace PersonalAccount\Workers\Analytics;

class TurnAnalyticsQueries extends AnalyticsQueriesBase
{
  public function queryTimeResponseStats(array $params): array
  {
    $rawSql = "WITH ranked AS (
      SELECT
        {$this->addUserId(params: $params, commaPostfix: ', ')}
        {$this->splitByPeriod($params, 'started_at')}
        {$params['field']},
        CUME_DIST() OVER (
        {$this->partitionBy($params)} 
        ORDER BY {$params['field']}) AS cd
          FROM 
          {$this->fromTable($params)}
          {$this->whereCondition($params)} 
      )
      SELECT
        {$this->addUserId(params: $params, commaPostfix: ', ')}
        {$this->addPeriod(params: $params, commaPostfix: ', ')}
        MIN(CASE WHEN cd >= 0.5 THEN {$params['field']} END) AS p50,
        MIN(CASE WHEN cd >= 0.9 THEN {$params['field']} END) AS p90,
        MIN(CASE WHEN cd >= 0.95 THEN {$params['field']} END) AS p95,
        MAX({$params['field']}) AS max_time_response
      FROM ranked
      {$this->groupBy($params)}
      {$this->orderBy($params)}
      ";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  public function querySlaStats(array $params): array
  {
    $threshold = intval($params['slaThresholdSeconds'] ?? 0);
    if ($threshold <= 0 || empty($params['field'])) {
      return [];
    }

    $field = $params['field'];
    $rawSql = "SELECT
        {$this->addUserId(params: $params, commaPostfix: ', ')}
        {$this->splitByPeriod($params, 'started_at')}
        COUNT(*) AS total,
        SUM(CASE WHEN {$field} > 0 AND {$field} <= {$threshold} THEN 1 ELSE 0 END) AS within_sla,
        ROUND(
          100 * SUM(CASE WHEN {$field} > 0 AND {$field} <= {$threshold} THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0),
          1
        ) AS sla_percent
      FROM {$this->fromTable($params)}
      {$this->whereCondition($params)}
      {$this->groupBy($params)}
      {$this->orderBy($params)}";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  public function queryTurnCounts(array $params): array
  {
    $userSelect = $this->addUserId($params, '', ', ');
    $periodSelect = $this->periodExpression($params['period'], 'started_at') . ' AS period';
    $rawSql = "SELECT
        {$userSelect}
        {$periodSelect},
        COUNT(*) AS turns_count
      FROM {$this->fromTable($params)}
      {$this->whereCondition($params)}
      GROUP BY {$this->groupByColumns($params)}
      {$this->orderByCounts($params)}";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  public function pickPercentileColumn($stats, string $column)
  {
    if (empty($stats)) {
      return null;
    }

    $column = $column === 'max_time_response' ? 'maxTimeResponse' : $column;
    $singleRow = !isset($stats[0]);
    $rows = $singleRow ? [$stats] : $stats;
    $result = [];
    $statColumns = ['p50', 'p90', 'p95', 'maxTimeResponse'];

    foreach ($rows as $row) {
      $item = [];
      foreach ($row as $key => $value) {
        if (!in_array($key, $statColumns, true) || $key === $column) {
          $item[$key] = $value;
        }
      }
      $result[] = $item;
    }

    return $singleRow ? $result[0] : $result;
  }

  private function partitionBy(array $params)
  {
    $rawSql = $params['whose'] !== 'all' || $params['period'] !== 'all' ? "PARTITION BY " : "";
    $rawSql .= $this->addUserId($params);
    $rawSql .= $this->splitByPeriodWithoutAs($params);
    return $rawSql;
  }

  private function orderBy(array $params)
  {
    $userId = $this->addUserId($params);
    $period = $this->addPeriod($params);
    $rawSql = $userId !== "" || $period !== "" ? "ORDER BY " : "";
    $rawSql .= $userId;
    $rawSql .= $userId !== "" && $period !== "" ? ", " : "";
    $rawSql .= $period;
    return $rawSql;
  }

  private function groupBy(array $params)
  {
    $userId = $this->addUserId($params);
    $period = $this->addPeriod($params);
    $rawSql = $userId !== "" || $period !== "" ? "GROUP BY " : "";
    $rawSql .= $userId;
    $rawSql .= $userId !== "" && $period !== "" ? ", " : "";
    $rawSql .= $period;
    return $rawSql;
  }

  private function fromTable(array $params)
  {
    // In queryTimeResponseStats(), something like:
    $from = 'gi_new_metrics_dialog_turns t';
    $join = !empty($params['dialoguesType'])
      ? ' JOIN gi_new_dialogues d ON d.id_dialog = t.id_dialog'
      : '';
    return $from . $join;
  }

  private function whereCondition(array $params)
  {
    $conditions = array_filter([
      $this->whereFilterField($params),
      $this->whereWhose($params),
      $this->whereStartedAt($params),
      $this->whereDialoguesType($params),
      $this->whereAnalyticsUser($params),
    ]);
    if (empty($conditions)) {
      return '';
    }

    return ' WHERE ' . implode(' AND ', $conditions);
  }

  private function whereFilterField(array $params)
  {
    return isset($params['filterField']) && isset($params['filterFieldValue']) && isset($params['filterCondition'])
      ? " {$params['filterField']} {$params['filterCondition']} {$params['filterFieldValue']} AND  {$params['field']} != 0"
      : "";
  }

  private function whereStartedAt(array $params)
  {
    return " started_at BETWEEN '{$params['dateFrom']}' AND '{$params['dateTo']}'";
  }

  private function whereDialoguesType(array $params)
  {
    if (empty($params['dialoguesType'])) {
      return '';
    }

    return "d.type_dialog = '{$params['dialoguesType']}'";
  }

  private function whereWhose(array $params)
  {
    return match ($params['whose']) {
      'factory' => " user_side = 'factory_worker'",
      'contractor' => " user_side = 'contractor'",
      'all' => " user_side = 'factory_worker'",
      'dealer', 'dealer_dependents' => " user_side = 'contractor' AND id_user_dealer IS NOT NULL",
    };
  }

  private function whereAnalyticsUser(array $params): string
  {
    if (empty($params['filterUserId'])) {
      return '';
    }

    if ($params['whose'] === 'dealer' || $params['whose'] === 'dealer_dependents') {
      return 'id_user_dealer = ' . intval($params['filterUserId']);
    }

    return 'id_user = ' . intval($params['filterUserId']);
  }

  private function splitByPeriod(array $params, string $column)
  {
    if ($params['period'] === 'all') {
      return "";
    }

    return ' ' . $this->periodExpression($params['period'], $column) . ' AS period,';
  }

  private function splitByPeriodWithoutAs(array $params)
  {
    $rawSql = "";
    $rawSql .= $params['whose'] !== 'all' && $params['period'] !== 'all' ? ", " : "";
    if ($params['period'] === 'all') {
      return $rawSql;
    }

    $rawSql .= ' ' . $this->periodExpression($params['period'], 'started_at');
    return $rawSql;
  }

  private function addPeriod(array $params, string $commaPrefix = '', string $commaPostfix = '')
  {
    return $params['period'] !== 'all' ? $commaPrefix . "period" . $commaPostfix : "";
  }

  private function addUserId(array $params, string $commaPrefix = '', string $commaPostfix = '')
  {
    return match ($params['whose']) {
      'all' => '',
      'dealer' => $commaPrefix . "id_user_dealer" . $commaPostfix,
      'dealer_dependents' => $commaPrefix . "id_user" . $commaPostfix,
      default => $commaPrefix . "id_user" . $commaPostfix,
    };
  }

  private function groupByColumns(array $params): string
  {
    $parts = array_filter([
      trim($this->addUserId($params)),
      'period',
    ]);

    return implode(', ', $parts);
  }

  private function orderByCounts(array $params): string
  {
    $userId = trim($this->addUserId($params));
    if ($userId === '') {
      return 'ORDER BY period';
    }

    return "ORDER BY period, {$userId}";
  }
}
