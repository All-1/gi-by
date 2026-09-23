<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\interfaces\ContractsInterface;
use PersonalAccount\Users\FactoryWorker;

class ContractsWorker extends FactoryWorker implements ContractsInterface
{
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->idPoint = $this->DBWorker->selectResultsFromDBU('gi_new_points', 'id_manager', $this->userId, 'id_point');
    // $this->allPontsID = $this->DBWorker->selectResultsFromDBU_2('gi_new_points', 'state', 1, 'id_point');
    // $this->CatcherBugs->convPrintLog($this->allPontsID, 'ContractsWorker', '$this->allPontsID');
    $this->initialisationOrders();
  }
  public function updatePoints()
  {
    $this->idPoint = $this->DBWorker->selectResultsFromDBU('gi_new_points', 'id_manager', $this->userId, 'id_point');
  }
  // assignYourselfAsReplacement. Назначить себя заменой кому-то. (Массово добавиться во все диалоги специалиста с такой же ролью.) 
  // public function assignYourselfAsReplacement($idWorker, $status)
  // {
  //   switch ($status) {
  //     case 'noactive':
  //       $this->accountDisenable($idWorker, $status, $this->userId);
  //       break;
  //     case 'active':
  //       $this->accountEnable($idWorker, $status, $this->userId);
  //       break;
  //     case 'blocked':
  //       $this->accountBlocked($idWorker, $status, $this->userId);
  //   }
  // }
}