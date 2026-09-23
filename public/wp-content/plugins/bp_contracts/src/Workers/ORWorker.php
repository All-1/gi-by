<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use Exception;

class ORWorker
{
  /** @var Container */
  private $Container;
  /** @var DBUtilities */
  private $DBUtilities;
  /** @var SimpleUtilities */
  private $SimpleUtilities;
  /** @var CatcherBugs */
  private $CatcherBugs;
  /** @var DBWorker */
  private $DBWorker;
  /** @var Utilit */
  private $Utilit;
  public function __construct($Container)
  {
    $this->Container = $Container;
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
    $this->DBUtilities = $this->Container->get('DBUtilities');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
    $this->DBWorker = $this->Container->get('DBWorker');
    $this->Utilit = $this->Container->get('Utilit');
  }
  public function deleteOR($serialNumber, $type = 'Contract')
  {
    $objectSql = $this->DBWorker->selectSimple($type, 'sn', $serialNumber);
    if (!empty($objectSql)) {
      if (strtotime($objectSql['dateLastActivity']) === strtotime($objectSql['dateCreation'])) {
        // $this->CatcherBugs->convPrintLog($objectSql, 'deleteOR', '$objectSql');
        $this->DBWorker->deleteDB_1($type, 'sn', $serialNumber);
        return $objectSql['idPoint'];
      }
    }
  }
}