<?php

namespace PersonalAccount\Controler;
use PersonalAccount\Core\Container;
use PersonalAccount\Factory\Factory;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\UPWorker;
use PersonalAccount\Workers\CatcherBugs;

use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\DBUtilities;

use PersonalAccount\Users\UserMain;
use PersonalAccount\Users\Admin;
use PersonalAccount\Users\SalesManager;
use PersonalAccount\Users\FactoryWorker;
use PersonalAccount\Users\Bookkeeper;
use PersonalAccount\Users\Distributor;
use PersonalAccount\Users\FreeDealer;
use Exception;
use PersonalAccount\Users\Contractor;

class UserControler
{
  // use DependencyInjections; //This is the main trait for all system.
  use Utilit; //Содержаться все функции которые имеют доступ к базе данных, но нужны в разных объектах, а так же общая функция для создания капсулы
  /** @var Container */
  private $container;
  /** @var UPWorker */
  private $upWorker;
  /** @var UserUtilities */
  private $userUtilities;
  /** @var SimpleUtilities */
  private $simpleUtilities;
  /** @var Factory */
  private $factory;
  /** @var DataUtilities */
  private $dataUtilities;
  /** @var DBWorker */
  protected $dbWorker;
  /** @var DBUtilities */
  protected $dbUtilities;
  /** @var CatcherBugs */
  private $catcherBugs;
  private $usersContractor;
  private $usersfactoryWorker;
  private $usersOnPoints;
  private $usersIdWebsocket;
  public function __construct($container)
  {

    $this->initializationWPDB();
    $this->container = &$container;
    $this->upWorker = $this->container->get('UPWorker');
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->factory = $this->container->get('Factory');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->dbWorker = $this->container->get('DBWorker');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->usersContractor = [];
    $this->usersfactoryWorker = [];
    $this->usersOnPoints = [];
    $this->usersIdWebsocket = [];
    $logIdDealer = $this->upWorker->getLogIdDealer();
    $logIdFirm = $this->upWorker->getLogIdFirm();
    $this->requestUpdateDealerUP($logIdDealer);
    $this->requestUpdateFirmUP($logIdFirm);
  }
  private function setSideUser($idUser)
  {
    $userRole = $this->userUtilities->getUserRole($idUser);
    $whose = $this->userUtilities->partyVerification($userRole);
    $property = '';
    if ($whose === 'factory_worker') {
      $property = 'usersfactoryWorker';
    } elseif ($whose === 'contractor') {
      $property = 'usersContractor';
    }
    return $property;
  }
  
  private function findUsersByPoints($idPoints)
  {
    $usersOnPoints = [];
    $idPoints = $this->simpleUtilities->toArray($idPoints);
    if (!empty($this->usersOnPoints)) {
      foreach ($idPoints as $idPoint) {
        if (!empty($this->usersOnPoints[$idPoint])) {
          foreach ($this->usersOnPoints[$idPoint] as &$user) {
            if (!empty($user)) {
              $userId = $user->getUserId();
              $usersOnPoints[$userId] = $user;
            }
          }
        }
      }
    }
    return $usersOnPoints;
  }
  private function findUserContractor($idUser)
  {
    $whose = $this->userUtilities->checkUserAccess($idUser);
    if ($whose === 'contractor') {
      if (!empty($this->usersContractor)) {
        $contractor = &$this->usersContractor[$idUser];
        return $contractor;
      }
    }
    return null;
  }
  public function findUserOnline($idUser)
  {
    $property = $this->setSideUser($idUser);
    if (!empty($this->$property)) {
      if (!empty($this->$property[$idUser])) {
        return true;
      }
      return false;
    }
  }
  private function findUserById($idUser)
  {
    $property = $this->setSideUser($idUser);
    $user = &$this->$property[$idUser];
    return !empty($user) ? $user : null;
  }

  public function findUserByIdWebsocket($idWebsocket)
  {
    /** @var UserMain $user */
    if (!empty($this->usersIdWebsocket[$idWebsocket])) {
      $user = &$this->usersIdWebsocket[$idWebsocket];
      return $user;
    }
    return null;
  }
  private function addParramForUser($newUser, $idWebsocket)
  {
    try {
      /** @var UserMain $newUser */
      $newUser->addWebsocketId($idWebsocket);
      $newUser->showContractsOnPage();
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'addParramForUser'];
    }
  }
  private function updateManagerByUserID($userId)
  {
    /** @var Contractor $contractor */
    foreach ($this->usersContractor as &$contractor) {
      if (!empty($contractor)) {
        $managerId = $contractor->getOutsideManagerMyPoint();
        if ($managerId == $userId) {
          // $this->catcherBugs->convPrintLog($userId, 'updateManagerByUserID', '$userId');
          $contractor->updateMyManager();
        }
      }
    }
  }
  private function deleteDisconnectedUser($user, $idWebsocketCurrent)
  {
    /** @var UserMain $user */
    $idWebsocketsUser = $user->outsideWebsocketId();
    // error_log(print_r($idWebsocketsUser, true));
    if (count($idWebsocketsUser) > 1) {
      foreach ($idWebsocketsUser as $key => $idWebsockets) {
        if ($idWebsockets === $idWebsocketCurrent) {
          unset($idWebsocketsUser[$key]);
          $user->setIdWebsocket($idWebsocketsUser);
          $idWebsocketsUserNew = $user->outsideWebsocketId();
          // error_log(print_r($idWebsocketsUser, true));
          // error_log(print_r($idWebsocketsUserNew, true));
          return true;
        }
      }
    } else if (count($idWebsocketsUser) == 1) {
      return false;
    }
  }
  public function addUsersContractor($idUser, $idWebsocket)
  {

    // $this->catcherBugs->convPrintLog($idUser, 'addUsersContractor', '$idUser');
    $property = $this->setSideUser($idUser);
    // $this->catcherBugs->convPrintLog($property, 'addUsersContractor', '$property');

    if ($property) {
      if (!isset($this->$property[$idUser])) {
        $this->$property[$idUser] = $this->factory->createUser($idUser, $this->container);
        // $this->catcherBugs->convPrintLog($property, 'addUsersContractor', '$property');
        /** @var UserMain */
        $user = &$this->$property[$idUser];
        $this->addParramForUser($user, $idWebsocket);
        $points = $user->getAllPointsId();
        $this->usersIdWebsocket[$idWebsocket] = $user;
        $points = $this->simpleUtilities->toArray($points);
        foreach ($points as $idPoint) {
          $this->usersOnPoints[$idPoint][] = $user;
        }
      } else {
        /** @var UserMain */
        // Пользователь с таким идентификатором WebSocket уже существует
        $user = &$this->$property[$idUser];
        $user->addWebsocketId($idWebsocket);
        $this->usersIdWebsocket[$idWebsocket] = $user;
      }
      // $this->catcherBugs->convPrintLog($property, 'addUsersContractor', '$property');
      if (!empty($user)) {
        // $this->catcherBugs->convPrintLog($property, 'addUsersContractor', '$property');
        $paramForClient = $user->outsideParamForClient();
        return $paramForClient;
      }

    } else {
      throw new Exception("User with idUser --- $idUser --- was refuse to give access.");
    }
  }

  public function checkDisconnectedUser($idWebsocketCurrent)
  {
    $userId = $this->getUserIdByIdWebsocket($idWebsocketCurrent);
    $property = $this->setSideUser($userId);
    if (isset($this->$property[$userId])) {
      $user = &$this->$property[$userId];
      if (!empty($user)) {
        $disconect = $this->deleteDisconnectedUser($user, $idWebsocketCurrent);
      }
      // $idWebsocketsUser = $this->usersfactoryWorker[$userId]->outsideWebsocketId();
      if (!$disconect) {
        // error_log('disconect');
        unset($this->$property[$userId]);
        if (isset($this->$property[$userId])) {
          // error_log('user exist');
        }
      } else {
        error_log('disconect left');
      }
      return $userId;
    }
  }

  public function logicCreateContract($idUser, $nameContract)
  {
    /** @var Contractor $contractor */
    $contractor = $this->findUserContractor($idUser);
    if (!empty($contractor)) {
      $serialNumber = $contractor->createContract($nameContract);
      return $serialNumber;
    } else {
      error_log('logicCreateContract $point empty');
      return null;
    }
  }
  /*



  This set of method MUST be replace 




   */
  public function setUserCurrentPage($idWebsocket, $currentPage, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'contracts':
          $currentUser->setCurrentPageContracts($currentPage);
          break;
        case 'orders':
          $currentUser->setCurrentPageOrders($currentPage);
          break;
        case 'contractors':
          /** @var Admin & SalesManager $currentUser */
          $currentUser->setCurrentPageContractorsMC($currentPage);
          break;
        case 'invoices':
          /** @var factoryWorker & Distributor & FreeDealer $currentUser */
          $currentUser->setCurrentPageInvoices($currentPage);
          break;
      }
    } else {
      error_log('setCurrentPageOrders $currentUser empty');
    }
  }
  public function setUserPerPage($idWebsocket, $perPage, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'contracts':
          $currentUser->setPerPageContracts($perPage);
          break;
        case 'orders':
          $currentUser->setPerPageOrders($perPage);
          break;
        case 'contractors':
          /** @var Admin & SalesManager $currentUser */
          if (method_exists($currentUser, 'setPerPageContractorsMC')) {
            $currentUser->setPerPageContractorsMC($perPage);
          }
          break;
        case 'invoices':
          /** @var factoryWorker & Distributor & FreeDealer $currentUser */
          if (method_exists($currentUser, 'setPerPageInvoices')) {
            $currentUser->setPerPageInvoices($perPage);
          }
          break;
      }
    } else {
      error_log('setPerPageOrders $currentUser empty');
    }
  }
  public function setUserSearchQuery($idWebsocket, $searchQuery, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'contracts':
          $currentUser->setSearchQueryContracts($searchQuery);
          break;
        case 'orders':
          $currentUser->setSearchQueryOrders($searchQuery);
          break;
        case 'contractors':
          /** @var Admin & SalesManager $currentUser */
          $currentUser->setSearchQueryContractors($searchQuery);
          break;
        case 'invoices':
          /** @var factoryWorker & Distributor & FreeDealer $currentUser */
          $currentUser->setSearchQueryInvoices($searchQuery);
          break;
      }
    } else {
      error_log('setUserSearchQuery $currentUser empty');
    }
  }
  public function setConditionForSearch($idWebsocket, $body, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'orders':
          $currentUser->setConditionForSearchOrders($body);
          break;
      }
    } else {
      error_log('setConditionForSearch $currentUser empty');
    }
  }
  public function setUserFilterContracts($idWebsocket, $filter, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'contracts':
          $currentUser->setFilterContracts($filter);
          break;
        case 'orders':
          $currentUser->setFilterOrders($filter);
          break;
        case 'filterRoleContractors':
          /** @var Admin & SalesManager $currentUser */
          $currentUser->setFilterRoleContractorsMC($filter);
          break;
        case 'filterStatusContractors':
          /** @var Admin & SalesManager $currentUser */
          $currentUser->setFilterStatusContractorsMC($filter);
          break;
      }
    } else {
      error_log('setUserFilterContracts $currentUser empty');
    }
  }
  public function setStartDateUC($idWebsocket, $date, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'orders':
          $currentUser->setStartDateOrders($date);
          break;
      }
    } else {
      error_log('setUserFilterContracts $currentUser empty');
    }
  }
  public function setEndDateUC($idWebsocket, $date, $where)
  {
    /** @var UserMain $currentUser */
    $currentUser = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($currentUser)) {
      switch ($where) {
        case 'orders':
          $currentUser->setEndDateOrders($date);
          break;
      }
    } else {
      error_log('setUserFilterContracts $currentUser empty');
    }
  }
  /*



  This set of method MUST be replace 




   */
  public function regNewDialogUser($idUser, $newDialog)
  {
    /** @var Contractor $contractor */
    $contractor = $this->findUserContractor($idUser);
    if (!empty($contractor)) {
      $idDialog = $contractor->createDialog($newDialog->serialNumber, $newDialog->typeDialog);
      $newDialog->idDialog = $idDialog;
      return $newDialog;
    } else {
      error_log('regNewDialogUser $contractor empty');
    }
    return null;
  }
  public function regNewMessage($newMessage, $idUser)
  {
    /** @var UserMain $user */
    $user = $this->findUserById($idUser);
    if (!empty($user)) {
      $newMessage = $user->writeNewMessage($newMessage);
      return $newMessage;
    } else {
      error_log('regNewMessage $newMessage empty');
    }
  }
  private function getIdWebsocketForUpdate($usersForUpdate)
  {
    $websocketIds = [];
    if (!empty($usersForUpdate)) {
      /** @var UserMain $user */
      foreach ($usersForUpdate as $user) {
        if (!empty($user)) {
          if (method_exists($user, 'outsideWebsocketId')) {
            $currentWebsocketId = $user->outsideWebsocketId();
            // $this->catcherBugs->convPrintLog($currentWebsocketId, 'getIdWebsocketForUpdate','$currentWebsocketId');
            $websocketIds = $this->simpleUtilities->mergeArrays($websocketIds, $currentWebsocketId);
            // $this->catcherBugs->convPrintLog($websocketIds, 'getIdWebsocketForUpdate','$websocketIds');
          }
        }
      }
      if (!empty($websocketIds)) {
        $websocketIds = array_unique($websocketIds);
        return $websocketIds;
      } else {
        error_log('getIdWebsocketUsers $websocketIds empty');
      }
    }
  }
  //Write this function. You need to consider who can change status whose 
  private function checkRightsDoChanges($userWhoChange, $userId)
  {
    // $userIdWhoChange = $userWhoChange->getUserId();
    // $userRole = serviceUser::getUserRole($userIdWhoChange);
    // $whose = serviceUser::partyVerification($userRole);
    // $checkParty = $whose === 'factory_worker' ? true : false;
    // $checkDealer = $userRole === 'dealer' ? true : false;
    // $checkRights = $checkDealer ? $userWhoChange->
  }
  public function getIdWebsocketUsersUC($idPoint = null, $allSystem = false)
  {
    if (!empty($idPoint)) {
      $usersOnPoint = $this->findUsersByPoints($idPoint);
      $usersForUpdate = $this->simpleUtilities->mergeArrays($this->usersfactoryWorker, $usersOnPoint);
    } else if ($allSystem) {
      $usersForUpdate = $this->simpleUtilities->mergeArrays($this->usersfactoryWorker, $this->usersContractor);
    } else {
      $usersForUpdate = $this->usersfactoryWorker;
    }
    $websocketIds = $this->getIdWebsocketForUpdate($usersForUpdate);
    if (!empty($websocketIds)) {
      return $websocketIds;
    } else {
      error_log('getIdWebsocketUsers $websocketIds empty');
    }
  }
  public function sortUsersWhoSeeContract($websocketIds, $serialNumber)
  {
    $usersForUpdate = [];
    foreach ($websocketIds as $websocketId) {
      $user = $this->findUserByIdWebsocket($websocketId);
      if (!empty($user)) {
        $seeContract = $user->seeContract($serialNumber);
        if ($seeContract) {
          $usersForUpdate[] = $websocketId;
        }
      }
    }
    return $usersForUpdate;
  }
  public function getIdWebsocketByUserId($idUser)
  {
    $property = $this->setSideUser($idUser);
    if (!empty($this->$property)) {
      /** @var UserMain $user */
      foreach ($this->$property as $key => &$user) {
        if ($idUser === $key) {
          if (!empty($user)) {
            $idWebsocket = $user->outsideWebsocketId();
            return $idWebsocket;
          }
        }
      }
      return false;
    }
  }
  public function setReplacementForUserUC($userId, $hisReplacementId, $idUserWhoChange)
  {
    /** @var UserMain $userWhoChange  */
    $userWhoChange = $this->findUserByIdWebsocket($idUserWhoChange);
    $idDialogues = $userWhoChange->changeResponsibleDialogs($userId, $hisReplacementId);
    
    $replacement = $this->findUserOnline($hisReplacementId);
    if (!empty($replacement)) {
      /** @var UserMain $replacement  */
      $replacement = $this->findUserById($hisReplacementId);
      $replacement->updateReplacement();
    }
    return $idDialogues;
  }
  //Rebuild this chain of Function
  public function setStatusUserUC($userId, $statusActivity, $idUserWhoChange)
  {
    $userWhoChange = $this->findUserByIdWebsocket($idUserWhoChange);
    if ($userWhoChange) {
      // I'm going to finish this additional checkup a little bit later
      // $checkHisRights = $this->checkRightsDoChanges($userWhoChange, $userId); 
      /** @var UserMain $userWhoChange  */
      $userWhoChange->changeStatusUserUM($userId, $statusActivity);
      $userRole = $this->userUtilities->getUserRole($userId);
      if ($statusActivity === 'active') {
        // $idReplacement = $userWhoChange->getBackDialogs($userId);
        $userWhoChange->deleteReplacementUM($userId);
      }
      
    }
    $idUsersOnline = $this->changeStatusUserOnline($userId);
    // return $this->catcherBugs->checkReturn($idUsersOnline, 'setStatusUserUC', '$idUsersOnline');
    return $idUsersOnline;
  }
  private function changeStatusUserOnline(...$usersId)
  {
    if (!empty($usersId)) {
      $userPool = is_array($usersId) ? $usersId : [$usersId];
      $usersOnline = [];
      foreach ($userPool as $userId) {
        $userCheck = $this->findUserOnline($userId);
        if ($userCheck) {
          /** @var UserMain $user  */
          $user = $this->findUserById(intval($userId));
          if (!empty($user)) {
            $user->updateStatus();
            $usersOnline[] = $userId;
          }
        }
      }
      return $usersOnline;
    }
  }
  public function updateForOtherUsers($userId, $idPoint)
  {
    $userRole = $this->userUtilities->getUserRole($userId);
    $whose = $this->userUtilities->partyVerification($userRole);
    // $property = $this->setSideUser($idUser);
    if ($whose === 'factory_worker') {
      /** @var factoryWorker $user */
      foreach ($this->usersfactoryWorker as $user) {
        if (!empty($user)) {
          $user->updateForClientStatusWorkers();
        }
      }
      $this->updateManagerByUserID($userId);
    } elseif ($whose === 'contractor') {
      $usersOnPoint = $this->findUsersByPoints($idPoint);
      if (!empty($usersOnPoint)) {
        /** @var Contractor $user */
        foreach ($usersOnPoint as $user) {
          $user->updateForClientStatusWorkers();

        }
      }
    }
  }
  public function savePointsUC($data, $idWebsocket)
  {
    /** @var Admin & SalesManager This is the name of the method, e.g. 'prepareDealer' or 'preparePoint' */
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      $user->savePointsNew($data);
      $usersForUpdate = $this->getIdWebsocketForUpdate($this->usersfactoryWorker);
      return $usersForUpdate;
    }
  }
  public function changePoints($idWbsockeet, $userId, $hisReplacementId)
  {
    /** @var factoryWorker $whoChange */
    $userWhoChange = $this->findUserByIdWebsocket($idWbsockeet);
    $userRole = $this->userUtilities->getUserRole($userId);
    if ($userRole === 'manager' && !empty($userWhoChange) && method_exists($userWhoChange, 'outsideWebsocketId')) {
      // $this->catcherBugs->convPrintLog($userRole, 'changePoints', '$userRole');
      $points = $userWhoChange->changePoints($userId, $hisReplacementId);
      return $points;
    }
  }
  public function updateManagerByPointIdUC($idPoints)
  {
    /** @var UserMain $user */
    $users = $this->findUsersByPoints($idPoints);
    if (!empty($users)) {
      foreach ($users as &$user) {
        $user->updateMyManager();
      }
    }
  }
  public function updateOnPointAfterBind($idPoints)
  {
    try {
      $usersOnPoint = $this->findUsersByPoints($idPoints);
      if (!empty($usersOnPoint)) {
        foreach ($usersOnPoint as &$user) {
          $user->updateAfterBindUser();
        }
      } else {
        error_log('getIdWebsocketUsers $websocketIds empty');
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'getUsersAfterBind'];
    }
  }
  public function reloadUser($userId)
  {
    /** @var UserMain $user */
    $user = $this->findUserById($userId);
    if (!empty($user)) {
      $idWebsocketsUser = $user->outsideWebsocketId();
      $user->updateAfterBindUser();
      return $idWebsocketsUser;
    } else {
      error_log('reloadUser empty($user)');
    }
  }
  public function reloadfactoryWorker($idWebsocket)
  {
    /** @var UserMain $user */
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      $idWebsocketsUser = $user->outsideWebsocketId();
      $user->bindOnMyOwn();
      $user->updateAfterBindUser();
      return $idWebsocketsUser;
    } else {
      error_log('reloadUser empty($user)');
    }
  }
  public function getUpdateUserParamUC($idWebsocket)
  {
    /** @var UserMain $user */
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      $user->updateForClientStatusWorkers();
      $paramForClient = $user->outsideParamForClient();
      return $paramForClient;
    }
  }
  //This is a generic method that return some stuff from user. 
  public function getFromUserStuff($method, $idWebsocket, $data = null)
  {
    try {
      $user = $this->findUserByIdWebsocket($idWebsocket);

      if (!empty($user)) {
        // $this->catcherBugs->convPrintLog('INSIDEif (!empty($user)) ' . $method, 'getFromUserStuff', '$method');
        if (method_exists($user, $method)) {
          // If the method exists, operation done
          // $this->catcherBugs->convPrintLog('INSIDE if (method_exists($user, $method)) ' . $method, 'getFromUserStuff', '$methode');
          $result = $data === null ? $user->$method() : $user->$method($data);
          return !empty($result) ? $result : null;
        } else {
          // Метод отсутствует
          error_log("getFromUserStuff: else method_exists(user, $method)");
        }
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'getFromUserStuff'];
    }
  }

  // METHOD CAN BE REPLACE EVERYWHERE

  public function getUserIdByIdWebsocket($idWebsocket)
  {
    /** @var UserMain $user */
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if ($user) {
      $idUser = $user->getUserId();
      return $idUser;
    } else {
      error_log("User with idWebsocket --- $idWebsocket --- didn't find in getUserIdByIdWebsocket.");
    }
  }
  // This methsod will be used in new class StateControler
  public function requestUpdateDealerUP($logId = null)
  {
    $dealersForUpdate = $this->upWorker->requestUpdateFromUP('dealer', $logId);
    // $this->catcherBugs->convPrintLog($dealersForUpdate, 'requestUpdateDealerUP', '$dealersForUpdate', 2);
    if (!empty($dealersForUpdate['update'])) {
      $idDealersUpdate = $this->dataUtilities->sortArray($dealersForUpdate['update'], 'State', 0, 'PKId');
      if (!empty($idDealersUpdate)) {
        // $this->catcherBugs->convPrintLog($idDealersUpdate, 'requestUpdateDealerUP', '$idDealersUpdate', 2);

        $idUpdateDesignersFromWP = $this->requireIdDesigners($idDealersUpdate);
        // $this->catcherBugs->convPrintLog($idUpdateDesignersFromWP, 'requestUpdateDealerUP', '$idUsersWP', 2);

        $idUpdatedDealersDistribution = $this->requireIdDealersDistributorsWP($idDealersUpdate);
        // $this->catcherBugs->convPrintLog($idUpdatedDealersDistribution, 'requestUpdateDealerUP', '$idUpdatedDealersDistribution', 2);

        $idUsersWP = $this->simpleUtilities->combineValues($idUpdateDesignersFromWP, $idUpdatedDealersDistribution);
        // $this->catcherBugs->convPrintLog($idUsersWP, 'requestUpdateDealerUP', '$idUsersWP', 2);
        $this->updateStatusUsers($idUsersWP);
        // return $this->catcherBugs->checkReturn($idUsersWP, 'requestUpdateDealerUP', 'idDealersUpdate');
        return $idUsersWP;
      }
    }
  }

  public function requestUpdateFirmUP($logId)
  {
    $firm = $this->upWorker->requestUpdateFromUP('firm', $logId);

    return $firm;
  }
  private function requireIdDealersDistributorsWP($idDealersUpdate)
  {
    $updatedDealersFromDB = $this->dbWorker->selectResultsFromDBU_2('gi_new_users', 'id_dealer', $idDealersUpdate, 'id_user');
    $updatedDistributerFromDB = $this->dbWorker->selectResultsFromDBU_2('gi_new_users', 'id_distributor', $idDealersUpdate, 'id_user');
    $idUsersWP = $this->simpleUtilities->combineValues($updatedDistributerFromDB, $updatedDealersFromDB);

    return $idUsersWP;
  }
  private function requireIdDesigners($idDealersUpdate)
  {
    $idPoints = $this->dbWorker->selectResultsFromDBU_2('gi_new_points', 'id_dealer', $idDealersUpdate, 'id_point');
    // $this->catcherBugs->convPrintLog($idPoints, 'requireIdDesigners', '$idPoints', 2);
    if (!empty($idPoints)) {
      $idDesigners = $this->dbWorker->selectResultsFromDBU_2('gi_new_users', 'id_point', $idPoints, 'id_user');
      // $this->catcherBugs->convPrintLog($idDesigners, 'requireIdDesigners', '$idDesigners', 2);
      return $idDesigners;
    }
  }
  private function updateStatusUsers($idUsersWP)
  {
    // $this->catcherBugs->convPrintLog($idUsersWP, 'updateStatusUsers', '$idUsersWP', 2);
    if (!empty($idUsersWP)) {
      $whereSqlUpdate = $this->dbUtilities->createConditionQueryIN('id_user', $idUsersWP);
      $setSqlUpdate = $this->dbUtilities->preparenSingleOperSepar('status_activity', 'blocked', ' = ', '');
      $valuesEnable = $whereSqlUpdate['values'];
      $valuesEnable = array_merge(array_values($setSqlUpdate['values']), $valuesEnable);
      if (!empty($whereSqlUpdate['sql'])) {
        $this->dbWorker->updateDB('gi_new_users', $whereSqlUpdate['sql'], $setSqlUpdate['sql'], $valuesEnable);
      }
    }
  }
  public function reloadUserMultiple($idUsers)
  {
    if (!empty($idUsers)) {
      $idUsersArr = is_array($idUsers) ? $idUsers : [$idUsers];
      $idWebsockets = [];
      /** @var UserMain $user */
      foreach ($idUsersArr as $userId) {
        $user = $this->findUserById($userId);
        if (!empty($user)) {
          $idWebsocketsUser = $user->outsideWebsocketId();
          $user->updateAfterBindUser();
          $temp = $idWebsocketsUser;
          $idWebsockets = $this->simpleUtilities->combineValues($temp, $idWebsockets);
        } else {
          error_log('reloadUser empty($user)');
        }
      }
      // return $this->catcherBugs->checkReturn($idWebsockets, 'requestUpdateDealerUP', 'idWebsockets');
      return $idWebsockets;
    }
  }
  public function getMassWebSocketIdfactory($userMethod)
  {
    $idWebsockets = [];
    /** @var UserMain $user */
    foreach ($this->usersfactoryWorker as &$user) {
      if (method_exists($user, $userMethod)) {
        $idWebsocketsUser = $user->outsideWebsocketId();
        $idWebsockets[] = $idWebsocketsUser;
      }
    }
    return $idWebsockets;
  }
  public function invokeMethodByWebsocketId($userMethod, $websocketIds)
  {
    $usersInSystem = $this->simpleUtilities->mergeArrays($this->usersfactoryWorker, $this->usersContractor);
    /** @var UserMain $user */
    foreach ($usersInSystem as &$user) {
      if (!empty($user)) {
        $idWebsocketsUser = $user->outsideWebsocketId();
        $intersection = array_intersect($websocketIds, $idWebsocketsUser);
        if (!empty($intersection) && method_exists($user, $userMethod)) {
          $user->$userMethod();
        }
      }
    }
  }
  public function setAnalyticsWhose($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsWhose')) {
        $response = $user->setAnalyticsWhose($body);
        return $response;
      }
    }
  }
  public function setAnalyticsDialoguesType($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsDialoguesType')) {
        $user->setAnalyticsDialoguesType($body);
      }
    }
  }
  public function getAnalyticsUserSearch($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'getAnalyticsUserSearch')) {
        $response = $user->getAnalyticsUserSearch($body);
        return $response;
      }
    }
  }
  public function setAnalyticsUser($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsUser')) {
        $user->setAnalyticsUser($body);
      }
    }
  }
  public function setStartDateAnalytics($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setStartDateAnalytics')) {
        $user->setStartDateAnalytics($body);
      }
    }
  }
  public function setEndDateAnalytics($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setEndDateAnalytics')) {
        $user->setEndDateAnalytics($body);
      }
    }
  }
  public function setAnalyticsPeriod($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsPeriod')) {
        $user->setAnalyticsPeriod($body);
      }
    }
  }
  public function setAnalyticsTimeMode($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsTimeMode')) {
        $user->setAnalyticsTimeMode($body);
      }
    }
  }
  public function setAnalyticsSlaFirstResponse($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsSlaFirstResponse')) {
        $user->setAnalyticsSlaFirstResponse($body);
      }
    }
  }
  public function setAnalyticsSlaSubsequentResponse($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user)) {
      if (method_exists($user, 'setAnalyticsSlaSubsequentResponse')) {
        $user->setAnalyticsSlaSubsequentResponse($body);
      }
    }
  }
  public function getDealerDependentsAnalytics($idWebsocket, $body)
  {
    $user = $this->findUserByIdWebsocket($idWebsocket);
    if (!empty($user) && method_exists($user, 'getDealerDependentsAnalytics')) {
      return $user->getDealerDependentsAnalytics($body);
    }
    return ['error' => 'getDealerDependentsAnalytics unavailable'];
  }
  // private function findUsersByPoints_OLD($idPoints)
  // {
  //   $usersOnPoints = [];
  //   $idPoints = $this->simpleUtilities->toArray($idPoints);
  //   if (!empty($this->usersContractor)) {
  //     /** @var Contractor $user */
  //     foreach ($this->usersContractor as $key => &$user) {
  //       if (!empty($user)) {
  //         $pointsUser = $this->simpleUtilities->toArray($user->outsideIdPoints());
  //         $intersection = array_intersect($idPoints, $pointsUser);
  //         if (!empty($intersection)) {
  //           $usersOnPoints[$key] = &$user;
  //         }
  //       }
  //     }
  //     return $usersOnPoints;
  //   }
  // }
  // private function findUserContractor_OLD($idUser) {
  //   $whose = $this->userUtilities->checkUserAccess($idUser);
  //   if ($whose === 'contractor') {
  //     foreach ($this->usersContractor as &$contractor) {
  //       if (!empty($contractor)) {
  //         $userId = $contractor->getUserId();
  //         if ($userId === $idUser) {
  //           return $contractor;
  //         }
  //       }
  //     }
  //   }
  //   return null;
  // }
  
  // public function findUserOnline_OLD($idUser)
  // {
  //   $property = $this->setSideUser($idUser);
  //   if (!empty($this->$property)) {
  //     foreach ($this->$property as $key => $user) {
  //       if (!empty($user)) {
  //         $userId = $user->getUserId();
  //         if (intval($userId) === intval($idUser)) {
  //           // error_log("Key: $key, userId: $userId, idUser: $idUser");
  //           return true;
  //         } else {
  //           // error_log("Key: $key, userId: $userId, idUser: $idUser");
  //         }
  //       }
  //     }
  //     return false;
  //   }
  // }

    // public function findUserByIdWebsocket_OLD($idWebsocket)
  // {
  //   if (!empty($this->usersContractor)) {
  //     foreach ($this->usersContractor as $key => &$user) {
  //       if (!empty($user)) {
  //         $idWebsocketsUser = $user->outsideWebsocketId();
  //         foreach ($idWebsocketsUser as $id) {
  //           if ($id === $idWebsocket) {
  //             $user = &$this->usersContractor[$key];
  //             return $user;
  //           }
  //         }
  //       }
  //     }
  //   }
  //   if (!empty($this->usersfactoryWorker)) {
  //     foreach ($this->usersfactoryWorker as $key => &$user) {
  //       if (!empty($user)) {
  //         $idWebsocketsUser = $user->outsideWebsocketId();
  //         foreach ($idWebsocketsUser as $id) {
  //           if ($id === $idWebsocket) {
  //             $user = &$this->usersfactoryWorker[$key];
  //             return $user;
  //           }
  //         }
  //       }
  //     }
  //   }
  //   return null;
  // }

  // private function invokeMethodByWebsocketId_OLD($userMethod, $websocketIds)
  // private function updateUsersAfterBindUC($users)
  // {
  //   try {
  //     if (!empty($users)) {

  //     }
  //   } catch (Exception $e) {
  //     // Логирование ошибки
  //     error_log($e->getMessage());
  //     return ['error' => 'updateUsersAfterBind'];
  //   }
  // }

  // public function findForUpdate($users)
  // {
  //   if ($this->checkPassword()) {
  //     $websocketIds = [];
  //     foreach ($users as $key => $userId) {
  //       $userCheck = $this->findUserOnline($userId);
  //       if ($userCheck) {
  //         $user = $this->findUserById($userId);
  //         if ($key === 'replacementId') {
  //           $user->updateReplacement();
  //         }
  //         $websocketIds[] = $user->outsideWebsocketId();
  //       }
  //     }
  //     return $websocketIds;
  //   }
  // }
  // private function updateСontractsByPoint($idPoint)
  // {
  //   $usersOnPoint = $this->findUserByPoint($idPoint);
  //   $usersForUpdate = $this->usersfactoryWorker;
  //   $usersForUpdate = array_merge($usersForUpdate, $usersOnPoint);
  //   foreach ($usersForUpdate as $user) {
  //     $user->showContractsOnPage();
  //   }
  //   return $usersForUpdate;
  // }
  // private function updateContractOnPage($usersForUpdate)
  // {
  //   $preparingContracts = [];
  //   foreach ($usersForUpdate as $key => $user) {
  //     $idWebsocket = $user->outsideWebsocketId();
  //     $preparingContracts[$idWebsocket] = $user->showContractsOnPage();
  //   }
  //   return $preparingContracts;
  // }
  // public function updateContractsForUser($idPoint)
  // {
  //   if ($this->checkPassword()) {
  //     $usersForUpdate = $this->updateСontractsByPoint($idPoint);
  //     return $this->updateContractOnPage($usersForUpdate);
  //   }
  // }
  // public function recievePotencialParticipiant($idWebsocket)
  // {
  //   if ($this->checkPassword()) {
  //     // $user = $this->findUserByIdWebsocket($idWebsocket);
  //     // $potencialParticipiant = $user->getPotencialParticipiantUser();
  //     // // $preparedPotencialParticipant = $this->preparingPotencialParticipant($potencialParticipiant);

  //     // return $preparedPotencialParticipant ;
  //   }
  // }

}