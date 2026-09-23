<?php
namespace PersonalAccount\Workers\Collector;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
class MessageCollector
{
  /** @var Container */
  private $container;
  /** @var DBWorker */
  private $dbWorker;
  /** @var CatcherBugs */
  private $catcherBugs;
  /** @var SimpleUtilities */
  private $simpleUtilities;
  /** @var DBUtilities */
  private $dbUtilities;
  /** @var DataUtilities */
  private $dataUtilities;
  public function __construct(object $Container)
  {
    $this->container = $Container;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->dataUtilities = $this->container->get('DataUtilities');
    
  }
  public function createTableAnalytics(string $nameTable = 'gi_new_metrics_messages')
  {
    if (!$this->dbWorker->checkExistanceTable($nameTable)) {
      $sqlCreate = "CREATE TABLE `$nameTable` (
        id INT NOT NULL AUTO_INCREMENT,
        id_dialog INT NOT NULL DEFAULT 0,
        id_message INT NOT NULL DEFAULT 0,
        id_author INT NOT NULL DEFAULT 0,
        message_body TEXT NOT NULL DEFAULT '',
        files TEXT NOT NULL DEFAULT '',
        date_send DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        date_readed DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id),
        KEY dialog_send (id_dialog, date_send), 
        KEY author_send (id_author, date_send)
        
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sqlCreate);
    } else {
      $this->dbWorker->dropTable($nameTable);
      $this->createTableAnalytics($nameTable);
      print_r('Table already exists');
    }
  }
  private function getMessages() : array
  {
    $defaultName = 'x_gi_new_messages_contract_';
    $firstYear = 2024;
    $lastYear = intval(date('Y'));
    $points = $this->dbWorker->selectSimple(table: 'gi_new_points', what: 'id_point');
    if (empty($points)) {
      return [];
    }
    if (!is_array($points)) {
      $points = [$points];
    }

    $tableQueries = [];
    for ($year = $firstYear; $year <= $lastYear; $year++) {
      foreach ($points as $point) {
        $nameTable = $defaultName . $year . '_' . $point;
        if ($this->dbWorker->checkExistanceTable($nameTable)) {
          $tableQueries[] = "SELECT * FROM `$nameTable`";
        }
      }
    }
    if (empty($tableQueries)) {
      return [];
    }

    $sql = implode(' UNION ALL ', $tableQueries) . ' ORDER BY date_send ASC';
    $messages = $this->dbWorker->getRawSQL($sql);
    return $messages ?? [];
  }
  private function insertMessages(array $messages) : void
  {
    $columns = $this->dbWorker->showColumnDB('gi_new_metrics_messages');
    $indexedMessages = $this->dataUtilities->convertToIndexArr($messages);
    $prepareData = $this->dbUtilities->prepareInsertTrust($indexedMessages, 1000);
    $this->dbWorker->insertMultipleTrustDB('gi_new_metrics_messages', $columns, $prepareData);
  }
  public function mergeMessages() : void
  {
    print_r("Merging messages \n");
    $this->createTableAnalytics();
    $messages = $this->getMessages();
    $fieldToRemove = 'id';
    array_walk($messages, function (&$item) use ($fieldToRemove) {
      if (is_array($item)) {
        unset($item[$fieldToRemove]);
      }
    });

    if (!empty($messages)) {
      $this->insertMessages($messages);
    }
    print_r("Messages merged \n");
  }

}