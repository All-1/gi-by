<?php
namespace PersonalAccount\Workers\Collector;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\Analytics\DialogMetrics;
use PersonalAccount\Workers\Analytics\ManagerMetrics;
use PersonalAccount\Workers\Analytics\ContractorsMetrics;
use PersonalAccount\Workers\Analytics\MessageMetrics;

class AnalyticsCollector
{
  /** @var Container */
  private $container;
  /** @var DBWorker */
  private $dbWorker;
  /** @var DialogMetrics */
  private $dialogMetrics;
  /** @var ManagerMetrics */
  private $managerMetrics;
  /** @var ContractorsMetrics */
  private $contractorsMetrics;
  /** @var MessageMetrics */
  private $messageMetrics;
  public function __construct(object $Container)
  {
    $this->container = $Container;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->dialogMetrics = $this->container->get('DialogMetrics');
    $this->managerMetrics = $this->container->get('ManagerMetrics');
    $this->contractorsMetrics = $this->container->get('ContractorsMetrics');
    $this->messageMetrics = $this->container->get('MessageMetrics');
  }
  private function getMessages(int $idDialog) : array
  {
    $sql = "SELECT * FROM gi_new_metrics_messages WHERE id_dialog = %d ORDER BY date_send ASC";
    $params = [intval($idDialog)];
    $messages = $this->dbWorker->getRawSQL($sql, $params);
    return $messages ?? [];
  }
  private function getDialog(int $limit = 10000) : array
  {
    $params = [intval($limit)];
    $sql = "SELECT * FROM gi_new_dialogues ORDER BY id_dialog ASC LIMIT %d";
    $dialogs = $this->dbWorker->getRawSQL($sql, $params);
    return $dialogs;
  }
  private function getDialogNightly(string $lastCalculated) : array
  {
    $sql = "SELECT * FROM gi_new_dialogues WHERE date_last_activity > %s ORDER BY date_last_activity ASC";
    $params = [$lastCalculated];
    $dialogs = $this->dbWorker->getRawSQL($sql, $params);
    return $dialogs;
  }
  private function lastCalculatedStatistic() : string
  {
    $sql = "SELECT calculated_at FROM gi_new_metrics_dialogues ORDER BY calculated_at DESC LIMIT 1";
    $dateLastActivity = $this->dbWorker->getRawSQL($sql);
    return $dateLastActivity['calculatedAt'];
  }
  private function dateLastActivity() : string
  {
    $sql = "SELECT date_last_activity FROM gi_new_dialogues ORDER BY date_last_activity DESC LIMIT 1";
    $dateLastActivity = $this->dbWorker->getRawSQL($sql);
    
    return $dateLastActivity['dateLastActivity'];
  }
  private function calculateAnalytics(array $messages, string $calculatedAt) : array
  {
    $analytics = [];
    $analytics['turns'] = $this->messageMetrics->calculateTurnsAnalytics($messages, $calculatedAt);
    $analytics['dialog'] = $this->dialogMetrics->calculateDialogAnalytics($messages, $calculatedAt);
    $analytics['manager'] = $this->managerMetrics->calculateManagerAnalytics($messages, $calculatedAt);
    $analytics['contractors'] = $this->contractorsMetrics->calculateContractorsAnalytics($messages, $calculatedAt, $analytics['turns']);
    return $analytics;
  }
  public function getAnalytics(int $limit = 10000) : array
  {
    $calculatedAt = $this->dateLastActivity();
    $dialogues = $this->getDialog($limit);
    $analytics = $this->packAnalytics($dialogues, $calculatedAt);
    return $analytics;
  }
  public function getAnalyticsNightly() : array
  {
    $lastCalculated = $this->lastCalculatedStatistic();
    $calculatedAt = $this->dateLastActivity();
    $dialogues = $this->getDialogNightly($lastCalculated);
    $dialogues = isset($dialogues[0]) ? $dialogues : [$dialogues];
    $analytics = $this->packAnalytics($dialogues, $calculatedAt);
    return $analytics;
  }
  private function packAnalytics(array $dialogues, string $calculatedAt) : array
  {
    $analytics = [];
    foreach ($dialogues as $dialog) {
      $messages = $this->getMessages($dialog['idDialog']);
      if (empty($messages)) continue;
      $messages = isset($messages[0]) ? $messages : [$messages];
      $analytics[] = $this->calculateAnalytics($messages, $calculatedAt);
    }
    return $analytics;
  }
  public function writeAnalytics(array $analytics) : void
  {
    $dialogAnalytics = $this->dialogMetrics->repackDialogAnalytics($analytics);
    $managerAnalytics = $this->managerMetrics->repackAnalytics($analytics, 'manager');
    $contractorsAnalytics = $this->contractorsMetrics->repackAnalytics($analytics, 'contractors');
    $turnsAnalytics = $this->messageMetrics->repackAnalytics($analytics, 'turns');
    print_r("analytics \n");
    // print_r($analytics);
    print_r("turnsAnalytics \n");
    // print_r($turnsAnalytics);
    print_r("managerAnalytics \n");
    // print_r($managerAnalytics);
    print_r("contractorsAnalytics \n");
    // print_r($contractorsAnalytics);
    print_r("dialogAnalytics \n");
    // print_r($dialogAnalytics);
    $this->dialogMetrics->writeAnalytics($dialogAnalytics, 'gi_new_metrics_dialogues');
    $this->managerMetrics->writeAnalytics($managerAnalytics, 'gi_new_metrics_managers');
    $this->contractorsMetrics->writeAnalytics($contractorsAnalytics, 'gi_new_metrics_contractors');
    $this->messageMetrics->writeAnalytics($turnsAnalytics, 'gi_new_metrics_dialog_turns');
  }
  public function writeAnalyticsNightly(array $analytics) : void
  {
    $dialogAnalytics = $this->dialogMetrics->repackDialogAnalytics($analytics);
    $managerAnalytics = $this->managerMetrics->repackAnalytics($analytics, 'manager');
    $contractorsAnalytics = $this->contractorsMetrics->repackAnalytics($analytics, 'contractors');
    $turnsAnalytics = $this->messageMetrics->repackAnalytics($analytics, 'turns');
    print_r("dialogAnalytics \n");
    // print_r($dialogAnalytics);
    print_r("managerAnalytics \n");
    // print_r($managerAnalytics);
    print_r("contractorsAnalytics \n");
    // print_r($contractorsAnalytics);
    print_r("turnsAnalytics \n");
    // print_r($turnsAnalytics);
    $this->dialogMetrics->writeAnalyticsNightly($dialogAnalytics, 'gi_new_metrics_dialogues');
    $this->managerMetrics->writeAnalyticsNightly($managerAnalytics, 'gi_new_metrics_managers');
    $this->contractorsMetrics->writeAnalyticsNightly($contractorsAnalytics, 'gi_new_metrics_contractors');
    $this->messageMetrics->writeAnalyticsNightly($turnsAnalytics, 'gi_new_metrics_dialog_turns');
  }
}