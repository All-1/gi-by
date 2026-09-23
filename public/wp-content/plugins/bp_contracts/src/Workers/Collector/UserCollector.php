<?php
namespace PersonalAccount\Workers\Collector;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\DBUtilities;
class UserCollector
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
  /** @var UserUtilities */
  private $userUtilities;
  public function __construct($container)
  {
    $this->container = $container;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->userUtilities = $this->container->get('UserUtilities');
  }
  private function packageDealer($idUser)
  {
    $idDealer = $this->dbWorker->selectSimple('Users', 'id_user', $idUser, 'id_dealer');
    $firmName = $this->dbWorker->selectSimple('Firms', 'id_dealer', $idDealer, 'firm_name');
    $firmName = is_array($firmName) ? implode(', ', $firmName) : $firmName;
    $dealerName = $this->dbWorker->selectSimple('Dealers', 'id_dealer', $idDealer, 'name_dealer');
    $pointName = $this->dbWorker->selectSimple('Points', 'id_dealer', $idDealer, 'name_point');
    $pointName = is_array($pointName) ? implode(', ', $pointName) : $pointName;
    return [
      'idDealer' => $idDealer,
      'idPoint' => $this->getIdPoint($idUser),
      'firmName' => $firmName,
      'dealerName' => $dealerName,
      'pointName' => $pointName,
    ];
  }
  private function packageDesignerDealer($idUser)
  {
    $idPoint = $this->dbWorker->selectSimple('Users', 'id_user', $idUser, 'id_point');
    $pointName = $this->dbWorker->selectSimple('Points', 'id_point', $idPoint, 'name_point');
    return [
      'idPoint' => $this->getIdPoint($idUser),
      'pointName' => $pointName,
    ];
  }
  private function packageDistributor($idUser)
  {
    $idDistributor = $this->dbWorker->selectSimple('Users', 'id_user', $idUser, 'id_distributor');
    $firmName = $this->dbWorker->selectSimple('Firms', 'id_dealer', $idDistributor, 'firm_name');
    $firmName = is_array($firmName) ? implode(', ', $firmName) : $firmName;
    $distributorName = $this->dbWorker->selectSimple('Dealers', 'id_dealer', $idDistributor, 'name_dealer');
    $pointName = $this->dbWorker->selectSimple('Points', 'id_dealer', $idDistributor, 'name_point');
    $pointName = is_array($pointName) ? implode(', ', $pointName) : $pointName;
    $distributorName = is_array($distributorName) ? implode(', ', $distributorName) : $distributorName;
    return [
      'idPoint' => $this->getIdPoint($idUser),
      'distributorName' => $distributorName,
    ];
  }
  private function getIdPoint(int $idUser): array
  {
    $idPoint = $this->userUtilities->getAllPointsUser($idUser);
    if (!is_array($idPoint)) {
      $idPoint = $idPoint ? [$idPoint] : [];
    }
    return array_values(array_unique(array_filter(array_map('intval', $idPoint))));
  }
  private function packageSearchField($array)
  {
    unset($array['id']);
    unset($array['idPoint']);
    $search = implode(', ', $array);
    return $search;
  }
  public function getFactoryUsers()
  {
    $factoryUsers = $this->userUtilities->getFactoryUsers();
    $users = [];
    foreach ($factoryUsers as $idUser) {
      $role = $this->userUtilities->getUserRole($idUser);
      $active = $this->dbWorker->selectVarFromDBU('gi_new_users', 'id_user', $idUser, 'status_activity');
      if ($active === 'active' && ($role === 'manager' || $role === 'consultant' || $role === 'complaint_handler')) {
        $userName = $this->userUtilities->getNameUser($idUser);
        $users[$idUser]['id'] = $idUser;
        $users[$idUser]['name'] = $userName;
        $users[$idUser]['role'] = $this->userUtilities->translateUserRole($role);
        $users[$idUser]['search'] = $this->packageSearchField($users[$idUser]);
      }
    }
    return $users;
  }
  public function getContractorUsers()
  {
    $contractorUsers = $this->userUtilities->getContractorUsers();
    $users = [];
    foreach ($contractorUsers as $idUser) {
      $active = $this->dbWorker->selectVarFromDBU('gi_new_users', 'id_user', $idUser, 'status_activity');
      if ($active === 'active') {
        $userName = $this->userUtilities->getNameUser($idUser);
        $userRole = $this->userUtilities->getUserRole($idUser);
        $users[$idUser] = match ($userRole) {
          'dealer' => $this->packageDealer($idUser),
          'free_dealer' => $this->packageDealer($idUser),
          'designer_dealer' => $this->packageDesignerDealer($idUser),
          'distributor' => $this->packageDistributor($idUser),
        };
        $users[$idUser]['id'] = $idUser;
        $users[$idUser]['name'] = $userName;
        $users[$idUser]['role'] = $this->userUtilities->translateUserRole($userRole);
        $users[$idUser]['search'] = $this->packageSearchField($users[$idUser]);
      }
    }
    return $users;
  }
  public function getDealerUsers()
  {
    $dealerRoles = ['dealer', 'free_dealer', 'distributor'];
    $contractorUsers = $this->userUtilities->getContractorUsers();
    $users = [];
    foreach ($contractorUsers as $idUser) {
      $active = $this->dbWorker->selectVarFromDBU('gi_new_users', 'id_user', $idUser, 'status_activity');
      if ($active !== 'active') {
        continue;
      }
      $userRole = $this->userUtilities->getUserRole($idUser);
      if (!in_array($userRole, $dealerRoles, true)) {
        continue;
      }
      $userName = $this->userUtilities->getNameUser($idUser);
      $users[$idUser] = match ($userRole) {
        'dealer' => $this->packageDealer($idUser),
        'free_dealer' => $this->packageDealer($idUser),
        'designer_dealer' => $this->packageDesignerDealer($idUser),
        'distributor' => $this->packageDistributor($idUser),
      };
      $users[$idUser]['id'] = $idUser;
      $users[$idUser]['name'] = $userName;
      $users[$idUser]['role'] = $this->userUtilities->translateUserRole($userRole);
      $users[$idUser]['search'] = $this->packageSearchField($users[$idUser]);
    }
    return $users;
  }
}