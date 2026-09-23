<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\interfaces\ContractorInterface;
use PersonalAccount\Users\interfaces\ContractsInterface;
use PersonalAccount\Users\traits\capsule\CapsuleContractorParam;
use Exception;
// сontractor (userMain – наследует)
class Contractor extends UserMain implements ContractorInterface, ContractsInterface
{
  use capsuleContractorParam;
  protected $idContractorHigherRank;
  protected $idMyManager;
  protected $idManagerPoint;
  protected $idMyDealerUP;

  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    // $idPoint = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_point');
    if ($this->userRole === 'designer_dealer') {

      $this->idPoint = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_point');
      $this->idMyDealerUP = $this->DBWorker->selectVarSimple('Points', 'id_point', $this->idPoint, 'id_dealer');
      $this->allPontsID = $this->DBWorker->selectSimple('Points', 'id_dealer', $this->idMyDealerUP, 'id_point');
      $this->idMyManager = $this->UserUtilities->getManagerForPoint($this->idPoint);
      $this->idManagerPoint = $this->DBWorker->selectResultsFromDBU_2('gi_new_points', 'id_point', $this->idPoint, 'id_manager');
      $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
    }
    $this->updateCOWorkers();

  }

  protected function getCoWorkersContractor($userId)
  {
    if ($this->userRole === 'designer_dealer') {
      $idMyCoWorkers = $this->DBWorker->selectResultsFromDBU_2('gi_new_users', 'id_point', $this->idPoint, 'id_user');
      $myCoWorkers = $this->UserUtilities->packageUser($idMyCoWorkers);
      return $myCoWorkers;
    }
  }
  protected function getIdUPMyDealer($userId)
  {
    $idUP = $this->wpdb->get_var("SELECT id_dealer FROM gi_new_designer_dealer WHERE id_user = '$userId'");
    return $idUP;
  }
  protected function getContractorHigherRankDB($user)
  {
    $idContractorHigherRank = '';
    if ($user->userRole === 'designer_dealer') {
      $idUP = $this->DBWorker->selectResultsFromDBU_2('gi_new_points', 'id_point', $user->idPoint, 'id_dealer');
      $idContractorHigherRank = $this->DBWorker->selectVarSimple('Users', 'id_user', $idUP, 'id_dealer');
    } elseif ($user->userRole === 'dealer') {
      $idUP = $this->wpdb->get_var("SELECT id_distributor FROM gi_new_dealers WHERE id_dealer = '$user->idUP'");
      $idContractorHigherRank = $this->DBWorker->selectVarSimple('Users', 'id_distributor', $idUP, 'id_user');
      // $this->CatcherBugs->convPrintLog($idContractorHigherRank, 'getContractorHigherRankDB', '$idContractorHigherRank');
    } elseif ($user->userRole === 'free_dealer' || $user->userRole === 'distributor') {
      $idContractorHigherRank = null;
    }
    return $idContractorHigherRank ? intval($idContractorHigherRank) : null;
  }
  //Получаем нашу точку
  private function getMyPoint()
  {
    try {
      $idPointSql = $this->wpdb->get_results("SELECT id_point FROM gi_new_users WHERE id_user = '$this->userId'");
      if (count($idPointSql) === 1) {
        return intval($idPointSql[0]->id_point);
      } else if (count($idPointSql) > 1) {
        $idPoints = [];
        foreach ($idPointSql as $idPoint) {
          $idPoints[] = intval($idPoint->id_point);
        }
        return $idPoints;
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'getMyPoint'];
    }
  }
  // Забираем кого можно добавить.
  private function getPotentialParticipantDB()
  {
    $sqlUsersMyPoint = $this->wpdb->get_results("SELECT * FROM gi_new_users WHERE id_point = '$this->idPoint' AND status_activity != 'blocked' AND rank <= " . ($this->rank + 1));
    $usersOnMyPoint = [];
    foreach ($sqlUsersMyPoint as $user) {
      // $firstName = get_user_meta($user->id_user, 'first_name', true);
      // $lastName = get_user_meta($user->id_user, 'last_name', true);
      $userdata = $this->UserUtilities->getUserData($user->id_user);
      $usersOnMyPoint[] = $userdata;
    }
    return $usersOnMyPoint;
  }
  protected function updateCOWorkers()
  {
    $this->myCoWorkers = $this->getCoWorkersContractor($this->userId);
    $this->potencialReplacement = $this->getPotencialReplacementServer($this->myCoWorkers, $this->userRole, $this->userId); //В трейте Utilit
  }
  // Интерфейс
  // createContract - Могут создать контракт. все кроме distributor
  public function createContract($nameContract)
  {
    $dateContract = $this->SimpleUtilities->currentTime();
    $idPoint = is_array($this->idPoint) ? $this->idPoint[0] : $this->idPoint;
    if (!empty($idPoint) && is_numeric($idPoint)) {
      $this->wpdb->query("INSERT INTO gi_new_contract 
        (name_contract, id_point, date_last_activity, date_creation) 
        VALUES ('$nameContract', '$idPoint', '$dateContract', '$dateContract')");
      // $this->CatcherBugs->convPrintLog($idPoint, 'createContract', '$idPoint');
      return $this->wpdb->insert_id;
    }
  }

  // createDialog - Могут создать тему диалога "Заказ".
  public function createDialog($serialNumber, $typeDialog)
  {
    $dateNow = $this->SimpleUtilities->currentTime();
    $this->wpdb->query("INSERT INTO gi_new_dialogues 
      (sn, id_creator, type_dialog, date_last_activity, date_creation, status_activity, id_order) 
      VALUES ('$serialNumber', '$this->userId', '$typeDialog', '$dateNow', '$dateNow', 'yes', null)");
    $idDialog = intval($this->wpdb->insert_id);
    $this->updateMyManager();
    $idFactoryWorker = $this->DialogServices->choiseManagerCurrentDialog($typeDialog, $this->idMyManager);
    //Добавляем всех кого надо участниками диалога.
    // $this->CatcherBugs->convPrintLog($this->userId, 'createDialog', '$this->userId');
    // $this->CatcherBugs->convPrintLog($idFactoryWorker, 'createDialog', '$idFactoryWorker');
    // $this->CatcherBugs->convPrintLog($this->idMyManager, 'createDialog', '$this->idMyManager');
    // $this->CatcherBugs->convPrintLog($idFactoryWorker, 'createDialog', '$idFactoryWorker');
    $this->DialogServices->addParticipant($this->userId, $idDialog);
    $this->DialogServices->addParticipant($idFactoryWorker, $idDialog);
    return $idDialog;
  }
  // renameContract - Может быть изменено контрагентом кому принадлежит точка. 
  public function renameContract($serialNumber, $newName)
  {
    $this->wpdb->query("UPDATE gi_new_new_contract SET name_contracts = '$newName' WHERE sn = $serialNumber");
  }
  public function updateForClientStatusWorkers()
  {
    $this->updateCOWorkers();
    // return $this->parametersForClient;
  }
  public function getOutsideManagerMyPoint()
  {
    return $this->idManagerPoint;
  }
  public function updateMyManager()
  {
    $idPoint = is_array($this->idPoint) ? $this->idPoint[0] : $this->idPoint;
    $this->idMyManager = $this->UserUtilities->getManagerForPoint($idPoint);

    $this->idManagerPoint = $this->DBWorker->selectVarFromDBU('gi_new_points', 'id_point', $idPoint, 'id_manager');
  }
  public function outsideContractorHigherRank()
  {
    return $this->getIdContractorHigherRank();
  }
  public function updateAfterBindUser()
  {
    $this->updateMyManager();
    $this->updateCOWorkers();
    $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
    $this->updatePoints();
    $this->rank = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'user_rank'); //Простое число.
    $this->connected = !empty($this->rank) ? true : false;
  }
  public function updateAfterBindedMe()
  {
    $this->statusActivity = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'status_activity');
    $this->rank = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'user_rank'); //Простое число.
    $this->updatePoints();
    $this->updateMyManager();
    $this->updateCOWorkers();
    if ($this->userRole === 'designer_dealer') {
      $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
    }
  }
  public function updatePoints()
  {
    $idPoint = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_point');
    $condition = $this->DBUtilities->prepareEqualAndEqual($idPoint, 1, 'id_point', 'state');
    $this->idPoint = $this->DBWorker->selectUni('Points', $condition, 'id_point');
    $this->allPontsID = $idPoint;
  }
  // getOrdersMyPoint – Видит все заказы на своей точке. РЕАЛИЗАЦИ ПОЗЖЕ
}