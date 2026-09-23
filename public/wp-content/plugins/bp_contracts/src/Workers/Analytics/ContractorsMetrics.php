<?php
namespace PersonalAccount\Workers\Analytics;
use PersonalAccount\Workers\Analytics\AnalyticsBase;
class ContractorsMetrics extends AnalyticsBase
{
  public function __construct(object $Container)
  {
    parent::__construct($Container);
  }

  public function createTableAnalytics(string $nameTable = 'gi_new_metrics_contractors')
  {
    if (!$this->dbWorker->checkExistanceTable($nameTable)) {
      $sqlCreate = "CREATE TABLE `$nameTable` (
        id INT NOT NULL AUTO_INCREMENT,
        id_dialog INT NOT NULL,

        id_contractor INT NOT NULL,
        id_user_dealer INT DEFAULT NULL,

        messages_count_contractors INT NOT NULL,
        time_waiting_first_response INT NOT NULL,
        time_waiting_first_response_at_work_hours INT NOT NULL,
        calculated_at DATETIME NOT NULL,
        PRIMARY KEY  (id),

        KEY idx_contractor (id_contractor),
        KEY idx_dealer_contractor (id_user_dealer, id_contractor)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sqlCreate);
    } else {
      $this->dbWorker->dropTable($nameTable);
      $this->createTableAnalytics($nameTable);
      print_r("Table $nameTable already exists \n");
    }
  }
  public function calculateContractorsAnalytics(array $messages, string $calculatedAt, array $turns): array
  {
    $result = [];
    $timeFirstResponse = isset($turns[2]) ? $turns[2]['timeFromPreviousTurn'] : 0;
    $timeFirstResponseAtWorkHours = isset($turns[2]) ? $turns[2]['timeFromPreviousTurnAtWorkHours'] : 0;
    foreach ($messages as $message) {
      $userSide = $this->userUtilities->checkUserAccess($message['idAuthor']);
      if ($userSide === 'contractor') {
        $idAuthor = $message['idAuthor'];
        if (!isset($result[$idAuthor])) {
          $idDealer = $this->userUtilities->getUserDealerId($idAuthor);
          $result[$idAuthor] = [
            'idDialog' => $message['idDialog'],
            'idContractor' => $idAuthor,
            'idDealer' => $idDealer !== 0 ? $idDealer : null,
            'messagesCount' => 0,
            'timeFirstResponse' => $timeFirstResponse,
            'timeFirstResponseAtWorkHours' => $timeFirstResponseAtWorkHours,
            'calculatedAt' => $calculatedAt,
          ];
        }
        $result[$idAuthor]['messagesCount']++;
        if ($result[$idAuthor]['idDealer'] === null) {
          print_r("idDealer is null \n");
          print_r($result[$idAuthor]);
        }
      }
    }
    return $result;
  }
  public function writeAnalyticsNightly(array $analytics, string $nameTable): void
  {
    foreach ($analytics as $item) {
      $condition = $this->dbUtilities->prepareEqualAndEqual($item['idDialog'], $item['idContractor'], 'id_dialog', 'id_contractor');
      $check = $this->dbWorker->selectUni_2($nameTable, $condition);
      if (!empty($check)) {
        $this->dbWorker->deleteComplexQueryDB($nameTable, $condition['sql'], $condition['values']);
      }
      $this->dbWorker->insertDBU($nameTable, $item);
    }
  }
}