<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\FactoryWorker;
use PersonalAccount\Users\traits\ManageFactoryWorkers;
use PersonalAccount\Users\traits\ManageContractors;
use PersonalAccount\Workers\ManageContractorsWorker;

class SalesManager extends FactoryWorker
{
  use ManageFactoryWorkers;
  use ManageContractors;
  /** @var ManageContractorsWorker */
  private $manageContractorsWorker;
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    // $this->initialisationOrders();
    $this->inicializationManageContractors();
    $this->manageContractorsWorker = new ManageContractorsWorker($this->Container);
    // $this->CatcherBugs->convPrintLog('Ready', 'SalesManager', 'manageContractorsWorker');
  }
  
  // changePointsPerson При назначении статуса blocked требует назначить другого менеджера ответственного за его точки.
}