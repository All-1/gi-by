<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\FactoryWorker;
use PersonalAccount\Users\traits\ManageFactoryWorkers;
use PersonalAccount\Users\traits\ManageContractors;
use PersonalAccount\Workers\ManageContractorsWorker;
use PersonalAccount\Users\traits\ManageAnalytics;
use PersonalAccount\Workers\Analytics\AnalyticsWorker;

class Admin extends FactoryWorker
{
  use ManageFactoryWorkers;
  use ManageContractors;
  use ManageAnalytics;
  /** @var ManageContractorsWorker */
  private $manageContractorsWorker;
  /** @var AnalyticsWorker */
  private $AnalyticsWorker; 

  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    // $this->initialisationOrders();
    $this->inicializationManageContractors();
    $this->manageContractorsWorker = new ManageContractorsWorker($this->Container);
    $this->AnalyticsWorker = new AnalyticsWorker($this->Container);
  }

  // changePointsPerson При назначении статуса blocked требует назначить другого менеджера ответственного за его точки.
}