<?php
namespace PersonalAccount\Workers\Analytics;

class DialogAnalyticsQueries extends AnalyticsQueriesBase
{
  public function queryDialogStats(array $params): array
  {
    $openedStats = $this->getOpenedDialogsStats($params);
    $openedByTypeStats = $this->getOpenedDialogsByTypeStats($params);
    $activeStats = $this->getActiveDialogsStats($params);
    return $this->mergeDialogStats($openedStats, $activeStats, $openedByTypeStats);
  }

  private function getOpenedDialogsStats(array $params)
  {
    $openedPeriod = $this->dialogPeriodExpression($params, 'd.date_creation');
    $userSelect = $this->dialogUserSelect($params);
    $userResultSelect = $this->dialogUserResultSelect($params);
    $userJoin = $this->dialogUserJoin($params);
    $userGroup = $this->dialogUserGroup($params);
    $filterConditions = $this->dialogFilterConditions($params, 'd');
    $dialogDuration = $this->durationColumn($params, 'md.dialog_duration');
    $rawSql = "WITH opened_base AS (
        SELECT
          d.id_dialog,
          {$userSelect}
          {$openedPeriod} AS period,
          {$dialogDuration} AS dialog_duration,
          md.messages_count
        FROM gi_new_dialogues d
        JOIN gi_new_metrics_dialogues md ON md.id_dialog = d.id_dialog
        {$userJoin}
        WHERE d.date_creation BETWEEN '{$params['dateFrom']}' AND '{$params['dateTo']}'
        {$filterConditions}
      ),
      ranked AS (
        SELECT
          opened_base.*,
          CUME_DIST() OVER (PARTITION BY period{$userGroup} ORDER BY dialog_duration) AS duration_cd,
          CUME_DIST() OVER (PARTITION BY period{$userGroup} ORDER BY messages_count) AS messages_cd
        FROM opened_base
      ),
      opened_stats AS (
        SELECT
          period,
          {$userResultSelect}
          COUNT(DISTINCT id_dialog) AS opened_dialogs,
          MIN(CASE WHEN duration_cd >= 0.5 THEN dialog_duration END) AS dialog_duration_p50,
          MIN(CASE WHEN duration_cd >= 0.9 THEN dialog_duration END) AS dialog_duration_p90,
          MIN(CASE WHEN duration_cd >= 0.95 THEN dialog_duration END) AS dialog_duration_p95,
          MIN(CASE WHEN messages_cd >= 0.5 THEN messages_count END) AS messages_count_p50,
          MIN(CASE WHEN messages_cd >= 0.9 THEN messages_count END) AS messages_count_p90,
          MIN(CASE WHEN messages_cd >= 0.95 THEN messages_count END) AS messages_count_p95
        FROM ranked
        GROUP BY period{$userGroup}
      )
      SELECT
        period,
        {$userResultSelect}
        opened_dialogs,
        dialog_duration_p50,
        dialog_duration_p90,
        dialog_duration_p95,
        messages_count_p50,
        messages_count_p90,
        messages_count_p95
      FROM opened_stats
      ORDER BY period{$userGroup}";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  private function getOpenedDialogsByTypeStats(array $params)
  {
    $openedPeriod = $this->dialogPeriodExpression($params, 'd.date_creation');
    $userSelect = $this->dialogUserSelect($params);
    $userJoin = $this->dialogUserJoin($params);
    $userGroup = $this->dialogUserGroup($params);
    $filterConditions = $this->dialogFilterConditions($params, 'd');

    $rawSql = "SELECT
        {$openedPeriod} AS period,
        {$userSelect}
        d.type_dialog,
        COUNT(DISTINCT d.id_dialog) AS opened_dialogs
      FROM gi_new_dialogues d
      JOIN gi_new_metrics_dialogues md ON md.id_dialog = d.id_dialog
      {$userJoin}
      WHERE d.date_creation BETWEEN '{$params['dateFrom']}' AND '{$params['dateTo']}'
      {$filterConditions}
      GROUP BY period{$userGroup}, d.type_dialog
      ORDER BY period{$userGroup}, d.type_dialog";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  private function getActiveDialogsStats(array $params): array
  {
    $activePeriod = $this->dialogPeriodExpression($params, 'd.date_last_activity');
    $userSelect = $this->dialogUserSelect($params);
    $userJoin = $this->dialogUserJoin($params);
    $userGroup = $this->dialogUserGroup($params);
    $filterConditions = $this->dialogFilterConditions($params, 'd');

    $rawSql = "SELECT
        {$activePeriod} AS period,
        {$userSelect}
        COUNT(DISTINCT d.id_dialog) AS active_dialogs
      FROM gi_new_dialogues d
      JOIN gi_new_metrics_dialogues md ON md.id_dialog = d.id_dialog
      {$userJoin}
      WHERE d.date_last_activity BETWEEN '{$params['dateFrom']}' AND '{$params['dateTo']}'
      {$filterConditions}
      GROUP BY period{$userGroup}
      ORDER BY period{$userGroup}";
    $results = $this->dbWorker->getRawSQL($rawSql);
    return $this->normalizeSqlResults($results);
  }

  private function dialogUserSelect(array $params)
  {
    return match ($params['whose']) {
      'factory' => "mm.id_manager,",
      'contractor', 'dealer_dependents' => "mc.id_contractor,",
      'dealer' => "mc.id_user_dealer,",
      default => "",
    };
  }

  private function dialogUserResultSelect(array $params)
  {
    return match ($params['whose']) {
      'factory' => "id_manager,",
      'contractor', 'dealer_dependents' => "id_contractor,",
      'dealer' => "id_user_dealer,",
      default => "",
    };
  }

  private function dialogUserJoin(array $params)
  {
    return match ($params['whose']) {
      'factory' => "JOIN gi_new_metrics_managers mm ON mm.id_dialog = d.id_dialog",
      'contractor', 'dealer', 'dealer_dependents' => "JOIN gi_new_metrics_contractors mc ON mc.id_dialog = d.id_dialog",
      default => "",
    };
  }

  private function dialogUserGroup(array $params)
  {
    return match ($params['whose']) {
      'factory' => ", id_manager",
      'contractor', 'dealer_dependents' => ", id_contractor",
      'dealer' => ", id_user_dealer",
      default => "",
    };
  }

  private function dialogFilterConditions(array $params, string $dialogAlias)
  {
    $rawSql = $params['dialoguesType'] ? " AND {$dialogAlias}.type_dialog = '{$params['dialoguesType']}'" : "";
    if ($params['whose'] === 'dealer_dependents') {
      $dealerUserId = intval($params['filterUserId'] ?? 0);
      if ($dealerUserId > 0) {
        $rawSql .= " AND mc.id_user_dealer = {$dealerUserId}";
      }
      return $rawSql;
    }

    if (!$params['idUser']) {
      return $rawSql;
    }

    if ($params['whose'] === 'factory') {
      $rawSql .= " AND mm.id_manager = {$params['idUser']}";
    } elseif ($params['whose'] === 'contractor') {
      $rawSql .= " AND mc.id_contractor = {$params['idUser']}";
    } elseif ($params['whose'] === 'dealer') {
      $rawSql .= " AND mc.id_user_dealer = {$params['idUser']}";
    }

    return $rawSql;
  }

  private function mergeDialogStats($openedStats, $activeStats, $openedByTypeStats)
  {
    $result = [];
    $openedStats = empty($openedStats) ? [] : (isset($openedStats[0]) ? $openedStats : [$openedStats]);
    $activeStats = empty($activeStats) ? [] : (isset($activeStats[0]) ? $activeStats : [$activeStats]);
    $openedByTypeStats = empty($openedByTypeStats) ? [] : (isset($openedByTypeStats[0]) ? $openedByTypeStats : [$openedByTypeStats]);

    foreach ($openedStats as $row) {
      $key = $this->dialogStatsKey($row);
      $result[$key] = $row;
      $result[$key]['activeDialogs'] = 0;
      $result[$key]['openedDialogsByType'] = [];
    }

    foreach ($activeStats as $row) {
      $key = $this->dialogStatsKey($row);
      if (!isset($result[$key])) {
        $result[$key] = $this->emptyDialogStatsRow($row);
      }
      $result[$key]['activeDialogs'] = $row['activeDialogs'];
    }

    foreach ($openedByTypeStats as $row) {
      $key = $this->dialogStatsKey($row);
      if (!isset($result[$key])) {
        $result[$key] = $this->emptyDialogStatsRow($row);
      }
      $result[$key]['openedDialogsByType'][] = [
        'typeDialog' => $row['typeDialog'],
        'openedDialogs' => $row['openedDialogs'],
      ];
    }

    ksort($result);
    return array_values($result);
  }

  private function dialogStatsKey(array $row)
  {
    $userId = $row['idManager'] ?? $row['idContractor'] ?? $row['idUserDealer'] ?? $row['idDealer'] ?? $row['idUser'] ?? '';
    return $row['period'] . '|' . $userId;
  }

  private function emptyDialogStatsRow(array $row)
  {
    $result = [
      'period' => $row['period'],
      'openedDialogs' => 0,
      'activeDialogs' => 0,
      'dialogDurationP50' => null,
      'dialogDurationP90' => null,
      'dialogDurationP95' => null,
      'messagesCountP50' => null,
      'messagesCountP90' => null,
      'messagesCountP95' => null,
      'openedDialogsByType' => [],
    ];

    foreach (['idManager', 'idContractor', 'idDealer', 'idUserDealer', 'idUser'] as $userKey) {
      if (isset($row[$userKey])) {
        $result[$userKey] = $row[$userKey];
      }
    }

    return $result;
  }

  private function dialogPeriodExpression(array $params, string $column)
  {
    return $this->periodExpression($params['period'], $column);
  }

  public function queryDealerDependentsCounts(array $params): array
  {
    $filterConditions = $params['dialoguesType']
      ? " AND d.type_dialog = '{$params['dialoguesType']}'"
      : '';

    $rawSql = "SELECT
        mc.id_user_dealer AS idUserDealer,
        COUNT(DISTINCT mc.id_contractor) AS dependentsCount
      FROM gi_new_dialogues d
      JOIN gi_new_metrics_contractors mc ON mc.id_dialog = d.id_dialog
      WHERE d.date_creation BETWEEN '{$params['dateFrom']}' AND '{$params['dateTo']}'
        AND mc.id_user_dealer IS NOT NULL
        AND mc.id_contractor IS NOT NULL
        AND mc.id_contractor <> mc.id_user_dealer
        {$filterConditions}
      GROUP BY mc.id_user_dealer
      HAVING dependentsCount > 0";

    return $this->normalizeSqlResults($this->dbWorker->getRawSQL($rawSql));
  }
}
