<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\Contractor;
use PersonalAccount\Users\interfaces\DealerInterface;
use PersonalAccount\Users\interfaces\InvoicesInterface;
use PersonalAccount\Users\traits\DealerTrait;
use PersonalAccount\Users\traits\DistributorTrait;
use PersonalAccount\Users\traits\capsule\CapsuleDealerParam;
use PersonalAccount\Users\traits\WorkWithInvoice;
use PersonalAccount\Users\traits\WorkWithShipments;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
class FreeDealer extends Contractor implements DealerInterface, InvoicesInterface
{
  private $idFirm;
  use DealerTrait, DistributorTrait;
  use CapsuleDealerParam;
  use WorkWithInvoice, WorkWithShipments;

  // Методы и свойства для free-dealer
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->inicializationDealer();
    // $this->CatcherBugs->convPrintLog($this->idUP, 'FreeDealer', '$this->idUP');
    $this->idFirm = $this->DBWorker->selectSimple('Firms', 'id_dealer', $this->idUP, 'id_firm');
    // $this->CatcherBugs->convPrintLog($this->idFirm, 'FreeDealer', '$this->idFirm');
    $this->initialisationShipments();
    $this->initialisationInvoices();
    // $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
    // error_log('print_r($this->idPoint, true)');
    // error_log(print_r($this->idPoint, true));
  }
  public function updateAfterBindUser()
  {
    $this->idUP = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_dealer');
    $this->updateMyManager();
    $this->updateWorkers();
    $this->updatePoints();
    $this->rank = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'user_rank'); //Простое число.
    $this->connected = !empty($this->rank) ? true : false;
  }
  protected function getMyShipments()
  {
    $sqlShipments = $this->wpdb->get_results("SELECT * FROM gi_new_shipment WHERE id_creator = '$this->userId'");
    $myShipments = objectRelatashionshipService::packagData($sqlShipments, 'Shipments', 'shipments');
    return $myShipments;
  }

  protected function initializationWPDB()
  {
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  public function showInvoices()
  {
    // $this->CatcherBugs->convPrintLog($this->idFirm, 'showInvoices', '$this->idFirm');
    $timeStart_1 = microtime(true);
    $invoicesOnPage = $this->InvoiceWorker->getInvoicesOnPage_2($this->currentPageInvoices, $this->perPageInvoices, $this->searchQueryInvoices, $this->idFirm);
    $timeEnd_1 = microtime(true);
    $timeStart_2 = microtime(true);
    // $invoicesOnPage = $this->InvoiceWorker->getInvoicesOnPage($this->currentPageInvoices, $this->perPageInvoices, $this->searchQueryInvoices, $this->idFirm);
    $timeEnd_2 = microtime(true);
    $time_1 = $timeEnd_1 - $timeStart_1;
    $time_2 = $timeEnd_2 - $timeStart_2;
    // $this->CatcherBugs->convPrintLog($time_1, 'showInvoices', '$time_1');
    // $this->CatcherBugs->convPrintLog($time_2, 'showInvoices', '$time_2');
    
    return $invoicesOnPage;
  }
  public function printInvoice($idInvoice)
  {
    $data = $this->InvoiceWorker->printInvoice($idInvoice);
    return $data;
  }
}