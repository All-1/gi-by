<?php
namespace PersonalAccount\Workers\Analytics;
use PersonalAccount\Workers\Analytics\AnalyticsBase;
class DialogMetrics extends AnalyticsBase
{
  public function __construct(object $Container)
  {
    parent::__construct($Container);
  }
  public function createTableAnalytics(string $nameTable = 'gi_new_metrics_dialogues')
  {
    if (!$this->dbWorker->checkExistanceTable($nameTable)) {
      $sqlCreate = "CREATE TABLE `$nameTable` (
        id_dialog INT NOT NULL,
      
        messages_count INT NOT NULL,
        dialog_duration INT NOT NULL,
        dialog_duration_at_work_hours INT NOT NULL,

        calculated_at DATETIME NOT NULL,
        PRIMARY KEY  (id_dialog)
        -- FOREIGN KEY (id_dialog) REFERENCES gi_new_dialogues(id_dialog)

      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sqlCreate);
    } else {
      $this->dbWorker->dropTable($nameTable);
      $this->createTableAnalytics($nameTable);
      print_r("Table $nameTable already exists \n");
    }
  }
  public function calculateDialogAnalytics(array $messages, string $calculatedAt) : array
  {
    $result = [];
    $result['idDialog'] = $messages[0]['idDialog'];
    $result['messagesCount'] = count($messages);
    $result['dialogDuration'] = $this->simpleUtilities->diffTimeNew($messages[count($messages) - 1]['dateSend'], $messages[0]['dateSend']);
    $result['dialogDurationAtWorkHours'] = $this->simpleUtilities->diffWorkHours($messages[0]['dateSend'], $messages[count($messages) - 1]['dateSend']);
    $result['calculatedAt'] = $calculatedAt;
    return $result;
  }
  public function repackDialogAnalytics(array $data): array
  {
    $result = [];
    foreach ($data as $item) {
      $result[] = $item['dialog'];
    }
    return $result;
  }
  public function writeAnalyticsNightly($analytics, $nameTable)
  {
    foreach ($analytics as $item) {
      $check = $this->dbWorker->selectSimple($nameTable, 'id_dialog', $item['idDialog']);
      if (!empty($check)) {
        $condition = $this->dbUtilities->preparenSingleOperSepar('id_dialog', $item['idDialog'], ' = ', '');
        $this->dbWorker->deleteComplexQueryDB($nameTable, $condition['sql'], $condition['values']);
      }
      $this->dbWorker->insertDBU($nameTable, $item);
    }
  }
}