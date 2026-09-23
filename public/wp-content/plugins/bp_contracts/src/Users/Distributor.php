<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\interfaces\InvoicesInterface;
use PersonalAccount\Users\traits\DistributorTrait;
use PersonalAccount\Users\traits\WorkWithInvoice;
use PersonalAccount\Users\traits\WorkWithShipments;
use PersonalAccount\Utilities\ObjectRelatashionshipService;

// Дистрибьютор
class Distributor extends Contractor implements InvoicesInterface
{
  use DistributorTrait;
  // use CapsuleDistributorParam;
  // use CapsuleDealerParam;
  use WorkWithInvoice;
  use WorkWithShipments;
  private $idUP;
  private $idDealersUP;
  private $myPointId;
  private $myPointsId;
  private $idFirm;
  // Методы и свойства для дистрибьютора
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->idUP = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_distributor');
    $this->idFirm = $this->DBWorker->selectSimple('Firms', 'id_dealer', $this->idUP, 'id_firm');
    // $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
    $conditions = $this->DBUtilities->prepareEqualAndEqual($this->idUP, 1, 'id_dealer', 'state');
    $this->myPointsId = $this->DBWorker->selectUni('Points', $conditions, 'id_point');
    $this->myPointId = is_array($this->myPointsId) ? $this->myPointsId[0] : $this->myPointsId;

    $this->idDealersUP = $this->DBWorker->selectSimple('Dealers', 'id_distributor', $this->idUP, 'id_dealer');

    $conditions = $this->DBUtilities->prepareInAndEqual($this->idDealersUP, 1, 'id_dealer', 'state');
    $this->idPoint = $this->DBWorker->selectUni('Points', $conditions, 'id_point');

    $conditions = $this->DBUtilities->createConditionQueryIN('id_dealer', $this->idDealersUP);
    $this->allPontsID = $this->DBWorker->selectUni('Points', $conditions, 'id_point');
    // $this->CatcherBugs->convPrintLog($this->allPontsID, 'Distributor', '$this->allPontsID', 1);
    // $this->CatcherBugs->convPrintLog($this->idPoint, 'Distributor', '$this->idPoint', 1);
    // $this->CatcherBugs->convPrintLog($this->myPointId, 'Distributor', '$this->myPointId', 1);
    $this->idMyManager = $this->UserUtilities->getManagerForPoint($this->myPointId);
    $this->initialisationInvoices();
    $this->initialisationShipments();
    // $this->CatcherBugs->convPrintLog($this->idUP, 'Distributor', '$this->idUP', 1);
    // $this->CatcherBugs->convPrintLog($this->idMyManager, 'Distributor', '$this->idMyManager', 1);
    // $this->CatcherBugs->convPrintLog($this->myPointId, 'Distributor', '$this->myPointId', 1);
    // $this->CatcherBugs->convPrintLog($this->idPoint, 'Distributor', '$this->idPoint', 1);
  }
  // protected function getPointsDealersDBU($idDealersUP)
  // {
  //   $idDealersUPStr = implode(',', $idDealersUP);
  //   $idPointsSQL = $this->wpdb->get_results("SELECT id_point FROM gi_new_points WHERE id_dealer IN ($idDealersUPStr)");
  //   if ($idPointsSQL) {
  //     $idPoints = [];
  //     foreach ($idPointsSQL as $point) {
  //       $idPoints[] = $point->id_point;
  //     }
  //     return $idPoints;
  //   }
  // }
  // protected function getIdDealersUPDBU($idUP)
  // {
  //   $idDealersUPSQL = $this->wpdb->get_results("SELECT id_dealer FROM gi_new_dealers WHERE id_distributor = '$idUP'");
  //   if ($idDealersUPSQL) {
  //     $idDealersUP = [];
  //     foreach ($idDealersUPSQL as $user) {
  //       $idDealersUP[] = $user->id_dealer;
  //     }
  //     return $idDealersUP;
  //   }
  // }
  // //REBUILD THESE MANIPULATES
  // private function getIdDistributorUP($userId)
  // {
  //   $idDistributorUP = $this->wpdb->get_var("SELECT id_distributor FROM gi_new_users WHERE id_user = '$userId'");
  //   return $idDistributorUP;
  // }
  // private function getIdDealersUP($idUP)
  // {
  //   $idDealersUPSQL = $this->wpdb->get_results("SELECT id_dealer FROM gi_new_dealers WHERE id_distributor = '$idUP'");
  //   if ($idDealersUPSQL) {
  //     $idDealersUP = [];
  //     foreach ($idDealersUPSQL as $user) {
  //       $idDealersUP[] = $user->id_dealer;
  //     }
  //     return $idDealersUP;
  //   }
  // }

  //REBUILD THESE MANIPULATES
  public function createContract($nameContract)
  {
    $idPoint = $this->myPointId;
    $dateContract = $this->SimpleUtilities->currentTime();
    $this->wpdb->query("INSERT INTO gi_new_contract 
      (name_contract, id_point, date_last_activity, date_creation) 
      VALUES ('$nameContract', '$idPoint', '$dateContract', '$dateContract')");
    return $this->wpdb->insert_id;
  }
  public function updatePoints()
  {

    $this->idUP = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_distributor');

    $conditions = $this->DBUtilities->prepareEqualAndEqual($this->idUP, 1, 'id_dealer', 'state');
    $this->myPointsId = $this->DBWorker->selectUni('Points', $conditions, 'id_point');
    $this->myPointId = is_array($this->myPointsId) ? $this->myPointsId[0] : $this->myPointsId;

    $this->idDealersUP = $this->DBWorker->selectSimple('Dealers', 'id_distributor', $this->idUP, 'id_dealer');

    $conditions = $this->DBUtilities->prepareInAndEqual($this->idDealersUP, 1, 'id_dealer', 'state');
    $this->idPoint = $this->DBWorker->selectUni('Points', $conditions, 'id_point');

    $conditions = $this->DBUtilities->createConditionQueryIN('id_dealer', $this->idDealersUP);
    $this->allPontsID = $this->DBWorker->selectUni('Points', $conditions, 'id_point');

    $this->idMyManager = $this->UserUtilities->getManagerForPoint($this->myPointId);
  }
  public function getMyPointOutside()
  {
    return $this->myPointId;
  }
  public function updateMyManager()
  {
    $idPoint = $this->myPointId;
    $this->idMyManager = $this->UserUtilities->getManagerForPoint($idPoint);

    $this->idManagerPoint = $this->DBWorker->selectVarFromDBU('gi_new_points', 'id_point', $idPoint, 'id_manager');
  }
  protected function getMyShipments()
  {
    $sqlShipments = $this->wpdb->get_results("SELECT * FROM gi_new_shipment WHERE id_creator = '$this->userId'");
    $myShipments = objectRelatashionshipService::packagData($sqlShipments, 'Shipments', 'shipments');
    return $myShipments;
  }
  public function showInvoices()
  {
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