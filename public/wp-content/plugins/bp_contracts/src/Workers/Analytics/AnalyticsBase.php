<?php
namespace PersonalAccount\Workers\Analytics;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\DBUtilities;
abstract class AnalyticsBase
{
  /** @var Container */
  protected $container;
  /** @var DBWorker */
  protected $dbWorker;
  /** @var CatcherBugs */
  protected $catcherBugs;
  /** @var SimpleUtilities */
  protected $simpleUtilities;
  /** @var DBUtilities */
  protected $dbUtilities;
  /** @var DataUtilities */
  protected $dataUtilities;
  /** @var UserUtilities */
  protected $userUtilities;
  public function __construct(object $Container)
  {
    $this->container = $Container;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->userUtilities = $this->container->get('UserUtilities');
  }
  public function writeAnalytics(array $analytics, string $nameTable, int $batchSize = 1000) : void
  {
    $columns = $this->dbWorker->showColumnDB($nameTable);
    $indexedAnalytics = $this->dataUtilities->convertToIndexArr($analytics);
    $prepareData = $this->dbUtilities->prepareInsertTrust($indexedAnalytics, $batchSize);
    $this->dbWorker->insertMultipleTrustDB($nameTable, $columns, $prepareData);
  }
  public function repackAnalytics(array $data, string $key) : array
  {
    $result = [];
    foreach ($data as $item) {
      foreach ($item[$key] as $value) {
        $result[] = $value;
      }
    }
    return $result;
  }
}