<?php
namespace PersonalAccount\Users\traits;

use PersonalAccount\Core\Container;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Workers\Analytics\AnalyticsWorker;

trait ManageAnalytics
{
  protected $whose = 'all';
  protected int $idUserAnalytics = 0;
  protected string $dialoguesType = '';
  protected string $dateFrom = '';
  protected string $dateTo = '';
  protected string $userSearch = '';
  protected array $users = [];
  protected string $period = 'all';
  protected string $timeMode = 'work_hours';
  protected int $slaFirstResponseMinutes = 180;
  protected int $slaSubsequentResponseMinutes = 120;

  public function getAnalytics()
  {
    $this->dateFrom = empty($this->dateFrom) ? $this->SimpleUtilities->previousDateTime(120) : $this->dateFrom;
    $this->dateTo = empty($this->dateTo) ? $this->SimpleUtilities->currentTime() : $this->dateTo;
    // $this->CatcherBugs->convPrintLog($this->dateFrom, 'dateFrom', 'dateFrom');
    // $this->CatcherBugs->convPrintLog($this->dateTo, 'dateTo', 'dateTo');
    $params = [
      'idUser' => $this->idUserAnalytics,
      'dialoguesType' => $this->dialoguesType,
      'dateFrom' => $this->dateFrom,
      'dateTo' => $this->dateTo,
      'period' => $this->period,
      'whose' => $this->whose,
      'timeMode' => $this->timeMode,
      'slaFirstResponseSeconds' => $this->slaFirstResponseMinutes * 60,
      'slaSubsequentResponseSeconds' => $this->slaSubsequentResponseMinutes * 60,
    ];
    // $this->CatcherBugs->convPrintLog($params, 'getAnalytics', 'params');
    $this->ensureAnalyticsUsersCollected();
    $response = $this->getResponse($params);

    // $this->CatcherBugs->convPrintLog($response, 'response', 'response');
    return $response;
  }

  private function ensureAnalyticsUsersCollected(): void
  {
    if (!empty($this->users)) {
      return;
    }
    $this->users = [
      'factory' => $this->userCollector->getFactoryUsers(),
      'contractor' => $this->userCollector->getContractorUsers(),
      'dealer' => $this->userCollector->getDealerUsers(),
      'all' => [],
    ];
  }

  private function getResponse(array $params): array
  {
    $response = match ($this->whose) {
      'factory' => $this->AnalyticsWorker->getAnalyticsFactory($params),
      'contractor' => $this->AnalyticsWorker->getAnalyticsContractor($params),
      'all' => $this->AnalyticsWorker->getAnalyticsAll($params),
      'dealer' => $this->AnalyticsWorker->getAnalyticsContractor($params)
    };

    if ($this->whose === 'all') {
      return $response;
    }

    if ($this->whose === 'dealer') {
      $response['dealerDependentsByDealer'] = $this->buildDealerDependentsByDealerMap($params);
    }

    $response['analyticsUsers'] = $this->getAnalyticsUsersCatalog($this->users[$this->whose], $params);
    return $response;
  }

  private function buildDealerDependentsByDealerMap(array $params): array
  {
    $rows = $this->AnalyticsWorker->getDealerDependentsCounts($params);
    $map = [];
    foreach ($rows as $row) {
      if (!is_array($row)) {
        continue;
      }
      $dealerUserId = intval($row['idUserDealer'] ?? $row['id_user_dealer'] ?? 0);
      $dependentsCount = intval($row['dependentsCount'] ?? $row['dependents_count'] ?? 0);
      if ($dealerUserId > 0 && $dependentsCount > 0) {
        $map[$dealerUserId] = $dependentsCount;
      }
    }
    return $map;
  }

  public function getDealerDependentsAnalytics($body)
  {
    $payload = is_object($body) ? $body : json_decode($body ?: '{}');
    $dealerUserId = intval(is_object($payload) ? ($payload->dealerUserId ?? 0) : 0);
    if ($dealerUserId <= 0) {
      return ['error' => 'invalid dealer'];
    }

    $this->dateFrom = empty($this->dateFrom) ? $this->SimpleUtilities->previousDateTime(120) : $this->dateFrom;
    $this->dateTo = empty($this->dateTo) ? $this->SimpleUtilities->currentTime() : $this->dateTo;
    $this->ensureAnalyticsUsersCollected();

    $params = [
      'idUser' => 0,
      'filterUserId' => $dealerUserId,
      'dialoguesType' => $this->dialoguesType,
      'dateFrom' => $this->dateFrom,
      'dateTo' => $this->dateTo,
      'period' => $this->period,
      'whose' => 'dealer_dependents',
      'timeMode' => $this->timeMode,
      'slaFirstResponseSeconds' => $this->slaFirstResponseMinutes * 60,
      'slaSubsequentResponseSeconds' => $this->slaSubsequentResponseMinutes * 60,
    ];

    $response = $this->AnalyticsWorker->getAnalyticsContractor($params);
    $response['analyticsUsers'] = $this->users['contractor'] ?? [];
    $dealer = $this->users['dealer'][$dealerUserId]
      ?? $this->users['dealer'][(string) $dealerUserId]
      ?? null;
    $dealerName = '';
    if (is_array($dealer)) {
      $dealerName = $dealer['dealerName'] ?? $dealer['name'] ?? '';
    }
    $response['dealerDependentsContext'] = [
      'dealerUserId' => $dealerUserId,
      'dealerName' => $dealerName,
    ];
    return $response;
  }

  private function getAnalyticsUsersCatalog(array $users, array $params): array
  {
    if (empty($params['idUser'])) {
      return $users;
    }

    $idUser = intval($params['idUser']);
    if ($idUser <= 0) {
      return $users;
    }

    if (isset($users[$idUser])) {
      return [$idUser => $users[$idUser]];
    }

    if (isset($users[(string) $idUser])) {
      return [(string) $idUser => $users[(string) $idUser]];
    }

    foreach ($users as $key => $user) {
      if (!is_array($user)) {
        continue;
      }
      if (isset($user['id']) && intval($user['id']) === $idUser) {
        return [$key => $user];
      }
    }

    return $users;
  }

  public function setAnalyticsWhose(string $body)
  {
    $this->whose = $body;
    $this->idUserAnalytics = 0;
    $this->userSearch = '';
  }
  public function setAnalyticsDialoguesType(string $body)
  {
    // $this->CatcherBugs->convPrintLog($body, 'setAnalyticsDialoguesType', 'body');
    $this->dialoguesType = $body;
    // $this->CatcherBugs->convPrintLog($this->dialoguesType, 'setAnalyticsDialoguesType', 'dialoguesType');
  }
  public function getAnalyticsUserSearch(string $body)
  {
    $this->ensureAnalyticsUsersCollected();
    $this->userSearch = $body;
    $response = array_filter($this->users[$this->whose], function ($user) use ($body) {
      return mb_stripos($user['search'], $body, 0, 'UTF-8') !== false;
    });
    // $this->CatcherBugs->convPrintLog($response, 'getAnalyticsUserSearch', 'response');
    return $response;
  }
  public function setAnalyticsUser(string $body)
  {
    $this->idUserAnalytics = !empty($body) ? intval($body) : 0;
    // $this->CatcherBugs->convPrintLog($this->idUserAnalytics, 'setAnalyticsUser', 'idUserAnalytics');
  }
  public function setStartDateAnalytics(string $body)
  {
    $this->dateFrom = $body;
    // $this->CatcherBugs->convPrintLog($this->dateFrom, 'setStartDateAnalytics', 'dateFrom');
  }
  public function setEndDateAnalytics(string $body)
  {
    $this->dateTo = $body;
    // $this->CatcherBugs->convPrintLog($this->dateTo, 'setEndDateAnalytics', 'dateTo');
  }
  public function setAnalyticsPeriod(string $body)
  {
    $this->period = $body;
    // $this->CatcherBugs->convPrintLog($this->period, 'setPeriodAnalytics', 'period');
  }
  public function setAnalyticsTimeMode(string $body)
  {
    $this->timeMode = $body === 'standard' ? 'standard' : 'work_hours';
    // $this->CatcherBugs->convPrintLog($this->timeMode, 'setAnalyticsTimeMode', 'timeMode');
  }
  public function setAnalyticsSlaFirstResponse(string $body)
  {
    $this->slaFirstResponseMinutes = intval($body);
    // $this->CatcherBugs->convPrintLog($this->slaFirstResponseMinutes, 'setAnalyticsSlaFirstResponse', 'slaFirstResponseMinutes');
  }
  public function setAnalyticsSlaSubsequentResponse(string $body)
  {
    $this->slaSubsequentResponseMinutes = intval($body);
    // $this->CatcherBugs->convPrintLog($this->slaSubsequentResponseMinutes, 'setAnalyticsSlaSubsequentResponse', 'slaSubsequentResponseMinutes');
  }
}
