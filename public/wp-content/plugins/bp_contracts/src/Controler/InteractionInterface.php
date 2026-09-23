<?php

namespace PersonalAccount\Controler;
use React\EventLoop\Loop;
use PersonalAccount\Chat;
use PersonalAccount\Core\Container;
use PersonalAccount\Controler\UserControler;
use PersonalAccount\Controler\ControlerObjectsRelationship;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\NotificationWorker;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\Utilit;

use PersonalAccount\Users\UserMain;
use PersonalAccount\Users\Distributor;

use Exception;

class InteractionInterface
{
  use Utilit;
  /** @var Chat */
  private $chat;
  /** @var Container */
  private $container;
  /** @var UserControler */
  private $userControler;
  /** @var ControlerObjectsRelationship */
  private $controlerObjectsRelationship;
  /** @var DBWorker */
  private $dbWorker;
  /** @var NotificationWorker */
  private $notificationWorker;
  /** @var SimpleUtilities */
  private $simpleUtilities;
  /** @var CatcherBugs */
  private $catcherBugs;

  /** @var UserUtilities */
  private $userUtilities;
  // private $mainObjects;
  public function __construct($container)
  {
    $this->initializationWPDB();
    $this->container = &$container;
    $this->userControler = $this->container->get('UserControler');
    $this->controlerObjectsRelationship = $this->container->get('ControlerObjectsRelationship');
    $this->dbWorker = $this->container->get('DBWorker');
    $this->notificationWorker = $this->container->get('NotificationWorker');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->chat = $this->container->get('Chat');
    // $this->bindSystem($closures);
  }
  private function packagePotencialParticipiant($users, $user, $id)
  {
    $userDetails = $users;
    $status = $this->dbWorker->selectVarSimple('Users', 'id_user', $id, 'status_activity');
    if (!empty($user)) {
      if ($status === 'active') {
        $userDetails[$id]['id'] = $user['id'];
        $userDetails[$id]['displayName'] = $user['displayName'];
        $userDetails[$id]['role'] = $user['role'];
        $userDetails[$id]['whose'] = $user['whose'];
      }
    }
    return $userDetails;
  }
  private function preparingPotencialParticipant($potentialParticipant, $userRank, $idUser)
  {
    $userDetails = [];
    // Перебираем каждого пользователя
    if (!empty($potentialParticipant)) {
      foreach ($potentialParticipant as $id => $user) {
        // Извлекаем необходимые данные
        $status = $this->dbWorker->selectSimple('Users', 'id_user', $idUser, 'status_activity');
        if (!empty($user)) {
          if ($status === 'active') {
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
      }
      return $userDetails;
    } else {
      error_log('getHaveAccessPoint $point empty');
    }
  }
  private function mergePreparingContracts($preparingContracts, $preparingContractsForParticipant)
  {
    foreach ($preparingContractsForParticipant as $key => $value) {
      // Если ключ существует в первом массиве, заменяем значение
      if (array_key_exists($key, $preparingContracts)) {
        $preparingContracts[$key] = $value;
      } else {
        // Если ключ отсутствует в первом массиве, добавляем новую пару ключ-значение
        $preparingContracts[$key] = $value;
      }
    }
    return $preparingContracts;
  }
  private function regNewDialog($idUser, $newDialog)
  {
    $newDialog = $this->userControler->regNewDialogUser($idUser, $newDialog);
    if ($newDialog) {
      $this->controlerObjectsRelationship->addDialogInContract($newDialog, $idUser);
      return $newDialog;
    } else {
      throw new Exception("regNewDialog {$newDialog} does not exist for this this User");
    }
  }
  public function regNewUser($userId, $idWebsocket)
  {
    try {
      if ($this->userControler) {
        // error_log('interactionInterface->regNewUser');
        // $this->catcherBugs->convPrintLog($userId, 'regNewUser', '$userId');
        $paramForClient = $this->userControler->addUsersContractor($userId, $idWebsocket);
        // $this->catcherBugs->convPrintLog($paramForClient, 'regNewUser', '$paramForClient');
        if ($paramForClient['whose'] === 'factory_worker' && empty($paramForClient['statusActivity'])) {
          $userWhoWasUpdate = $this->userControler->reloadFactoryWorker($idWebsocket);
          $idPoints = $this->userControler->getFromUserStuff('getAllPointsIdFW', $idWebsocket);
          if (!empty($idPoints)) {
            $this->controlerObjectsRelationship->updateUserHaveAccess($idPoints);
            $usersForUpdate = $this->userControler->getIdWebsocketUsersUC();
            $userWhoWasUpdate = $this->userControler->reloadUser($userId);
          } else {
            error_log('bindContractorsII !empty($idPoints) ');
          }
          if (!empty($usersForUpdate)) {
            $contractsSN = $this->controlerObjectsRelationship->getOpenedContractsSN($usersForUpdate);
          }
          if (!empty($userWhoWasUpdate)) {
            $this->chat->massUpdateUser($userWhoWasUpdate);
          } else {
            error_log('updateConditionUserII empty($userWhoWasBind)');
          }
          if (!empty($usersForUpdate) && !empty($contractsSN)) {
            $this->chat->massUpdatePotencialParticipiant($usersForUpdate, $contractsSN);
          }

        } else {
          return $paramForClient;
        }
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'onMessage'];
    }
  }

  //These are two generic methods that replace many of the methods below and change the workflow for handling incoming requests from users
  public function setParamUserByMyself($idWebsocket, $method, $value)
  {
    // error_log($method);
    $paramForClient = $this->userControler->getFromUserStuff($method, $idWebsocket, $value);
    // error_log(print_r($paramForClient, true));
    return $paramForClient;
  }
  public function setPropertyUserByMyself($idWebsocket, $value, $method)
  {

  }
  public function getUserNotification($idWebsocket, $idUser = null)
  {
    try {
      if (!empty($idUser)) {
        // error_log('getUserNotification');
        $notificationData = $this->notificationWorker->getUserNotification($idUser);
        // error_log('getUserNotification');
      } else {
        // error_log('getUserNotification');
        $userId = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
        $notificationData = $this->notificationWorker->getUserNotification($userId);
      }
      return $notificationData;
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'onMessage'];
    }
  }
  public function notificationAfterAddDialog($message, $idWebsocket)
  {
    $idUserWhoAdd = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    // $checkOnline = $this->userControler->findUserOnline(intval($message->idUser));
    $idWebsocketAdd = $this->userControler->getIdWebsocketByUserId(intval($message->idUser));
    $this->notificationWorker->addParticipiant($message, $idWebsocketAdd, $idUserWhoAdd);
  }
  public function getPotencialParticipiant($idWebsocket, $serialNumber)
  {
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    $userRank = $this->userControler->getFromUserStuff('outsideUserRank', $idWebsocket);
    $haveAccessPoint = $this->controlerObjectsRelationship->getHaveAccessPoint($idUser, $serialNumber);
    // $this->catcherBugs->convPrintLog($haveAccessPoint, 'getPotencialParticipiant', '$haveAccessPoint');
    $potencialParticipiant = $this->preparingPotencialParticipant($haveAccessPoint, $userRank, $idUser);
    return $potencialParticipiant;
  }
  public function deleteUser($idWebsocket)
  {
    try {
      $userId = $this->userControler->checkDisconnectedUser($idWebsocket);
      // $this->catcherBugs->convPrintLog($idWebsocket, 'deleteUser', '$idWebsocket');
      $this->controlerObjectsRelationship->updateSeeUsersCOR($idWebsocket, $userId);
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'onMessage'];
    }
  }
  public function newCurrentPage($idWebsocket, $currentPage, $where)
  {
    $this->userControler->setUserCurrentPage($idWebsocket, $currentPage, $where);
  }
  public function newPerPage($idWebsocket, $perPage, $where)
  {
    $this->userControler->setUserPerPage($idWebsocket, $perPage, $where);
  }
  public function newFilterContract($idWebsocket, $filter, $where)
  {
    $this->userControler->setUserFilterContracts($idWebsocket, $filter, $where);
  }
  public function newSearchQuery($idWebsocket, $searchQuery, $where)
  {
    $this->userControler->setUserSearchQuery( $idWebsocket, $searchQuery, $where);
  }
  public function setStartDateII($idWebsocket, $searchQuery, $where)
  {
    $this->userControler->setStartDateUC($idWebsocket, $searchQuery, $where);
  }
  public function setEndDateII($idWebsocket, $searchQuery, $where)
  {
    $this->userControler->setEndDateUC($idWebsocket, $searchQuery, $where);
  }

  public function showContracts($idWebsocket)
  {
    // $this->catcherBugs->convPrintLog($idWebsocket, 'showContracts', '$idWebsocket');
    $contractsOnPage = $this->userControler->getFromUserStuff('showContractsOnPage', $idWebsocket);
    // $this->catcherBugs->convPrintLog($contractsOnPage, 'showContracts', '$contractsOnPage');
    if ($contractsOnPage) {
      return $contractsOnPage;
    } else {
      throw new Exception("contractsOnPage {$contractsOnPage} does not exist for this this User");
    }
  }
  public function regNewContract($idWebsocket, $nameContract, $idPoint)
  {
    /** @var UserMain $user  */
    $user = $this->userControler->findUserByIdWebsocket($idWebsocket);
    $idPoint = null;
    if ($user) {
      $idUser = $user->getUserId();
      $userRole = $this->userUtilities->getUserRole($idUser);
      if ($userRole === 'distributor') {
        /** @var Distributor $user  */
        $idPoint = $user->getMyPointOutside();
      }
    } else {
      error_log('regNewContract $user empty');
    }
    // $this->catcherBugs->convPrintLog($idPoint, 'regNewContract', '$idPoint');
    $serialNumber = $this->userControler->logicCreateContract($idUser, $nameContract);
    if ($serialNumber) {
      // $idPoint = $this->controlerObjectsRelationship->updateContractOnPoint($serialNumber, $idUser, $idPoint);
      $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoint);
      $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $serialNumber);
      return $usersForUpdate;
    } else {
      throw new Exception("User {$idUser} does not exist among contractors");
    }
  }
  public function deleteContract($idWebsocket, $serialNumber)
  {
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    $idPoint = $this->controlerObjectsRelationship->deleteContract($serialNumber, $idUser);
    $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoint);
    $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $serialNumber);
    return $usersForUpdate;
  }
  public function showContract($idWebsocket, $serialNumber = null)
  {
    // $this->catcherBugs->convPrintLog($serialNumber, 'showContract', '$serialNumber');
    if (!empty($serialNumber)) {
      /** @var UserMain $user */
      $user = $this->userControler->findUserByIdWebsocket($idWebsocket);
      // $this->catcherBugs->convPrintLog($serialNumber, 'showContract', '$serialNumber');
      if ($user) {
        $idUser = $user->getUserId();
        // $this->catcherBugs->convPrintLog($serialNumber, 'showContract', '$serialNumber');
        // $contractData = $this->controlerObjectsRelationship->getContractData($idUser, $serialNumber, $idWebsocket);
        $contractData = $this->controlerObjectsRelationship->getContractData_NEW($idUser, $serialNumber, $idWebsocket);
        // $this->catcherBugs->convPrintLog($contractData, 'showContract', '$contractData');
        return $contractData;
      } else {
        error_log('showContract $user empty');
      }
    }
  }
  public function refreshOpenContract($idWebsocket, $serialNumber = null)
  {
    if (!empty($serialNumber)) {
      /** @var UserMain $user */
      $user = $this->userControler->findUserByIdWebsocket($idWebsocket);
      if ($user) {
        $idUser = $user->getUserId();
        $contractData = $this->controlerObjectsRelationship->getOpenContractData($idUser, $serialNumber, $idWebsocket);
        return $contractData;
      }
    }
  }
  // public function closeContract($idWebsocket, $serialNumber)
  // {
  //   $user = $this->userControler->findUserByIdWebsocket($idWebsocket);
  //   if ($user) {
  //     $idUser = $user->getUserId();
  //     $answer = $this->controlerObjectsRelationship->shutDownContract($idUser, $serialNumber);
  //     return $answer;
  //   } else {
  //     error_log('closeContract $user empty');
  //   }
  // }
  public function closeObjectRelationshipII($idWebsocket, $serialNumber, $where)
  {

    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    switch ($where) {
      case 'contract':
        $this->controlerObjectsRelationship->shutDownContractCOR($idUser, $serialNumber, $idWebsocket);
    }
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
  }
  // This is the format in which the server receives messages from the client.
  // $firstMessage = [
  //   'serialNumber' => '',
  //   'messageBody' => '',
  //   'idDialog' => '',
  //   'typeDialog' = '',
  //   'idMessage' => '',
  //   'files' => '',
  //   'idAuthor' => '',
  // ];
  // $message = [
  //   'serialNumber' => '',
  //   'messageBody' => '',
  //   'idDialog' => '',
  //   'idMessage' => '',
  //   'typeDialog' = '',
  //   'files' => '',
  //   'idAuthor' => '',
  // ];
  public function regNewMessage($idWebsocket, $newMessage)
  {
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    $newMessage->idAuthor = $idUser;
    //Если это первое сообщение в новом диалоге мы проверяем есть ли в нём номер диалога, если нет то отправляем сразу создавать новый диалог.
    if (empty($newMessage->idDialog)) {
      $newMessage = $this->regNewDialog($idUser, $newMessage);
    }
    if ($newMessage) {
      // $this->catcherBugs->convPrintLog($newMessage, 'regNewMessage', '$newMessage', 3);
      // $this->catcherBugs->catchBug('NewMessage', 'newMessage->files', $newMessage->files, $newMessage->filesExist);
      $newMessage = $this->userControler->regNewMessage($newMessage, $idUser);
      $this->controlerObjectsRelationship->addMessageInContract($newMessage, $idUser);
      // error_log('Next notificationAfterNewMessage');
      Loop::futureTick(function () use ($newMessage) {
        $this->notificationWorker->prepareDataToSend($newMessage);
        // error_log('Here notificationAfterNewMessage');
      });

      $idPoint = $this->controlerObjectsRelationship->findPointByContract($newMessage->serialNumber, $idUser);
      $this->updateWhoSeeOR($newMessage->serialNumber);
      if ($idPoint) {
        $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoint);
        $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $newMessage->serialNumber);
        return $usersForUpdate;
      } else {
        // error_log('regNewMessage $idPoint empty');
      }
    }
  }
  public function getReadedMessage($idWebsocket, $message)
  {
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    $message->whoRead = $idUser;
    $idPoint = $this->controlerObjectsRelationship->findPointByContract($message->serialNumber, $idUser);
    if ($idPoint) {
      $this->controlerObjectsRelationship->setReadedMessage($message);

      $this->updateWhoSeeOR($message->serialNumber);
    } else {
      error_log('getReadedMessage $idPoint empty');
    }
  }
  public function getAddParticipiant($message, $idWebsocket)
  {
    $idWebsocketUserAdd = $this->userControler->getIdWebsocketByUserId($message->idUser);
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    $message->whoAdded = $idUser;
    $idPoint = $this->controlerObjectsRelationship->findPointByContract($message->serialNumber, $idUser); // Может пригодится
    if ($idPoint) {
      $this->controlerObjectsRelationship->getAddParticipiant($message);
      $usersForUpdate = $this->controlerObjectsRelationship->getWhoLookContract($message->serialNumber);
      // $this->catcherBugs->convPrintLog($idWebsocketUserAdd, 'getAddParticipiant', '$idWebsocketUserAdd');
      // $this->catcherBugs->convPrintLog($usersForUpdate, 'getAddParticipiant', '$usersForUpdate');
      $usersForUpdate = $this->simpleUtilities->mergeArrays($idWebsocketUserAdd, $usersForUpdate);
      $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $message->serialNumber);
      return $usersForUpdate;
    } else {
      error_log('getAddParticipiant $idPoint empty');
    }
  }
  public function getQuitDialog($message, $idWebsocket)
  {
    $idPoint = $this->controlerObjectsRelationship->findPointByContract($message->serialNumber, $message->idUser); // Может пригодится
    if ($idPoint) {
      $this->controlerObjectsRelationship->userQuitDialog($message);
      $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoint);
      $usersForUpdate[] = $idWebsocket;
      $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $message->serialNumber);
      return $usersForUpdate;
    } else {
      error_log('getQuitDialog $idPoint empty');
    }
  }
  public function setReplacement($message, $idWebsocket)
  {
    $idDialogues = $this->userControler->setReplacementForUserUC($message->userId, $message->hisReplacementId, $idWebsocket);
    $userRole = $this->userUtilities->getUserRole($message->userId);
    if ($userRole === 'manager') {
      // $this->catcherBugs->convPrintLog($message, 'setReplacement', '$message');
      $idPoints = $this->userControler->changePoints($idWebsocket, $message->userId, $message->hisReplacementId);
      // $this->catcherBugs->convPrintLog($idPoints, 'setReplacement', '$idPoints');
    }
    if (!empty($idDialogues)) {
      $serialNumbers = $this->controlerObjectsRelationship->updateDialogAfterReplacement($idDialogues, $message);
    } else {
      // error_log('setReplacement $idDialogues empty');
    }
    if (!empty($serialNumbers)) {
      return $serialNumbers;
    } else {
      // error_log('setReplacement $serialNumbers empty');
    }
  }
  public function getUsersOnline($serialNumbers, $message)
  {
    $usersForUpdate = [];
    $serialNumbers = $this->simpleUtilities->toArray($serialNumbers);
    foreach ($serialNumbers as $sn) {

      $idPoint = $this->controlerObjectsRelationship->findPointByContract($sn, $message->hisReplacementId); // Может пригодится
      if ($idPoint) {
        if (!is_object($idPoint)) {
          $usersForPoint = $this->userControler->getIdWebsocketUsersUC($idPoint);
          if (!empty($usersForPoint)) {
            // Объединяем массивы и удаляем повторы
            $usersForUpdate = array_unique(array_merge($usersForUpdate, $usersForPoint));
          }
        } else {
          // error_log('getUsersOnline $idPoint OBJECT');
        }

      } else {
        // error_log('getUsersOnline $idPoint empty');
      }
    }
    return $usersForUpdate;
  }

  public function getParamUser($idWebsocket)
  {
    $paramForUser = $this->userControler->getFromUserStuff('outsideParamForClient', $idWebsocket);
    return $paramForUser;
  }
  public function changeNameContractII($idWebsocketWhoChange, $contract)
  {
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocketWhoChange);
    $idPoint = $this->controlerObjectsRelationship->changeNameContractCOR($contract, $idUser);
    $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoint);
    $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $contract->serialNumber);
    return $usersForUpdate;
  }
  public function showOrdersII($idWebsocket)
  {
    $ordersOnPage = $this->userControler->getFromUserStuff('showOrdersUser', $idWebsocket);
    return $ordersOnPage;
  }
  public function showInvoices($idWebsocket)
  {
    $invoicesOnPage = $this->userControler->getFromUserStuff('showInvoices', $idWebsocket);

    return $invoicesOnPage;
  }
  public function packageInfoOrdersII($idWebsocket, $orders)
  {
    $packageOrders = $this->userControler->getFromUserStuff('packageInfoOrdersWWO', $idWebsocket, $orders);
    return $packageOrders;
  }
  public function printReportOrdersII($idWebsocket, $orders)
  {
    $printOrders = $this->userControler->getFromUserStuff('printReportOrdersWWO', $idWebsocket, $orders);
    return $printOrders;
  }
  public function savePointsII($data, $idWebsocket)
  {
    $usersForUpdate = $this->userControler->savePointsUC($data, $idWebsocket);
    $this->userControler->updateManagerByPointIdUC($data->idPoints);
    return $usersForUpdate;
  }
  public function getUpdateUserParamII($idWebsocket)
  {
    $paramData = $this->userControler->getUpdateUserParamUC( $idWebsocket);
    return $paramData;
  }
  public function requestUpdateOrders($logId)
  {

    $orders = $this->controlerObjectsRelationship->requestUpdateOrdersCOR($logId);
    $contractsSN = [];
    if (!empty($orders)) {
      $pointsId = $this->controlerObjectsRelationship->getIdPointsForUpdateCOR($orders);
    }
    if (!empty($orders)) {
      $contractsSN = $this->controlerObjectsRelationship->updateOrdersPointsCOR($orders);
    }
    if (!empty($contractsSN)) {
      $this->updateWhoSeeOR($contractsSN);
    }
    // if (!empty($contractsSN)) {
    //   $usersSeeContracts = $this->controlerObjectsRelationship->userSeeContractsMassUpdateCOR($contractsSN);
    // }
    // // if (!empty($pointsId)) {
    // //   $usersForUpdate = $this->userControler->getIdWebsocketUsersUC( $pointsId);
    // //   // return $usersForUpdate;
    // // }
    // if (!empty($usersSeeContracts) && !empty($contractsSN)) {
    //   $this->chat->updateWhoSeeOR($usersSeeContracts, $contractsSN);
    // }
  }

  public function requestUpdatePointUP($logId)
  {
    $idPoints = $this->controlerObjectsRelationship->requestUpdatePointUP( $logId);
    if (!empty($idPoints)) {
      $idWebsocketsAllSystem = $this->userControler->getIdWebsocketUsersUC(null, true);
    }
    if (!empty($idWebsocketsAllSystem)) {
      $this->userControler->invokeMethodByWebsocketId('updatePoints', $idWebsocketsAllSystem);
      $idWebsockets = $this->userControler->getIdWebsocketUsersUC($idPoints);
    }
    // return $this->catcherBugs->checkReturn($idWebsockets, 'requestUpdatePointUP', 'idWebsockets');
    return $idWebsockets;
  }
  public function requestUpdateDealerUP($logId)
  {
    $idDealersUpdate = $this->userControler->requestUpdateDealerUP( $logId);
    // $this->catcherBugs->convPrintLog($idDealersUpdate, 'requestUpdateDealerUP', '$logId');
    $idWebsocketsUpdate = $this->userControler->reloadUserMultiple($idDealersUpdate);
    // return $this->catcherBugs->checkReturn($idWebsocketsUpdate, 'requestUpdateDealerUP', 'idWebsocketsUpdate');
    return $idWebsocketsUpdate;
  }
  public function requestUpdateFirmUP($logId)
  {
    $idFirmsUpdate = $this->userControler->requestUpdateFirmUP($logId);
    // $this->catcherBugs->convPrintLog($idFirmsUpdate, 'requestUpdateFirmsUP', '$logId');
    // $idWebsocketsUpdate = $this->userControler->reloadUserMultiple($idFirmsUpdate);
    // return $idWebsocketsUpdate;
  }
  public function requestUpdateInvoiceUP($logId)
  {
    $idPoints = $this->controlerObjectsRelationship->requestUpdateInvoiceUP($logId);
    // $this->catcherBugs->convPrintLog($idInvoicesUpdate, 'requestUpdateInvoicesUP', '$logId');

    $idWebsocketsUpdate = $this->userControler->getIdWebsocketUsersUC($idPoints);
    return $idWebsocketsUpdate;
  }
  public function getMassWebSocketIdFactory($hasMethod)
  {
    $idWebsockets = $this->userControler->getMassWebSocketIdFactory($hasMethod);
    // return $this->catcherBugs->checkReturn($idWebsockets, 'requestUpdateDealerUP', 'idWebsockets');
    return $idWebsockets;
  }
  public function showContractorsII($idWebsocket)
  {
    $dataContractors = $this->userControler->getFromUserStuff('showContractorsMC', $idWebsocket);
    return $dataContractors;
  }
  public function searchForBindContractorsII($idWebsocket, $data)
  {
    $paramData = $this->userControler->getFromUserStuff('searchForBindContractorsMC', $idWebsocket, $data);
    return $paramData;
  }
  //Rebuild this chain of Function
  public function setStatusUserII($message, $idWebsocketWhoChange)
  {
    $this->userControler->setStatusUserUC($message->userId, $message->statusActivity, $idWebsocketWhoChange);
    $idPoint = $this->userUtilities->getAllPointsUser($message->userId);

    $this->controlerObjectsRelationship->updateUserHaveAccess($idPoint);
    $this->userControler->updateForOtherUsers($message->userId, $idPoint);
    //Отправляем для обновления.
    $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoint);
    return $usersForUpdate;
  }
  public function updateConditionUserII($method, $idWebsocket, $data)
  {
    $userId = isset($data->userId) ? $data->userId : null;
    if (isset($userId)) {
      $idPoints = $this->userControler->getFromUserStuff($method, $idWebsocket, $data);
      if (!empty($idPoints)) {
        $this->controlerObjectsRelationship->updateUserHaveAccess($idPoints);
        $this->userControler->updateOnPointAfterBind($idPoints);
        $usersForUpdate = $this->userControler->getIdWebsocketUsersUC($idPoints);
        $userWhoWasUpdate = $this->userControler->reloadUser($userId);
      } else {
        error_log('bindContractorsII !empty($idPoints) ');
      }
      if (!empty($usersForUpdate)) {
        $contractsSN = $this->controlerObjectsRelationship->getOpenedContractsSN($usersForUpdate);
      }
      if (!empty($userWhoWasUpdate)) {
        $this->chat->massUpdateUser($userWhoWasUpdate);
      } else {
        error_log('updateConditionUserII empty($userWhoWasBind)');
      }
      if (!empty($usersForUpdate) && !empty($contractsSN)) {
        $usersForUpdate = $this->userControler->sortUsersWhoSeeContract($usersForUpdate, $contractsSN);
        $this->chat->massUpdatePotencialParticipiant($usersForUpdate, $contractsSN);
      }

      return !empty($usersForUpdate) ? $usersForUpdate : null;
    } else {
      error_log('!isset($data->userId)');
    }
  }

  private function updateWhoSeeOR($contractsSN)
  {
    $contractsSN = $this->simpleUtilities->toArray($contractsSN);

    if (!empty($contractsSN)) {
      foreach ($contractsSN as $sn) {
        $usersSeeContracts = $this->controlerObjectsRelationship->userSeeContractsMassUpdateCOR( $sn);
        if (!empty($usersSeeContracts)) {
          // $this->catcherBugs->convPrintLog($usersSeeContracts, 'updateWhoSeeOR', '$usersSeeContracts');
          $this->chat->updateWhoSeeOR($usersSeeContracts, $sn);
        }
      }
    }
  }
  public function printInvoice($idWebsocket, $idInvoice)
  {
    $idUser = $this->userControler->getUserIdByIdWebsocket($idWebsocket);
    // $this->catcherBugs->convPrintLog($idInvoice, 'printInvoice', '$idInvoice');
    // $this->catcherBugs->convPrintLog($idUser, 'printInvoice', '$idUser');
    $data = $this->userControler->getFromUserStuff('printInvoice', $idWebsocket, $idInvoice);
    // $this->catcherBugs->convPrintLog($data, 'printInvoice', '$orders');
    return $data;
    // $this->chat->sendMessageToUser($idWebsocket, $orders);
  }
  public function setConditionForSearch($idWebsocket, $body, $where)
  {
    $this->userControler->setConditionForSearch($idWebsocket, $body, $where);
  }
  public function showAnalytics($idWebsocket, $data)
  {
    $analytics = $this->userControler->getFromUserStuff('getAnalytics', $idWebsocket, $data);
    return $analytics;
  }
  public function getDealerDependentsAnalytics($idWebsocket, $data)
  {
    return $this->userControler->getFromUserStuff('getDealerDependentsAnalytics', $idWebsocket, $data);
  }
  public function setAnalyticsWhose($idWebsocket, $body)
  {
    $response = $this->userControler->setAnalyticsWhose($idWebsocket, $body);
    return $response;
  }
  public function setAnalyticsDialoguesType($idWebsocket, $body)
  {
    $this->userControler->setAnalyticsDialoguesType($idWebsocket, $body);
  }
  public function getAnalyticsUserSearch($idWebsocket, $body)
  {
    $response = $this->userControler->getAnalyticsUserSearch($idWebsocket, $body);
    return $response;
  }
  public function setAnalyticsUser($idWebsocket, $body)
  {
    $this->userControler->setAnalyticsUser($idWebsocket, $body);
  }
  public function setStartDateAnalytics($idWebsocket, $body)
  {
    $this->userControler->setStartDateAnalytics($idWebsocket, $body);
  }
  public function setEndDateAnalytics($idWebsocket, $body)
  {
    $this->userControler->setEndDateAnalytics($idWebsocket, $body);
  }
  public function setAnalyticsPeriod($idWebsocket, $body)
  {
    $this->userControler->setAnalyticsPeriod($idWebsocket, $body);
  }
  public function setAnalyticsTimeMode($idWebsocket, $body)
  {
    $this->userControler->setAnalyticsTimeMode($idWebsocket, $body);
  }
  public function setAnalyticsSlaFirstResponse($idWebsocket, $body)
  {
    $this->userControler->setAnalyticsSlaFirstResponse($idWebsocket, $body);
  }
  public function setAnalyticsSlaSubsequentResponse($idWebsocket, $body)
  {
    $this->userControler->setAnalyticsSlaSubsequentResponse($idWebsocket, $body);
  }
  // public function updateOrderII($idOrder)
  // {
  //   $idPoint = $this->controlerObjectsRelationship->updateOrderCOR( $idOrder);
  //   error_log(print_r($idPoint, true));
  //   $usersForUpdate = $this->userControler->getIdWebsocketUsersUC( $idPoint);
  //   return $usersForUpdate;
  // }

  // public function addNewParcipiantDialog($idUser, $idParcipiant, $objectDialog)
  // {
  // }
  // public function showPotencialParticipiant($idUser)
  // {
  // }

  // public function showShipments($idUser, $snShipments)
  // {
  // }
  // public function showInvoices($idUser, $snInvoices)
  // {
  // }
  // public function showContractsOnPage($idUser, $perPage, $currentPage)
  // {
  // }
  // public function showShipmentsOnPage($idUser, $perPage, $currentPage)
  // {

  // }
  // public function showInvoicesOnPage($idUser, $perPage, $currentPage)
  // {

  // }
  // public function showOrdersOnPage($idUser, $perPage, $currentPage)
  // {
  // }
  public function searchAndFilterContract($idUser, $perPage, $currentPage, $paramQuery)
  {

  }
  public function searchAndFilterOrder($idUser, $perPage, $currentPage, $paramQuery)
  {

  }
}
