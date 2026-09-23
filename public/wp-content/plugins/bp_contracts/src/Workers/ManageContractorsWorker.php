<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\DBUtilities;
class ManageContractorsWorker
{
  use Utilit;
  /** @var Container */
  private $container;
  /** @var DBWorker */
  private $dbWorker;
  /** @var DataUtilities */
  private $dataUtilities;
  /** @var CatcherBugs */
  private $catcherBugs;
  /** @var SimpleUtilities */
  private $simpleUtilities;
  /** @var UserUtilities */
  private $userUtilities;
  /** @var DBUtilities */
  private $dbUtilities;
  private $roleDistributor;
  private $roleDealer;
  private $roleDesigner;
  private $contractorsFromUP;
  private $bindedUsers;
  private $allPoints;
  private $allContractorsWP;
  public function __construct($container)
  {
    $this->container = $container;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');

    $this->roleDistributor = ['distributor'];
    $this->roleDealer = ['free_dealer', 'dealer', 'subscriber'];
    $this->roleDesigner = ['designer_dealer'];

    $this->contractorsFromUP = $this->determinUsersFromUP();
    $this->bindedUsers = $this->dbWorker->selectSimple('Users');
    $this->allPoints = $this->dbWorker->selectSimple('Points', 'state', 1);
    // $this->catcherBugs->convPrintLog($this->allPoints, 'allPoints', '$this->allPoints');
  }
  private function propertyDetermine($role)
  {
    if (in_array($role, $this->roleDesigner)) {
      $property = 'designers';
    } elseif (in_array($role, $this->roleDealer)) {
      $property = 'dealers';
    } elseif (in_array($role, $this->roleDistributor)) {
      $property = 'distributors';
    }
    return $property;
  }
  protected function getUserDataByRoleWP($role)
  {
    wp_cache_flush();
    $distributors = get_users(array('role__in' => $role));
    $userData = array();
    foreach ($distributors as $user) {
      $userData[] = array(
        'idUser' => $user->ID,
        'name' => $user->first_name,
        'lastname' => $user->last_name,
        'role' => implode(', ', $user->roles),
        'country' => $user->country,
        'city' => $user->city,
        'address' => $user->address,
        'phone' => $user->phone,
        'company' => $user->company,
      );
    }
    return $userData;
  }
  public function sortedContractors($filters)
  {
    $this->contractorsFromUP = $this->determinUsersFromUP();
    $this->bindedUsers = $this->dbWorker->selectSimple('Users');
    $this->allPoints = $this->dbWorker->selectSimple('Points', 'state', 1);
    $this->prepareAllUsers();
    $sortedContractors = $this->sortedByRole($this->allContractorsWP, $filters['filterRole']);
    $sortedContractors = $this->sortedByStatus($sortedContractors, $filters['filterStatus']);
    $sortedContractors = $this->sortedBySearchQuery($sortedContractors, $filters['searchQuery']);
    $sortedContractors = $this->simpleUtilities->sortedByAlphabet($sortedContractors, 'nameWP');
    $contractorsOnPage = objectRelatashionshipService::prepareListObjectsRelatashionShip($sortedContractors, $filters['perPage'], $filters['currentPage']);
    return $contractorsOnPage;
  }
  protected function sortedByRole($contractors, $filterRole)
  {
    $sortedContractors = [];
    if (!empty($contractors) && !empty($filterRole)) {
      foreach ($filterRole as $role) {
        foreach ($contractors as $key => $user) {
          if ($role === $key) {
            $sortedContractors = $contractors[$role];
            break;
          } else {
            continue;
          }
        }
      }
      return $sortedContractors;
    }
    return $contractors;
  }
  protected function sortedByStatus($contractors, $filerStatus)
  {
    $sortedContractors = [];
    if (!empty($contractors) && !empty($filerStatus)) {
      foreach ($filerStatus as $status) {
        foreach ($contractors as $user) {
          if ($user['binded'] === $status || $user['status'] === $status) {
            $sortedContractors[] = $user;
          }
        }
      }
      return $sortedContractors;
    }
    return $contractors;
  }
  protected function sortedBySearchQuery($contractors, $searchQuery)
  {
    $sortedContractors = [];
    if (!empty($contractors) && !empty($searchQuery)) {
      foreach ($contractors as $user) {
        $propertiesForSearch = [
          'nameWP',
          'nameUP',
          'namePoint',
          'myDealerNameUP',
          'myDistributorNameUP',
          'myDesignersNameWP',
          'statusRus',
          'bindTo',
          'country',
          'city',
          'address',
          'phone',
          'company'
        ];
        $overallCheck = $this->dataUtilities->checkIncludedByString($user, $propertiesForSearch, $searchQuery);
        if ($overallCheck) {
          $sortedContractors[] = $user;
        }
      }
      return $sortedContractors;
    }
    return $contractors;
  }
  protected function getUsersContractor($bindedUsers, $usersDataWP, $role)
  {
    $checkDistributor = $this->userUtilities->checkRecieveUserRole(['distributor'], $role);
    $checkDealer = $this->userUtilities->checkRecieveUserRole(['free_dealer', 'dealer', 'subscriber'], $role);
    $checkDesignerDealer = $this->userUtilities->checkRecieveUserRole(['designer_dealer'], $role);
    if ($checkDistributor) {
      $property = 'idDistributor';
    } else if ($checkDealer) {
      $property = 'idDealer';
    } else if ($checkDesignerDealer) {
      $property = 'idPoint';
    }
    $preparedUsers = $this->prepareUsersForManager($bindedUsers, $usersDataWP, $property);
    return $preparedUsers;
  }
  protected function prepareUsersForManager($bindedUsersDB, $usersDataWP, $property)
  {
    $users = [];
    foreach ($usersDataWP as $userWP) {
      foreach ($bindedUsersDB as $bindedUser) {
        $i = $userWP['idUser'];
        $userRole = $this->userUtilities->determineUserRoleDealer($userWP['role']);
        $users[$i]['idUser'] = $i;
        $users[$i]['nameWP'] = $userWP['lastname'] . ' ' . $userWP['name'];
        $users[$i]['name'] = $userWP['name'];
        $users[$i]['lastname'] = $userWP['lastname'];
        $users[$i]['role'] = $userRole;
        $users[$i]['country'] = $userWP['country'];
        $users[$i]['city'] = $userWP['city'];
        $users[$i]['address'] = $userWP['address'];
        $users[$i]['phone'] = $userWP['phone'];
        $users[$i]['company'] = $userWP['company'];
        if (intval($userWP['idUser']) === intval($bindedUser['idUser'])) {
          $users[$i] = $this->prepareBindedUser($users[$i], $bindedUser, $property);
          break;
        } else {
          $users[$i]['binded'] = 'nobind';
          $users[$i]['statusRus'] = 'не привязан';
          $users[$i]['status'] = 'nobind';
        }
        if (isset($users[$i]['state']) && intval($users[$i]['state']) === 0) {
          unset($users[$i]); // Удаляем пользователя из массива.
        }
      }
    }
    return $users;
  }
  protected function prepareBindedUser($user, $bindedUser, $property)
  {
    $user['nameUP'] = $bindedUser['userName']; // Возможно изменится.
    $user[$property] = $bindedUser[$property]; // Can be idDistributor, idDealer, idPoint
    $user['status'] = $bindedUser['statusActivity'];
    $user['statusRus'] = $this->userUtilities->changeStatusToRus($bindedUser['statusActivity'], 3);
    $user['binded'] = 'bind';
    if ($user['role'] === 'distributor') {
      $user = $this->getExtendedInfoDistridutor($user, $property);
    } else if ($user['role'] === 'dealer' || $user['role'] === 'free_dealer' || $user['role'] === 'subscriber') {
      $user = $this->getExtendedInfoDealer($user, $property);
    } else if ($user['role'] === 'designer_dealer') {
      $user = $this->getExtendedInfoDesigner($user, $property);
    }
    return $user;
  }
  protected function getExtendedInfoDistridutor($user, $property)
  {
    //Work with my Dealers
    $dealers = $this->dataUtilities->sortArray($this->contractorsFromUP['dealers'], $property, $user[$property]);
    $idDealers = $this->dataUtilities->getFromArrByKeyU($dealers, 'idDealer');
    $dealersBinded = $this->dataUtilities->sortArray($this->bindedUsers, 'idDealer', $idDealers);
    $idUsersDealer = $this->dataUtilities->getFromArrByKeyU($dealersBinded, 'idUser');
    $infoDealersWP = $this->userUtilities->getInfoAboutUsers($idUsersDealer);
    //Work with Point
    $points = $this->dataUtilities->sortArray($this->allPoints, 'idDealer', $idDealers);

    //Work with Distributor info
    $distributorUP = $this->dataUtilities->sortArray($this->contractorsFromUP['distributors'], 'idDealer', $user[$property]);

    $user = $this->dataUtilities->getFromArrToArr($distributorUP, $user, 'nameDealer', 'bindTo');
    $user = $this->dataUtilities->getFromArrToArr($distributorUP, $user, 'state', 'state');
    // Add new Property in Array user
    $user = $this->dataUtilities->getFromArrToArr($infoDealersWP, $user, 'displayName', 'myDealerNameWP');
    $user = $this->dataUtilities->getFromArrToArr($dealers, $user, 'nameDealer', 'myDealerNameUP');
    $user = $this->dataUtilities->getFromArrToArr($points, $user, 'namePoint', 'namePoint');
    $user = $this->dataUtilities->getFromArrToArr($points, $user, 'idPoint', 'idPoint');

    return $user;
  }
  protected function getExtendedInfoDealer($user, $property)
  {
    //Work with Point
    $points = $this->dataUtilities->sortArray($this->allPoints, $property, $user[$property]);
    $idPoints = $this->dataUtilities->getFromArrByKeyU($points, 'idPoint');
    //Work with Designer
    $designers = $this->dataUtilities->sortArray($this->bindedUsers, 'idPoint', $idPoints);

    $idUsersDesigner = $this->dataUtilities->getFromArrByKeyU($designers, 'idUser');
    $infoDesignerWP = $this->userUtilities->getInfoAboutUsers($idUsersDesigner);
    //Work with Distridutor
    $distributorId = $this->dataUtilities->sortArray($this->contractorsFromUP['dealers'], $property, $user[$property], 'idDistributor');
    $distributorUP = $this->dataUtilities->sortArray($this->contractorsFromUP['distributors'], 'idDealer', $distributorId);
    $distributor = $this->dataUtilities->sortArray($this->bindedUsers, 'idDistributor', $distributorId, 'idUser');

    $dealerUP = $this->dataUtilities->sortArray($this->contractorsFromUP['dealers'], $property, $user[$property]);
    $user = $this->dataUtilities->getFromArrToArr($dealerUP, $user, 'state', 'state');
    //Add new Property in Array user
    $user = $this->dataUtilities->getFromArrToArr($distributorUP, $user, 'nameDealer', 'myDistributorNameUP');
    $user = $this->dataUtilities->getFromArrToArr($points, $user, 'namePoint', 'namePoint');
    $user = $this->dataUtilities->getFromArrToArr($infoDesignerWP, $user, 'displayName', 'myDesignersNameWP');
    $user = $this->dataUtilities->getFromArrToArr($dealerUP, $user, 'nameDealer', 'bindTo');
    $user['idPoint'] = $idPoints;

    return $user;
  }
  protected function getExtendedInfoDesigner($user, $property)
  {
    $point = $this->dataUtilities->sortArray($this->allPoints, $property, $user[$property]);
    $idDealer = $this->dataUtilities->getFromArrByKeyU($point, 'idDealer');
    $dealerUP = $this->dataUtilities->sortArray($this->contractorsFromUP['dealers'], 'idDealer', $idDealer);
    $idUsersDealer = !empty($dealerUP['idDealer']) ? $this->dataUtilities->sortArray($this->bindedUsers, 'idDealer', $dealerUP['idDealer'], 'idUser') : '';
    $infoDealersWP = $this->userUtilities->getInfoAboutUsers($idUsersDealer);

    $user = $this->dataUtilities->getFromArrToArr($infoDealersWP, $user, 'displayName', 'myDealerNameWP');
    $user = $this->dataUtilities->getFromArrToArr($dealerUP, $user, 'nameDealer', 'myDealerNameUP');
    $user = $this->dataUtilities->getFromArrToArr($point, $user, 'namePoint', 'namePoint');
    $user = $this->dataUtilities->getFromArrToArr($point, $user, 'namePoint', 'bindTo');
    return $user;
  }
  private function prepareAllUsers()
  {
    $distributors = $this->getUserDataByRoleWP($this->roleDistributor);
    $dealers = $this->getUserDataByRoleWP( $this->roleDealer);
    $designers = $this->getUserDataByRoleWP($this->roleDesigner);
    $this->allContractorsWP = $this->setAllContractorsWP($distributors, $dealers, $designers); // Написать присвоение свойства точки.
  }
  protected function setAllContractorsWP($distributors, $dealers, $designers)
  {
    $allDistributors = $this->getUsersContractor($this->bindedUsers, $distributors, $this->roleDistributor);
    $allDealers = $this->getUsersContractor($this->bindedUsers, $dealers, $this->roleDealer);
    $allDesigners = $this->getUsersContractor($this->bindedUsers, $designers, $this->roleDesigner);
    $allContractorsWP = [
      'distributors' => $allDistributors,
      'dealers' => $allDealers,
      'designers' => $allDesigners
    ];
    return $allContractorsWP;
  }
  protected function sortWhoBindFromUP($usersFromUP, $usersBinded)
  {
    $usersUnbinded = [];
    foreach ($usersFromUP as $userUP) {
      foreach ($usersBinded as $bindedUser) {
        $i = $userUP['idDealer'];
        $checkIdDealer = $i === $bindedUser['idDealer'];
        $checkIdDistributor = $i === $bindedUser['idDistributor'];
        $checkState = $userUP['state'] === 1;
        if (!$checkIdDealer && !$checkIdDistributor && $checkState) {
          $usersUnbinded[$i]['idDealer'] = $i;
          $usersUnbinded[$i]['nameDealer'] = $userUP['nameDealer'];
          $usersUnbinded[$i]['idDistributor'] = $userUP['idDistributor'];
        }
      }
    }
    return $usersUnbinded;
  }
  protected function determinUsersFromUP()
  {
    $condition = $this->dbUtilities->prepareEqualAndEqualColumn(1, 'id_dealer', 'state', 'id_distributor');
    $usersFromUP = [
      'distributors' => $this->dbWorker->selectUni('Dealers', $condition),
      'dealers' => $this->dbWorker->selectSimple('Dealers', 'state', 1)
    ];
    $contractorsFromUP = [];
    foreach ($usersFromUP as $key => $part) {
      foreach ($part as $userUP) {
        $idDealer = $userUP['idDealer'];
        $contractorsFromUP[$key][$idDealer] = $userUP;
      }
    }
    return $contractorsFromUP;
  }
  private function changeUserName($userId, $name, $lastname)
  {
    // Проверьте, существует ли пользователь
    $user = $this->userUtilities->getUserData($userId);
    if ($user) {
      $updated_user = wp_update_user([
        'ID' => (int) $userId, // Преобразуем ID в целое число, это важно
        'first_name' => sanitize_text_field($name),
        'last_name' => sanitize_text_field($lastname),
      ]);
      if (is_wp_error($updated_user)) {
        // Обработка ошибки
        return 'Ошибка: ' . $updated_user->get_error_message();
      }
      return 'Имя и фамилия успешно обновлены!';
    } else {
      return 'Пользователь не найден.';
    }
  }
  public function searchForBindContractors($data)
  {
    wp_cache_delete($data->idUser, 'users');
    wp_cache_delete($data->idUser, 'user_meta');
    $role = $this->userUtilities->getUserRole($data->idUser);
    if (in_array($role, $this->roleDesigner)) {
      $this->allPoints = $this->dbWorker->selectResultsFromDBU_2('gi_new_points', 'state', 1);
      $paramData = $this->dataUtilities->sortedByQueryArray($this->allPoints, 'namePoint', $data->searchQuery);
    } elseif (in_array($role, $this->roleDealer)) {
      // $usersFromUP = $this->dbWorker->selectResultsFromDBU_2('gi_new_dealers', 'state', 1);
      $this->contractorsFromUP = $this->determinUsersFromUP();
      $paramData = $this->dataUtilities->sortedByQueryArray($this->contractorsFromUP['dealers'], 'nameDealer', $data->searchQuery);
    } elseif (in_array($role, $this->roleDistributor)) {
      $usersFromUP = $this->dbWorker->selectResultsFromDBU_2('gi_new_dealers', 'state', 1);
      $this->contractorsFromUP = $this->determinUsersFromUP();
      $paramData = $this->dataUtilities->sortedByQueryArray($this->contractorsFromUP['distributors'], 'nameDealer', $data->searchQuery);
    }
    return !empty($paramData) ? $paramData : null;
  }
  public function bindContractors($data)
  {
    $userId = $data->userId;
    $userWP = $this->userUtilities->getInfoAboutUsers($userId);
    $role = $userWP[$userId]['role'];
    $name = $userWP[$userId]['displayName'];
    $property = $this->propertyDetermine($role);
    if (in_array($role, $this->roleDesigner)) {
      $values = [$userId, $name, $data->id, '', '', '', 'active', '1'];
    } elseif (in_array($role, $this->roleDealer)) {
      $values = [$userId, $name, '', $data->id, '', '', 'active', '2'];
    } elseif (in_array($role, $this->roleDistributor)) {
      $values = [$userId, $name, '', '', $data->id, '', 'active', '3'];
    }
    if (isset($values) || !empty($values)) {
      $this->dbWorker->insertDBU_2('gi_new_users', $values);
      $this->bindedUsers = $this->dbWorker->selectResultsFromDBU_2('gi_new_users');
      $this->prepareAllUsers();
      $idPoint = $this->dataUtilities->sortArray($this->allContractorsWP[$property], 'idUser', $userId, 'idPoint');
      $user = $this->dataUtilities->sortArray($this->allContractorsWP[$property], 'idUser', $userId);
      // $this->catcherBugs->convPrintLog($user, 'bindContractorsMC', '$user');
      // $this->catcherBugs->convPrintLog($idPoint, 'bindContractorsMC', '$idPoint');
      return !empty($idPoint) ? $idPoint : null;
    }
  }
  public function changeStatusContractors($data)
  {
    $userWP = $this->userUtilities->getInfoAboutUsers($data->userId);
    $property = $this->propertyDetermine($userWP[$data->userId]['role']);
    $idPoint = $this->dataUtilities->sortArray($this->allContractorsWP[$property], 'idUser', $data->userId, 'idPoint');
    $this->dbWorker->updateDBU('gi_new_users', 'id_user', $data->userId, 'status_activity', $data->statusActivity);
    $this->bindedUsers = $this->dbWorker->selectResultsFromDBU_2('gi_new_users');
    $this->prepareAllUsers();
    return $idPoint;
  }
  public function changeUserNameContractors($data)
  {
    $result = $this->changeUserName($data->userId, $data->name, $data->lastname);
    $userWP = $this->userUtilities->getInfoAboutUsers($data->userId);
    $property = $this->propertyDetermine($userWP[$data->userId]['role']);
    $idPoint = $this->dataUtilities->sortArray($this->allContractorsWP[$property], 'idUser', $data->userId, 'idPoint');
    $this->dbWorker->updateDBU('gi_new_users', 'id_user', $data->userId, 'user_name', $data->name . ' ' . $data->lastname);
    $this->bindedUsers = $this->dbWorker->selectResultsFromDBU_2('gi_new_users');
    $this->prepareAllUsers();
    return $idPoint;
  }
  public function unbindContractors($data)
  {
    $userWP = $this->userUtilities->getInfoAboutUsers($data->userId);
    $property = $this->propertyDetermine($userWP[$data->userId]['role']);
    $idPoint = $this->dataUtilities->sortArray($this->allContractorsWP[$property], 'idUser', $data->userId, 'idPoint');
    $result = $this->dbWorker->deleteDB('gi_new_users', 'id_user', $data->userId);
    if ($result === false) {
      error_log("Ошибка при удалении записи!");
    } else {
      $this->bindedUsers = $this->dbWorker->selectResultsFromDBU_2('gi_new_users');
      $this->prepareAllUsers();
      return $idPoint;
    }
  }
}