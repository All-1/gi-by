<?php
namespace PersonalAccount\Users\traits;
// Трейт для дилера
trait DealerTrait
{
  protected $idUP;
  private $myWorkers;
  // private function getIdDealerUPDB()
  // {
  //   $idDealerUP = $this->wpdb->get_var("SELECT id_dealer FROM gi_new_users WHERE id_user = '$this->userId'");
  //   return $idDealerUP;
  // }
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  abstract protected function getPotencialReplacementServer($name, $role, $idUser);
  abstract protected function prepareParametrForClientUM();
  private function removeUserDisegnerDealer($idWorker)
  {
    $this->wpdb->query("DELETE FROM gi_new_designer_dealer WHERE id_user = '$idWorker' AND id_dealer = '$this->idUP'");
  }
  // changeResponsibleDialogs - Перекинуть массово передать все диалоги другому Дизайнеру. 

  private function getMyWorkersServer($myWorkers)
  {
    $myCoWorkers = [];
    foreach ($myWorkers as $idUser => $value) {
      if ($myWorkers[$idUser]['area'] === $this->userArea) {
        $user = $this->UserUtilities->packegeUserForClient($myWorkers, $idUser);
        $user['potencialReplacement'] = $this->getPotencialReplacementServer($myWorkers, $user['role'], $idUser);
        $myCoWorkers[$idUser] = $user;
      }
    }
    return $myCoWorkers;
  }
  private function parametrForClientDealer()
  {
    $prepareParametersForClient = $this->prepareParametrForClientUM();
    $prepareParametersForClient['myWorkers'] = $this->myWorkers;
    return $prepareParametersForClient;
  }
  private function inicializationDealer()
  {
    $this->idUP = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_dealer');
    $this->allPontsID = $this->DBWorker->selectSimple('Points', 'id_dealer', $this->idUP, 'id_point');
    $condition = $this->DBUtilities->prepareEqualAndEqual($this->idUP, 1, 'id_dealer', 'state');
    $this->idPoint = $this->DBWorker->selectUni('Points', $condition, 'id_point');
    $idPoint = is_array($this->idPoint) ? $this->idPoint[0] : $this->idPoint;
    $this->idMyManager = $this->UserUtilities->getManagerForPoint($idPoint);
    $this->idManagerPoint = $this->DBWorker->selectVarFromDBU('gi_new_points', 'id_point', $idPoint, 'id_manager');
    // $this->CatcherBugs->convPrintLog($this->idPoint, 'Dealer', '$this->idPoint');
    // $this->CatcherBugs->convPrintLog($this->idMyManager, 'Dealer', '$this->idMyManager');
    // $this->CatcherBugs->convPrintLog($this->idManagerPoint, 'Dealer', '$this->idManagerPoint');
    $this->updateWorkers();
  }
  private function updateWorkers()
  {
    $myWorkersId = $this->DBWorker->selectResultsFromDBU_2('gi_new_users', 'id_point', $this->idPoint, 'id_user');
    $prapareMyWorkers = $this->UserUtilities->packageUser($myWorkersId);
    $this->myWorkers = $this->getMyWorkersServer($prapareMyWorkers);
    $this->parametersForClient = $this->parametrForClientDealer(); //Метод находится в UserMain
  }
  public function outsideParamForClient()
  {
      $this->parametersForClient = $this->parametrForClientDealer();
      return $this->parametersForClient;
  }
  public function updateForClientStatusWorkers()
  {
      $this->updateWorkers();
  }
  public function updatePoints()
  {
      $this->allPontsID = $this->DBWorker->selectSimple('Points', 'id_dealer', $this->idUP, 'id_point');
      $condition = $this->DBUtilities->prepareEqualAndEqual($this->idUP, 1, 'id_dealer', 'state');
      $this->idPoint = $this->DBWorker->selectUni('Points', $condition, 'id_point');
  }

}