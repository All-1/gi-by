<?php
namespace PersonalAccount\Utilities;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;
class UserUtilities
{
  // use DependencyInjections;
  use Utilit;
  /** @var DBWorker */
  protected $DBWorker;
  /** @var DBUtilities */
  protected $DBUtilities;
  private $CatcherBugs;
  public function __construct($ServicesContainer)
  {
    $this->DBWorker = &$ServicesContainer->get('DBWorker');
    $this->DBUtilities = &$ServicesContainer->get('DBUtilities');
    $this->CatcherBugs = &$ServicesContainer->get('CatcherBugs');
  }
  public function getUserData($userId)
  {
    wp_cache_delete($userId, 'users');
    wp_cache_delete($userId, 'user_meta');
    $userData = get_userdata($userId);
    return $userData;
  }
  public function getUserMeta($userId, $key, $single)
  {
    wp_cache_delete($userId, 'users');
    wp_cache_delete($userId, 'user_meta');
    $userMeta = get_user_meta($userId, $key, $single);
    return $userMeta;
  }
  public function getUserRole($userId, $object = null)
  {
    $userData = $this->getUserData($userId);
    if (!empty($object)) {
      // $object->convPrintLog($userData, 'searchForBindContractorsMC', '$role', 1);
    }
    $userRole = !empty($userData->roles[0]) ? $this->determineUserRoleDealer($userData->roles[0]) : '';
    // $this->CatcherBugs->convPrintLog($userData->roles[0], 'getUserRole', '$userData->roles[0]');
    // $this->CatcherBugs->convPrintLog($userData, 'getUserRole', '$userData');
    return $userRole;
  }
  public function determineUserRoleDealer($role)
  {
    if ($role === 'subscriber') {
      $role = 'dealer';
    }
    return $role;
  }
  public function extractUserParam($userInfo, $param, $userId, $where)
  {
    $value = '';
    if (!empty($userInfo)) {
      $value = $userInfo->$param;
    } else {
      error_log("extractUserParam: User with id $userId didn't find in $where");
    }
    // return $this->CatcherBugs->checkReturn($value, 'extractUserParam', $param);

    return $value;
  }
  public function packegeUserForClient($users, $idUserInArr)
  {
    $role = $users[$idUserInArr]['role'];

    $user = [
      'idUser' => $idUserInArr,
      'role' => $role,
      'firstname' => get_user_meta($idUserInArr, 'first_name', true),
      'lastname' => get_user_meta($idUserInArr, 'last_name', true),
      'statusActivity' => $this->DBWorker->selectSimple('Users', 'id_user', $idUserInArr, 'status_activity'),
      'whose' => $this->partyVerification($role)
    ];
    if ($role === 'manager') {
      $where = ['id_manager', 'state'];
      $value = [
        'id_manager' => $user['idUser'],
        'state' => 1
      ];
      $user['points'] = $this->DBWorker->selectResultsFromDBU_2('gi_new_points', $where, $value, 'id_point');
    }
    return $user;
  }

  public function packageUser($sqlUsers)
  {
    $users = [];
    $sqlUsers = is_array($sqlUsers) ? $sqlUsers : [$sqlUsers];
    foreach ($sqlUsers as $idUser) {
      $idUser = intval($idUser);
      $users[$idUser] = [];
      $users[$idUser]['id'] = $idUser;
      $users[$idUser]['role'] = $this->getUserRole(intval($idUser));
      $users[$idUser]['area'] = $this->areaVerification($users[$idUser]['role']);
      $users[$idUser]['statusActivity'] = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'status_activity');
      // $this->DBWorker->getStatusActivityDB(intval($idUser));
    }
    return $users;
  }
  public function getInfoAboutUsers($users, $separate = false)
  {
    $allInfoUsers = [];
    $users = is_array($users) ? $users : [$users];
    foreach ($users as $id) {
      if ($id && (is_string($id) || is_int($id))) {
        $allInfoUsers[$id] = [];
        $userInfo = $this->getUserData($id);
        if (!$userInfo) {
          continue; // Пропускаем итерацию, если пользователь не найден
        }
        $allInfoUsers[$id]['id'] = $id;

        $allInfoUsers[$id]['role'] = $this->getUserRole($id);
        $allInfoUsers[$id]['whose'] = $this->partyVerification($allInfoUsers[$id]['role']);
        $allInfoUsers[$id]['displayName'] = $userInfo->last_name . ' ' . $userInfo->first_name;
        $allInfoUsers[$id]['rank'] = $this->DBWorker->selectSimple('Users', 'id_user', $id, 'user_rank');
        if (!$separate) {
          $allInfoUsers[$id]['name'] = $userInfo->first_name;
          $allInfoUsers[$id]['lastname'] = $userInfo->last_name;
        }
      }
    }
    return $allInfoUsers;
  }
  public function getNameUser($userId)
  {
    $author = $this->getInfoAboutUsers($userId);
    $nameAuthor = isset($author[$userId]) ? $author[$userId]['name'] . ' ' . $author[$userId]['lastname'] : '';
    return $nameAuthor;
  }
  public function getUserEmail($userId)
  {
    $userInfo = get_userdata($userId);

    if ($userInfo) {
      $email = $userInfo->user_email;
      return $email;
    } else {
      error_log("Пользователь с ID $userId не найден.");
    }
  }
  public function changeStatusToRus($status, $mode = 2)
  {
    $statusRus = '';
    if ($mode === 2) {
      if ($status === 'active' || $status === 'noactive') {
        $statusRus = 'активный';
      } else {
        $statusRus = 'заблокирован';
      }
    } else if ($mode === 3) {
      if ($status === 'active') {
        $statusRus = 'активный';
      } else if ($status === 'noactive') {
        $statusRus = 'не работает';
      } else if ($status === 'blocked') {
        $statusRus = 'заблокирован';
      }
    }
    return $statusRus;
  }
  public function checkUserAccess($idUser)
  {
    $userRole = $this->getUserRole($idUser);
    return $this->partyVerification($userRole);
  }
  public static function partyVerification($userRole)
  {
    $whose = '';
    switch ($userRole) {
      case "manager":
      case "consultant":
      case "complaint_handler":
      case "bookkeeper":
      case "shipment_manager":
      case "specialist":
      case "administrator":
      case "sales_manager":
        $whose = 'factory_worker';
        break;
      case "distributor":
      case "free_dealer":
      case "dealer":
      case "designer_dealer":
        $whose = 'contractor';
        break;
    }
    return $whose;
  }
  public static function areaVerification($userRole)
  {

    $area = '';
    switch ($userRole) {
      case "manager":
      case "consultant":
      case "complaint_handler":
        $area = 'contract';
        break;
      case "bookkeeper":
        $area = 'accounting';
        break;
      case "shipment_manager":
        $area = 'shipment';
        break;
      case "specialist":
        $area = 'specialist';
        break;
      case "administrator":
        $area = 'administrator';
        break;
      case "distributor":
        $area = 'distributor';
        break;
      case "free_dealer":
      case "dealer":
      case "designer_dealer":
        $area = 'contractor';
        break;
    }
    return $area;
  }
  public function checkRecieveUserRole($needle, $role)
  {
    foreach ($needle as $item) {
      if (in_array($item, $role)) {
        return true;
      }
    }
  }
  public function getManagerForPoint($idPoint)
  {
    $idPoint = is_array($idPoint) ? $idPoint[0] : $idPoint;
    $myManager = $this->DBWorker->selectVarFromDBU('gi_new_points', 'id_point', $idPoint, 'id_manager');
    $myManager = $this->findReplacement($myManager);
    return $myManager;
  }
  protected function findReplacement($idUser)
  {
    $statusActivity = $this->DBWorker->selectVarFromDBU('gi_new_users', 'id_user', $idUser, 'status_activity');
    if ($statusActivity === 'active') {
      return $idUser;
    } else {
      $myManager = $this->DBWorker->selectVarFromDBU('gi_new_users', 'id_user', $idUser, 'id_replacement');
      if (!empty($myManager)) {
        return $this->findReplacement($myManager); // return
      } else {
        return $idUser;
      }
    }
  }
  public function initializationRolesFactory()
  {
    $rolesFactory = [
      'manager',
      'consultant',
      'complaint_handler',
      'bookkeeper',
      'shipment_manager',
      'specialist',
      'administrator',
      'sales_manager'
    ];
    return $rolesFactory;
  }
  public function initializationRolesContractor()
  {
    $rolesContractor = [
      'distributor',
      'free_dealer',
      'dealer',
      'designer_dealer',
      'subscriber',
    ];
    return $rolesContractor;
  }
  public function getUsersByRoles($roles)
  {
    $users = get_users(array('role__in' => $roles));
    return $users;
  }
  public function getFactoryUsers()
  {
    wp_cache_flush();
    $rolesFactory = $this->initializationRolesFactory();
    $users = $this->getUsersByRoles($rolesFactory);
    $usersIds = $this->getUsersIds($users);
    return $usersIds;
  }
  public function getContractorUsers()
  {
    wp_cache_flush();
    $rolesContractor = $this->initializationRolesContractor();
    $users = $this->getUsersByRoles($rolesContractor);
    $usersIds = $this->getUsersIds($users);
    return $usersIds;
  }
  public function getUsersIds($users)
  {
    $result = [];
    foreach ($users as $user) {
      $result[] = $user->ID;
    }
    return $result;
  }
  public function getAllPointsUser($idUser)
  {
    $userRole = $this->getUserRole($idUser);
    $userArea = $this->areaVerification($userRole);
    $AllPoints = [];
    if ($userArea === 'factory_worker') {
      $AllPoints = $this->DBWorker->selectSimple('Points', null, null, 'id_point');
    } elseif ($userRole === 'free_dealer' || $userRole === 'dealer') {
      $idUP = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'id_dealer');
      $AllPoints = $this->DBWorker->selectSimple('Points', 'id_dealer', $idUP, 'id_point');
    } elseif ($userRole === 'distributor') {
      $idUP = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'id_distributor');
      $idDealersUP = $this->DBWorker->selectSimple('Dealers', 'id_distributor', $idUP, 'id_dealer');
      $conditions = $this->DBUtilities->createConditionQueryIN('id_dealer', $idDealersUP);
      $AllPoints = $this->DBWorker->selectUni('Points', $conditions, 'id_point');
    } elseif ($userRole === 'designer_dealer') {
      $idPoint = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'id_point');
      $idMyDealerUP = $this->DBWorker->selectVarSimple('Points', 'id_point', $idPoint, 'id_dealer');
      $AllPoints = $this->DBWorker->selectSimple('Points', 'id_dealer', $idMyDealerUP, 'id_point');
    }
    return $AllPoints;
  }
  private function packagePotencialParticipiant($users, $user, $id)
  {
    $userDetails = $users;
    $status = $this->DBWorker->selectVarSimple('Users', 'id_user', $id, 'status_activity');
    if ($status === 'active') {
      if (!empty($user)) {
        $userDetails[$id]['id'] = $user['id'];
        $userDetails[$id]['displayName'] = $user['displayName'];
        $userDetails[$id]['role'] = $user['role'];
        $userDetails[$id]['whose'] = $user['whose'];
      }
    }
    return $userDetails;
  }
  public function preparingPotencialParticipant($potentialParticipant, $idUser)
  {
    $userDetails = [];
    // Перебираем каждого пользователя
    $userRank = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'user_rank');
    if (!empty($potentialParticipant)) {
      foreach ($potentialParticipant as $id => $user) {
        // Извлекаем необходимые данные
        $status = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'status_activity');
        if ($status === 'active' && !empty($user)) {
          if ($userRank > 4) {
            if ($user['rank'] >= 2 && $idUser !== $id) {
              $userDetails = $this->packagePotencialParticipiant($userDetails, $user, $id);
            }
          } else {
            if ($user['rank'] <= $userRank + 1) {
              $userDetails = $this->packagePotencialParticipiant($userDetails, $user, $id);
            }
          }
        }
      }
      return $userDetails;
    } else {
      error_log('getHaveAccessPoint $point empty');
    }
  }
  public function getUserReplacement($idUser)
  {
    $conditions = $this->DBUtilities->preparenSingleOperSepar('id_user', $idUser, ' = ', '');
    $idRepalcement = $this->DBWorker->selectUni('Users', $conditions, 'id_replacement');
    return $idRepalcement;
  }
  public function defineUserRank($idUser)
  {
    $userRole = $this->getUserRole($idUser);
    $userRank = 1;
    switch ($userRole) {
      case 'manager':
      case 'consultant':
      case 'complaint_handler':
      case 'bookkeeper':
      case 'shipment_manager':
      case 'specialist':
      case 'administrator':
      case 'sales_manager':
        $userRank = 5;
        break;
      case 'distributor':
        $userRank = 3;
        break;
      case 'free_dealer':
        $userRank = 2;
        break;
      case 'dealer':
        $userRank = 2;
        break;
      case 'subscriber':
        $userRank = 2;
        break;
      case 'designer_dealer':
        $userRank = 1;
        break;
    }
    return $userRank;
  }
  public function translateUserRole($userRole)
  {
    $userRole = match ($userRole) {
      'manager' => 'Менеджер',
      'consultant' => 'Консультант',
      'complaint_handler' => 'Специалист по качеству',
      'bookkeeper' => 'Бухгалтер',
      'shipment_manager' => 'Менеджер по отгрузке',
      'specialist' => 'Узкий специалист',
      'administrator' => 'Админ',
      'sales_manager' => 'Менеджер по продажам',
      'distributor' => 'Дистрибьютер',
      'free_dealer' => 'Дилер',
      'dealer' => 'Дилер',
      'designer_dealer' => 'Дизайнер салона',
      'subscriber' => 'Подписчик',
    };
    return $userRole;
  }
  public function getUserDealerId(int $idUser): int
  {
    $userRole = $this->getUserRole($idUser);
    if ($userRole === 'free_dealer' || $userRole === 'dealer' || $userRole === 'distributor') {
      // $idDealer = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'id_dealer');
      return $idUser;
    } else if ($userRole === 'designer_dealer') {
      $idPoint = $this->DBWorker->selectVarSimple('Users', 'id_user', $idUser, 'id_point');
      $idDealer = $this->DBWorker->selectVarSimple('Points', 'id_point', $idPoint, 'id_dealer');
      $idDealer = $this->DBWorker->selectVarSimple('Users', 'id_dealer', $idDealer, 'id_user');
      if (empty($idDealer)) {
        $idDistributor = $this->DBWorker->selectVarSimple('Users', 'id_distributor', $idUser, 'id_user');
        return $idDistributor ?? 0;
      }
      return $idDealer ?? 0;
    }
    return 0;
  }
}
?>