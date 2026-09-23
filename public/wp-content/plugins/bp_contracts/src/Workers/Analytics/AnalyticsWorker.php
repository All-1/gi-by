<?php
namespace PersonalAccount\Workers\Analytics;

use PersonalAccount\Core\Container;

class AnalyticsWorker
{
  /** @var Container */
  private $container;
  /** @var TurnAnalyticsQueries */
  private $turnAnalyticsQueries;
  /** @var DialogAnalyticsQueries */
  private $dialogAnalyticsQueries;
  /** @var OrderAnalyticsQueries */
  private $orderAnalyticsQueries;

  public function __construct(object $Container)
  {
    $this->container = $Container;
    $this->turnAnalyticsQueries = $this->container->get('TurnAnalyticsQueries');
    $this->dialogAnalyticsQueries = $this->container->get('DialogAnalyticsQueries');
    $this->orderAnalyticsQueries = $this->container->get('OrderAnalyticsQueries');
  }

  public function getAnalyticsFactory(array $params)
  {
    $params = $this->applyAnalyticsUserFilter($params);

    return array_merge(
      $this->buildFactoryTurnMetrics($params),
      ['dataDialogs' => $this->dialogAnalyticsQueries->queryDialogStats($params)]
    );
  }

  public function getAnalyticsContractor(array $params)
  {
    $params = $this->applyAnalyticsUserFilter($params);

    $turn = $this->turnAnalyticsQueries;
    $beforeNextField = $this->turnAnalyticsQueries->durationColumn($params, 'time_before_next_turn');

    $params['filterFieldValue'] = 1;
    $params['filterField'] = 'from_message_id';
    $params['filterCondition'] = '=';
    $params['field'] = $beforeNextField;
    $timeManagerFirstResponseStats = $turn->queryTimeResponseStats($params);
    $timeManagerFirstResponse50 = $turn->pickPercentileColumn($timeManagerFirstResponseStats, 'p50');
    $timeManagerFirstResponse90 = $turn->pickPercentileColumn($timeManagerFirstResponseStats, 'p90');
    $timeManagerFirstResponse95 = $turn->pickPercentileColumn($timeManagerFirstResponseStats, 'p95');
    $maxTimeManagerFirstResponse = $turn->pickPercentileColumn($timeManagerFirstResponseStats, 'maxTimeResponse');
    $params['slaThresholdSeconds'] = $params['slaFirstResponseSeconds'] ?? 900;
    $timeManagerFirstResponseSla = $turn->querySlaStats($params);

    $params['filterCondition'] = '>';
    $timeManagerResponseStats = $turn->queryTimeResponseStats($params);
    $timeManagerResponse50 = $turn->pickPercentileColumn($timeManagerResponseStats, 'p50');
    $timeManagerResponse90 = $turn->pickPercentileColumn($timeManagerResponseStats, 'p90');
    $timeManagerResponse95 = $turn->pickPercentileColumn($timeManagerResponseStats, 'p95');
    $params['slaThresholdSeconds'] = $params['slaSubsequentResponseSeconds'] ?? 3600;
    $timeManagerResponseSla = $turn->querySlaStats($params);

    unset($params['filterCondition']);
    unset($params['filterFieldValue']);
    unset($params['filterField']);
    unset($params['slaThresholdSeconds']);
    $params['field'] = $beforeNextField;
    $timeContractorFirstResponseStats = $turn->queryTimeResponseStats($params);
    $timeContractorFirstResponse50 = $turn->pickPercentileColumn($timeContractorFirstResponseStats, 'p50');
    $timeContractorFirstResponse90 = $turn->pickPercentileColumn($timeContractorFirstResponseStats, 'p90');
    $timeContractorFirstResponse95 = $turn->pickPercentileColumn($timeContractorFirstResponseStats, 'p95');

    return [
      'timeManagerFirstResponse50' => $timeManagerFirstResponse50,
      'timeManagerFirstResponse90' => $timeManagerFirstResponse90,
      'timeManagerFirstResponse95' => $timeManagerFirstResponse95,
      'maxTimeManagerFirstResponse' => $maxTimeManagerFirstResponse,
      'timeManagerResponse50' => $timeManagerResponse50,
      'timeManagerResponse90' => $timeManagerResponse90,
      'timeManagerResponse95' => $timeManagerResponse95,
      'timeContractorFirstResponse50' => $timeContractorFirstResponse50,
      'timeContractorFirstResponse90' => $timeContractorFirstResponse90,
      'timeContractorFirstResponse95' => $timeContractorFirstResponse95,
      'timeManagerFirstResponseSla' => $timeManagerFirstResponseSla,
      'timeManagerResponseSla' => $timeManagerResponseSla,
      'dataDialogs' => $this->dialogAnalyticsQueries->queryDialogStats($params),
      'dataTurns' => $this->turnAnalyticsQueries->queryTurnCounts($params),
      'dataOrders' => $this->orderAnalyticsQueries->queryOrderStats($params),
    ];
  }

  public function getAnalyticsAll(array $params)
  {
    return array_merge(
      $this->buildFactoryTurnMetrics($params),
      ['dataDialogs' => $this->dialogAnalyticsQueries->queryDialogStats($params)]
    );
  }

  private function buildFactoryTurnMetrics(array $params): array
  {
    $turn = $this->turnAnalyticsQueries;
    $fromPreviousField = $this->turnAnalyticsQueries->durationColumn($params, 'time_from_previous_turn');
    $beforeNextField = $this->turnAnalyticsQueries->durationColumn($params, 'time_before_next_turn');

    $params['filterFieldValue'] = 1;
    $params['filterField'] = 'is_first_response';
    $params['filterCondition'] = '=';
    $params['field'] = $fromPreviousField;
    $timeFirstResponseStats = $turn->queryTimeResponseStats($params);
    $timeFirstResponse50 = $turn->pickPercentileColumn($timeFirstResponseStats, 'p50');
    $timeFirstResponse90 = $turn->pickPercentileColumn($timeFirstResponseStats, 'p90');
    $timeFirstResponse95 = $turn->pickPercentileColumn($timeFirstResponseStats, 'p95');
    $maxTimeFirstResponse = $turn->pickPercentileColumn($timeFirstResponseStats, 'maxTimeResponse');
    $params['slaThresholdSeconds'] = $params['slaFirstResponseSeconds'] ?? 900;
    $timeFirstResponseSla = $turn->querySlaStats($params);

    $params['filterFieldValue'] = 0;
    $timeResponseStats = $turn->queryTimeResponseStats($params);
    $timeResponse50 = $turn->pickPercentileColumn($timeResponseStats, 'p50');
    $timeResponse90 = $turn->pickPercentileColumn($timeResponseStats, 'p90');
    $timeResponse95 = $turn->pickPercentileColumn($timeResponseStats, 'p95');
    $params['slaThresholdSeconds'] = $params['slaSubsequentResponseSeconds'] ?? 3600;
    $timeResponseSla = $turn->querySlaStats($params);

    $params['field'] = $beforeNextField;
    unset($params['filterFieldValue']);
    unset($params['filterField']);
    unset($params['filterCondition']);
    unset($params['slaThresholdSeconds']);
    $timeBeforeNextTurnStats = $turn->queryTimeResponseStats($params);
    $timeBeforeNextTurn50 = $turn->pickPercentileColumn($timeBeforeNextTurnStats, 'p50');
    $timeBeforeNextTurn90 = $turn->pickPercentileColumn($timeBeforeNextTurnStats, 'p90');
    $timeBeforeNextTurn95 = $turn->pickPercentileColumn($timeBeforeNextTurnStats, 'p95');

    return [
      'timeFirstResponse50' => $timeFirstResponse50,
      'timeFirstResponse90' => $timeFirstResponse90,
      'timeFirstResponse95' => $timeFirstResponse95,
      'maxTimeFirstResponse' => $maxTimeFirstResponse,
      'timeResponse50' => $timeResponse50,
      'timeResponse90' => $timeResponse90,
      'timeResponse95' => $timeResponse95,
      'timeBeforeNextTurn50' => $timeBeforeNextTurn50,
      'timeBeforeNextTurn90' => $timeBeforeNextTurn90,
      'timeBeforeNextTurn95' => $timeBeforeNextTurn95,
      'timeFirstResponseSla' => $timeFirstResponseSla,
      'timeResponseSla' => $timeResponseSla,
    ];
  }

  public function getDealerDependentsCounts(array $params): array
  {
    return $this->dialogAnalyticsQueries->queryDealerDependentsCounts($params);
  }

  private function applyAnalyticsUserFilter(array $params): array
  {
    if (($params['whose'] ?? '') === 'dealer_dependents' && !empty($params['filterUserId'])) {
      $params['filterUserId'] = intval($params['filterUserId']);
      return $params;
    }

    if (empty($params['idUser'])) {
      return $params;
    }

    $params['filterUserId'] = intval($params['idUser']);
    return $params;
  }
}
