<?php
namespace PersonalAccount\Workers;


use PersonalAccount\Core\Container;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Workers\UPWorker;

use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;

class ContractsWorker 
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
  public function __construct($Container)
  {
    $this->container = $Container;
    $this->dbWorker = $Container->get('DBWorker');
    $this->catcherBugs = $Container->get('CatcherBugs');
    $this->simpleUtilities = $Container->get('SimpleUtilities');
    $this->dbUtilities = $Container->get('DBUtilities');
    $this->dataUtilities = $Container->get('DataUtilities');
  }
  public function getContracts($userId, $pontsID, $filters)
  {
    $pontsID = is_array($pontsID) ? $pontsID : [$pontsID];
    $pointsString = implode(',', $pontsID);
    $contracts = $this->dbWorker->selectContracts($userId, $pointsString, $filters);
    $unreadedDialogues = $this->dbWorker->selectUnreadedDialogues($userId);
    $countContracts = $this->dbWorker->selectCountContracts($userId, $pointsString, $filters);
    $contracts = $this->dataUtilities->packageContracts_2($contracts, $unreadedDialogues);
    
    $countPages = ceil($countContracts / $filters['perPage']);
    $contracts[] = ['countPage' => $countPages];

    return $contracts;
  }
}