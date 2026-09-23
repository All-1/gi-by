<?php
namespace PersonalAccount\Workers\Analytics;
use PersonalAccount\Workers\Analytics\AnalyticsBase;
class ManagerMetrics extends AnalyticsBase
{
  public function __construct(object $Container)
  {
    parent::__construct($Container);
  }
  public function createTableAnalytics(string $nameTable = 'gi_new_metrics_managers')
  {
    if (!$this->dbWorker->checkExistanceTable($nameTable)) {
      $sqlCreate = "CREATE TABLE `$nameTable` (
        id INT NOT NULL AUTO_INCREMENT,
        id_dialog INT NOT NULL,

        id_manager INT NOT NULL,
        messages_count_managers INT NOT NULL,

        calculated_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        -- FOREIGN KEY (id_dialog) REFERENCES gi_new_dialogues(id_dialog),
        -- FOREIGN KEY (id_manager) REFERENCES gi_new_users(id_user),

        KEY idx_manager (id_manager)

      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sqlCreate);
    } else {
      $this->dbWorker->dropTable($nameTable);
      $this->createTableAnalytics($nameTable);
      print_r("Table $nameTable already exists \n");
    }
  }

  public function calculateManagerAnalytics(array $messages, string $calculatedAt): array
  {
    $result = [];
    foreach ($messages as $message) {
      $userSide = $this->userUtilities->checkUserAccess($message['idAuthor']);
      if ($userSide === 'factory_worker') {
        $idAuthor = $message['idAuthor'];
        if (!isset($result[$idAuthor])) {
          $result[$idAuthor] = [
            'idDialog' => $message['idDialog'],
            'idManager' => $idAuthor,
            'messagesCount' => 0,
            'calculatedAt' => $calculatedAt,
          ];
        }
        $result[$idAuthor]['messagesCount']++;
      }
    }
    return $result;
  }
  public function writeAnalyticsNightly(array $analytics, string $nameTable): void
  {
    foreach ($analytics as $item) {
      $condition = $this->dbUtilities->prepareEqualAndEqual($item['idDialog'], $item['idManager'], 'id_dialog', 'id_manager');
      $check = $this->dbWorker->selectUni_2($nameTable, $condition);
      if (!empty($check)) {
        $this->dbWorker->deleteComplexQueryDB($nameTable, $condition['sql'], $condition['values']);
      }
      $this->dbWorker->insertDBU($nameTable, $item);
    }
  }
}