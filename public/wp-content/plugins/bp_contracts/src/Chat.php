<?php

namespace PersonalAccount;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use PersonalAccount\Core\SystemConstructor;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Controler\InteractionInterface;
use PersonalAccount\Utilities\SimpleUtilities;

class Chat implements MessageComponentInterface
{
  /** @var SystemConstructor */
  protected $systemConstructor;
  private $clients;
  /** @var InteractionInterface */
  private $interactionInterface;
  /** @var Container */
  private $container;
  /** @var CatcherBugs */
  protected $catcherBugs;
  /** @var SimpleUtilities */
  protected $simpleUtilities;
  public function __construct()
  {
    $this->systemConstructor = new SystemConstructor($this);
    $this->container = $this->systemConstructor->getContainer();
    $this->interactionInterface = $this->container->get('InteractionInterface');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
  }
  private function findCurrentConnection($from)
  {
    $id = 0;
    if ($this->clients) {
      foreach ($this->clients as $idWebsocket => $oldFrom) {
        if (in_array($from, $oldFrom, true)) {
          $id = $idWebsocket;
          break; // Найдено соответствие, можно выйти из цикла
        }
      }
    }
    return $id;
  }

  private function regUser($from, $body)
  {

    $idUser = $body;
    $idWebsocket = uniqid();
    $this->clients[$idWebsocket][] = $from;
    // $this->catcherBugs->convPrintLog($idUser, 'Chat', '$idUser');
    $paramForClient = $this->interactionInterface->regNewUser($idUser, $idWebsocket);
    $paramForClient = json_encode($paramForClient);
    // $this->catcherBugs->convPrintLog($paramForClient, 'Chat', '$paramForClient');
    $from->send('idUser::: ' . $idUser);
    $from->send('idWebsocket::: ' . $idWebsocket);
    $from->send('paramForClient::: ' . $paramForClient);

    $notificationParam = $this->interactionInterface->getUserNotification($idWebsocket, $idUser);
    // $this->catcherBugs->convPrintLog($notificationParam, 'Chat', '$notificationParam');
    $notificationParam = json_encode($notificationParam);
    $from->send('Notification::: ' . $notificationParam);
  }

  //These are two generic methods that replace many of the methods below and change the workflow for handling incoming requests from users

  private function setParamUserByMyself($from, $method, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $value = json_decode($body);
    // error_log($method);
    // error_log($value);

    $paramForClient = $this->interactionInterface->setParamUserByMyself($idWebsocket, $method, $value);
    $data = json_encode($paramForClient);

    $from->send('paramForClient::: ' . $data);
  }

  // private function setPropertyUserByMyself($from, $body, $method) {
  //   $idWebsocket = $this->findCurrentConnection($from);
  //   $value = json_decode($body);
  //   $data = $this->interactionInterface->setPropertyUserByMyself($idWebsocket, $value, $method);
  //   $from->send($method . '::: ' . $data);
  // }

  private function takePotencialParticipiant($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $potencialParticipiant = $this->interactionInterface->getPotencialParticipiant($idWebsocket, $body);
    // $this->catcherBugs->convPrintLog($potencialParticipiant, 'takePotencialParticipiant', '$potencialParticipiant');
    $potencialParticipiant = json_encode($potencialParticipiant);
    $from->send('potencialParticipiant::: ' . $potencialParticipiant);
  }
  private function changeCurrentPage($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $this->interactionInterface->newCurrentPage($idWebsocket, $body, $where);
  }
  private function changePerPage($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $this->interactionInterface->newPerPage($idWebsocket, $body, $where);
  }
  private function closeObjectRelationship($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $this->interactionInterface->closeObjectRelationshipII($idWebsocket, $message, $where);
  }
  private function getSerchQuery($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $this->interactionInterface->newSearchQuery($idWebsocket, $body, $where);
    return $idWebsocket;
  }
  private function getFilterContract($from, $body, $where)
  {

    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $this->interactionInterface->newFilterContract($idWebsocket, $message, $where);
  }
  private function setStartDate($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $this->interactionInterface->setStartDateII($idWebsocket, $message, $where);
  }
  private function setEndDate($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $this->interactionInterface->setEndDateII($idWebsocket, $message, $where);
  }
  private function changeContractOnPage($from)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $contractsData = $this->interactionInterface->showContracts($idWebsocket);

    $message = json_encode($contractsData);

    // error_log($message);
    // error_log('changeContractOnPage');
    $from->send('ContractOnPage::: ' . $message);
  }
  private function sendMassChanges($usersForUpdate, $methodToGetData, $messagePrefix, $serialNumber = null)
  {
    if ($usersForUpdate) {
      // error_log(print_r($messagePrefix, true));
      // error_log(print_r($usersForUpdate, true));
      // $usersForUpdate = $this->simpleUtilities->toArray($usersForUpdate);
      // $this->catcherBugs->convPrintLog($serialNumber, 'sendMassChanges', '$serialNumber');
      // $this->catcherBugs->convPrintLog($methodToGetData, 'sendMassChanges', '$methodToGetData');
      // $this->catcherBugs->convPrintLog($usersForUpdate, 'sendMassChanges', '$usersForUpdate');
      // $this->catcherBugs->convPrintLog($messagePrefix, 'sendMassChanges', '$messagePrefix');
      $i = 1;
      foreach ($usersForUpdate as $idWebsocket) {

        if (!is_array($idWebsocket) && isset($this->clients[$idWebsocket])) {
          // Вызов переданного метода для получения данных
          $data = empty($serialNumber) ?
            $this->interactionInterface->$methodToGetData($idWebsocket) :
            $this->interactionInterface->$methodToGetData($idWebsocket, $serialNumber);
          $message = json_encode($data);
          // Отправка сообщения всем клиентам
          foreach ($this->clients[$idWebsocket] as $client) {
            $client->send($messagePrefix . '::: ' . $message);
          }
        } else {
          // $this->catcherBugs->convPrintLog($i, 'sendMassChanges', '$i');
          // $this->catcherBugs->convPrintLog($idWebsocket, 'sendMassChanges', '$idWebsocket');
          // $this->catcherBugs->convPrintLog($usersForUpdate, 'sendMassChanges', '$idWebsocket');
          // $this->catcherBugs->convPrintLog($messagePrefix, 'sendMassChanges', '$messagePrefix');
          // $this->catcherBugs->convPrintLog($methodToGetData, 'sendMassChanges', '$methodToGetData');
          // $this->catcherBugs->convPrintLog($serialNumber, 'sendMassChanges', '$serialNumber');
          $this->interactionInterface->deleteUser($idWebsocket);
        }
      }
    }
  }

  private function newContractFromUser($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $usersForUpdate = $this->interactionInterface->regNewContract($idWebsocket, $message->new_contract, $message->point_id);
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
  }
  private function deleteEmptyContract($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);

    $contractsForDelete = json_decode($body);
    foreach ($contractsForDelete as $serialNumber) {
      $usersForUpdate = $this->interactionInterface->deleteContract($idWebsocket, intval($serialNumber));
      $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    }
  }
  private function regNewMessage($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);

    // Sanitize message body while preserving allowed HTML tags
    $allowedTags = '<p><br><strong><em><u><s><ul><ol><li><blockquote><code><pre>';
    $message->messageBody = strip_tags($message->messageBody, $allowedTags);
    $message->messageBody = htmlspecialchars($message->messageBody, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

    $usersForUpdate = $this->interactionInterface->regNewMessage($idWebsocket, $message);
    $notificationParam = $this->interactionInterface->getUserNotification($idWebsocket);
    $notificationParam = json_encode($notificationParam);
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    // $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', $message->serialNumber);
    $from->send('Notification::: ' . $notificationParam);
  }
  private function userOpenContract($from, $serialNumber)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $contractData = $this->interactionInterface->showContract($idWebsocket, $serialNumber);
    $message = json_encode($contractData);
    $from->send('sendDataForContract::: ' . $message);
  }
  private function readedMessage($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $this->interactionInterface->getReadedMessage($idWebsocket, $message);
    $this->changeContractOnPage($from);
    // $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', $message->serialNumber);
    $notificationParam = $this->interactionInterface->getUserNotification($idWebsocket);
    $notificationParam = json_encode($notificationParam);
    $from->send('Notification::: ' . $notificationParam);
  }
  private function addParticipiant($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $usersForUpdate = $this->interactionInterface->getAddParticipiant($message, $idWebsocket);

    $this->interactionInterface->notificationAfterAddDialog($message, $idWebsocket);
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', $message->serialNumber);
  }
  private function addMeParticipiant($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $usersForUpdate = $this->interactionInterface->getAddParticipiant($message, $idWebsocket);
    // error_log('addMeParticipiant');
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', $message->serialNumber);
    $notificationParam = $this->interactionInterface->getUserNotification($idWebsocket);
    $notificationParam = json_encode($notificationParam);
    $from->send('Notification::: ' . $notificationParam);
  }
  private function quitDialog($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $usersForUpdate = $this->interactionInterface->getQuitDialog($message, $idWebsocket);
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', $message->serialNumber);
    $notificationParam = $this->interactionInterface->getUserNotification($idWebsocket);
    $notificationParam = json_encode($notificationParam);
    $from->send('Notification::: ' . $notificationParam);
  }
  private function setReplacementForUser($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $serialNumbers = $this->interactionInterface->setReplacement($message, $idWebsocket);
    $usersForUpdate = $this->interactionInterface->getUsersOnline($serialNumbers, $message);
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($usersForUpdate, 'getUserNotification', 'Notification');
    if (!empty($serialNumbers)) {
      foreach ($serialNumbers as $sn) {
        $this->sendMassChanges($usersForUpdate, 'refreshOpenContract', 'sendDataForContract', $sn);
      }
    }
  }
  private function setStatusUser($from, $body)
  {
    $idWebsocketWhoChange = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $usersForUpdate = $this->interactionInterface->setStatusUserII($message, $idWebsocketWhoChange);

    $this->sendMassChanges($usersForUpdate, 'getParamUser', 'paramForClient');
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($usersForUpdate, 'getUserNotification', 'Notification');
  }
  private function changeNameContract($from, $body)
  {
    $idWebsocketWhoChange = $this->findCurrentConnection($from);
    $message = json_decode($body);
    $usersForUpdate = $this->interactionInterface->changeNameContractII($idWebsocketWhoChange, $message);
    $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', $message->serialNumber);
  }
  private function showOrders($from)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $dataOrders = $this->interactionInterface->showOrdersII($idWebsocket);
    $message = json_encode($dataOrders);
    $from->send('OrdersOnPage::: ' . $message);
  }
  private function showContractors($from)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $dataContractors = $this->interactionInterface->showContractorsII($idWebsocket);
    $message = json_encode($dataContractors);
    $from->send('ContractorsOnPage::: ' . $message);
  }
  private function showInvoices($from)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $dataInvoices = $this->interactionInterface->showInvoices($idWebsocket);
    // $this->catcherBugs->convPrintLog($dataInvoices, 'showInvoices', '$message');
    $message = json_encode($dataInvoices);
    // $this->catcherBugs->convPrintLog($message, 'showInvoices', '$message');
    $from->send('InvoicesOnPage::: ' . $message);
  }
  private function packageInfoOrders($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $orders = json_decode($body);
    $dataOrders = $this->interactionInterface->packageInfoOrdersII($idWebsocket, $orders);
    $message = json_encode($dataOrders);
    $from->send('packageInfoOrdersXLSX::: ' . $message);
  }
  private function printReportOrders($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $orders = json_decode($body);
    $dataOrders = $this->interactionInterface->printReportOrdersII($idWebsocket, $orders);
    $message = json_encode($dataOrders);
    $from->send('printReportOrdersXLSX::: ' . $message);
  }
  private function savePoints($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $data = json_decode($body);
    $usersForUpdate = $this->interactionInterface->savePointsII($data, $idWebsocket);
    $this->sendMassChanges($usersForUpdate, 'getUpdateUserParamII', 'paramForClient');
  }
  private function requestUpdateOrders($body)
  {
    $logId = intval(json_decode($body));
    $this->interactionInterface->requestUpdateOrders($logId);
    if (!empty($usersForUpdate)) {
      $this->sendMassChanges($usersForUpdate, 'showOrdersII', 'OrdersOnPage');
    }
  }
  private function requestUpdatePointUP($body)
  {
    $logId = intval(json_decode($body));
    $usersForUpdate = $this->interactionInterface->requestUpdatePointUP($logId);

    if (!empty($usersForUpdate)) {
      $this->sendMassChanges($usersForUpdate, 'showOrdersII', 'OrdersOnPage');
      $this->sendMassChanges($usersForUpdate, 'showContracts', 'ContractOnPage');
      $this->sendMassChanges($usersForUpdate, 'getUpdateUserParamII', 'paramForClient');
    }
  }
  private function requestUpdateDealerUP($body)
  {
    $logId = intval(json_decode($body));
    // $this->catcherBugs->convPrintLog($logId, 'requestUpdateDealerUP', '$logId');
    $dealersForUpdate = $this->interactionInterface->requestUpdateDealerUP($logId);
    $this->sendMassChanges($dealersForUpdate, 'getParamUser', 'paramForClient');
    $updateFactoryUsers = $this->interactionInterface->getMassWebSocketIdFactory('showContractorsMC');
    if ($updateFactoryUsers) {
      $this->sendMassChanges($updateFactoryUsers, 'showContractorsMC', 'ContractorsOnPage');
    }
  }

  private function requestUpdateFirmUP($body)
  {
    $logId = intval(json_decode($body));
    $this->interactionInterface->requestUpdateFirmUP($logId);
    // $this->sendMassChanges($firmsForUpdate, 'getParamUser', 'paramForClient');
  }

  private function requestUpdateInvoiceUP($body)
  {
    $logId = intval(json_decode($body));
    $usersForUpdate = $this->interactionInterface->requestUpdateInvoiceUP($logId);
    $this->sendMassChanges($usersForUpdate, 'showInvoices', 'invoicesOnPage');
  }

  private function searchForBindContractors($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $data = json_decode($body);
    $paramData = $this->interactionInterface->searchForBindContractorsII($idWebsocket, $data);
    if (!$paramData) {
      $paramData = [];
    }
    $paramData['idUser'] = $data->idUser;
    $message = json_encode($paramData);
    $from->send('showForBindContractors::: ' . $message);
  }
  //Rebuild this chain of Function

  private function updateConditionUser($from, $body, $method)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $data = json_decode($body);
    $usersForUpdate = $this->interactionInterface->updateConditionUserII($method, $idWebsocket, $data);
    // $this->sendMassChanges($usersForUpdate, 'getParamUser', 'paramForClient');
  }
  public function updateWhoSeeOR($usersForUpdate, $contractsSN)
  {
    $contractsSN = $this->simpleUtilities->toArray($contractsSN);
    foreach ($contractsSN as $serialNuumber) {
      $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', intval($serialNuumber));
    }
  }
  public function massUpdatePotencialParticipiant($usersForUpdate, $contractsSN)
  {
    foreach ($contractsSN as $serialNuumber) {
      $this->sendMassChanges($usersForUpdate, 'getPotencialParticipiant', 'potencialParticipiant', intval($serialNuumber));
      $this->sendMassChanges($usersForUpdate, 'showContract', 'sendDataForContract', intval($serialNuumber));
    }
  }
  public function massUpdateUser($user)
  {
    $user = is_array($user) ? $user : [$user];
    $this->sendMassChanges($user, 'showContracts', 'ContractOnPage');
    $this->sendMassChanges($user, 'showOrdersII', 'OrdersOnPage');
    $this->sendMassChanges($user, 'getParamUser', 'paramForClient');
  }
  public function sendExternalMessage($websocketId, $messagePrefix, $data)
  {
    $usersForUpdate = is_array($websocketId) ? $websocketId : [$websocketId];
    // error_log('Here sendExternalMessage');
    // error_log(print_r($websocketId,true)); 
    if ($usersForUpdate) {
      foreach ($usersForUpdate as $idWebsocket) {
        if (isset($this->clients[$idWebsocket])) {
          // error_log('if (isset($this->clients[$idWebsocket])) {');

          // Вызов переданного метода для получения данных
          $message = json_encode($data);
          // error_log(print_r($data,true));
          // error_log(print_r($messagePrefix,true));

          // Отправка сообщения всем клиентам
          foreach ($this->clients[$idWebsocket] as $client) {
            // error_log('if (isset($this->clients[$idWebsocket])) { foreach ($this->clients[$idWebsocket] as $client) {');
            // error_log(print_r($idWebsocket,true));
            $client->send($messagePrefix . '::: ' . $message);
          }
        }
      }
    }
  }
  private function printInvoice($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $data = json_decode($body);
    // $this->catcherBugs->convPrintLog($data, 'printInvoice', '$data');
    $data = $this->interactionInterface->printInvoice($idWebsocket, $data);
    $message = json_encode($data);
    $from->send('printInvoicePDF::: ' . $message);
  }

  private function setConditionForSearch($from, $body, $where)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $this->interactionInterface->setConditionForSearch($idWebsocket, $body, $where);
  }
  public function showAnalytics($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $data = json_decode($body);
    $analytics = $this->interactionInterface->showAnalytics($idWebsocket, $data);
    // $this->catcherBugs->convPrintLog($analytics, 'chat showAnalytics', 'analytics');
    $message = json_encode($analytics);
    $from->send('AnalyticsOnPage::: ' . $message);
  }
  public function setAnalyticsWhose($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsWhose($idWebsocket, $body);
  }
  public function setAnalyticsDialoguesType($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsDialoguesType($idWebsocket, $body);
  }
  public function getAnalyticsUserSearch($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $response = $this->interactionInterface->getAnalyticsUserSearch($idWebsocket, $body);
    $message = json_encode($response);
    $from->send('analyticsUserSearch::: ' . $message);
  }
  public function setAnalyticsUser($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsUser($idWebsocket, $body);
  }
  public function setStartDateAnalytics($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setStartDateAnalytics($idWebsocket, $body);
  }
  public function setEndDateAnalytics($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setEndDateAnalytics($idWebsocket, $body);
  }
  public function setAnalyticsPeriod($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsPeriod($idWebsocket, $body);
  }
  public function setAnalyticsTimeMode($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsTimeMode($idWebsocket, $body);
  }
  public function setAnalyticsSlaFirstResponse($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsSlaFirstResponse($idWebsocket, $body);
  }
  public function setAnalyticsSlaSubsequentResponse($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $body = json_decode($body);
    $this->interactionInterface->setAnalyticsSlaSubsequentResponse($idWebsocket, $body);
  }
  public function getDealerDependentsAnalytics($from, $body)
  {
    $idWebsocket = $this->findCurrentConnection($from);
    $data = json_decode($body);
    $analytics = $this->interactionInterface->getDealerDependentsAnalytics($idWebsocket, $data);
    $message = json_encode($analytics);
    $from->send('AnalyticsDealerDependentsOnPage::: ' . $message);
  }
  public function onOpen(ConnectionInterface $conn)
  {
    ob_start();
  }
  public function onMessage(ConnectionInterface $from, $msg)
  {
    ob_start();
    $data = explode('::: ', $msg);
    $title = $data[0];
    $body = $data[1];
    // try {
    switch ($title) {
      case "idUser":
        $this->regUser($from, $body);
        break;
      case "perPageContracts":
        $this->changePerPage($from, $body, 'contracts');
        break;
      case "perPageOrders":
        $this->changePerPage($from, $body, 'orders');
        break;
      case "perPageInvoices":
        $this->changePerPage($from, $body, 'invoices');
        break;
      case "perPageContractors":
        $this->changePerPage($from, $body, 'contractors');
        break;
      case "currentPageContracts":
        $this->changeCurrentPage($from, $body, 'contracts');
        break;
      case "currentPageOrders":
        $this->changeCurrentPage($from, $body, 'orders');
        break;
      case "currentPageContractors":
        $this->changeCurrentPage($from, $body, 'contractors');
        break;
      case "currentPageInvoices":
        $this->changeCurrentPage($from, $body, 'invoices');
        break;

      case "setFilterContracts":
        $this->getFilterContract($from, $body, 'contracts');
        break;

      case "getPotencialParticipiant":
        $this->takePotencialParticipiant($from, $body);
        break;
      case "newContract":
        $this->newContractFromUser($from, $body);
        break;
      case "getDataForContract":
        $this->userOpenContract($from, $body);
        break;
      case "newMessage":
        // $this->catcherBugs->convPrintLog($body, 'newMessage', '$body');
        $this->regNewMessage($from, $body);
        break;
      case "readedMessage":
        $this->readedMessage($from, $body);
        break;
      case "deleteEmptyContracts":
        $this->deleteEmptyContract($from, $body);
        break;
      case "addParticipiant":
        // error_log('addParticipiant');
        $this->addParticipiant($from, $body);
        break;
      case "addMeParticipiant":
        // error_log('addMeParticipiant');
        $this->addMeParticipiant($from, $body);
        break;
      case "quitDialog":
        // error_log('quitDialog');
        $this->quitDialog($from, $body);
        break;
      case "setReplacementForUser":
        $this->setReplacementForUser($from, $body);
        break;
      case "setStatusUser":
        $this->setStatusUser($from, $body);
        break;
      case "changeNameContract":
        $this->changeNameContract($from, $body);
        break;
      case "setFilterOrders":
        $this->getFilterContract($from, $body, 'orders');
        break;
      case "setStartDateOrders":
        $this->setStartDate($from, $body, 'orders');
        break;
      case "setEndDateOrders":
        $this->setEndDate($from, $body, 'orders');
        break;
      case "packageInfoOrders":
        $this->packageInfoOrders($from, $body);
        break;
      case "printReportOrders":
        $this->printReportOrders($from, $body);
        break;
      case "createInvoiceOrders":
        break;
      case "createShipmentOrders":
        break;
      case "savePoints":
        $this->savePoints($from, $body);
        break;

      case "closeContracts":
        $this->closeObjectRelationship($from, $body, 'contract');
        break;
      case "setFilterRoleContractors":
        $this->getFilterContract($from, $body, 'filterRoleContractors');
        break;
      case "setFilterStatusContractors":
        $this->getFilterContract($from, $body, 'filterStatusContractors');
        break;


      case "searchQueryContracts":
        $this->getSerchQuery($from, $body, 'contracts');
        break;
      case "searchQueryOrders":
        $this->getSerchQuery($from, $body, 'orders');
        break;
      
      case "searchQueryInvoices":
        $this->getSerchQuery($from, $body, 'invoices');
        // $this->catcherBugs->convPrintLog($body, 'searchQueryInvoices', '$body');
        break;
      case "setConditionForSearchOrders":
        $this->setConditionForSearch($from, $body, 'orders');
        break;
      case "searchQueryContractors":
        $this->getSerchQuery($from, $body, 'contractors');
        break;
      case "searchForBindContractors":
        $this->searchForBindContractors($from, $body);
        break;


      case "bindContractors":
      case "unbindContractors":
      case "changeStatusContractors":
      case "changeUserNameContractors":
        // $this->catcherBugs->convPrintLog($title, 'updateConditionUser', '$title');
        // $this->catcherBugs->convPrintLog($body, 'updateConditionUser', '$body');
        $this->updateConditionUser($from, $body, $title . 'MC');
        break;
      case "setEmailNotification":
        $var = json_decode($body);
        if (!empty($var)) {
          // error_log('Set Param true');
        }
        $this->setParamUserByMyself($from, $title, $body);
        break;
      case "showContracts":
        error_log('Here changeContractOnPage');
        $this->changeContractOnPage($from);
        break;
      case "showOrders":
        $this->showOrders($from);
        break;
      case "showInvoices":
        error_log('Here changeContractOnPage');
        $this->showInvoices($from);
        break;
      case "showContractors":
        $this->showContractors($from);
        break;
      case "requestUpdateOrdersUP":
        // $this->catcherBugs->convPrintLog($body, 'requestUpdateOrdersUP', '$body');
        $this->requestUpdateOrders($body);
        break;
      case "requestUpdatePointUP":
        // error_log($title);
        $this->requestUpdatePointUP($body);
        break;
      case "requestUpdateDealerUP":
        // error_log($title);
        $this->requestUpdateDealerUP($body);
        break;
      case "requestUpdateFirmUP":
        // error_log($title);
        $this->requestUpdateFirmUP($body);
        break;
      case "requestUpdateInvoiceUP":
        // error_log($title);
        $this->requestUpdateInvoiceUP($body);
        break;
      case "printInvoice":
        // $this->catcherBugs->convPrintLog($body, 'printInvoice', '$body');
        $this->printInvoice($from, $body);
        break;
      case "showAnalytics":
        // $this->catcherBugs->convPrintLog($title, 'showAnalytics', '$title');
        // $this->catcherBugs->convPrintLog($body, 'showAnalytics', '$body');
        $this->showAnalytics($from, $body);
        break;
      case "setAnalyticsWhose":
        $this->setAnalyticsWhose($from, $body);
        break;
      case "setAnalyticsDialoguesType":
        $this->setAnalyticsDialoguesType($from, $body);
        break;
      case "setAnalyticsPeriod":
        $this->setAnalyticsPeriod($from, $body);
        break;
      case "setAnalyticsTimeMode":
        $this->setAnalyticsTimeMode($from, $body);
        break;
      case "setAnalyticsSlaFirstResponse":
        $this->setAnalyticsSlaFirstResponse($from, $body);
        break;
      case "setAnalyticsSlaSubsequentResponse":
        $this->setAnalyticsSlaSubsequentResponse($from, $body);
        break;
      case "setAnalyticsUser":
        $this->setAnalyticsUser($from, $body);
        break;
      case "getAnalyticsUserSearch":
        // $this->catcherBugs->convPrintLog($body, 'getAnalyticsUserSearch', '$body');
        $this->getAnalyticsUserSearch($from, $body);
        break;
      case "setStartDateAnalytics":
        $this->setStartDateAnalytics($from, $body);
        break;
      case "setEndDateAnalytics":
        $this->setEndDateAnalytics($from, $body);
        break;
      case "getDealerDependentsAnalytics":
        $this->getDealerDependentsAnalytics($from, $body);
        break;
     
    }
    // } 
    // catch (Exception $e) {
    //   // Логирование ошибки
    //   error_log($e->getMessage());
    //   return ['error' => 'onMessage'];
    // }
  }
  public function onClose(ConnectionInterface $conn)
  {
    $idWebsocket = $this->findCurrentConnection($conn);
    if ($idWebsocket !== null && isset($this->clients[$idWebsocket])) {
      $this->interactionInterface->deleteUser($idWebsocket);
      unset($this->clients[$idWebsocket]);
    }
  }
  public function onError(ConnectionInterface $conn, \Exception $e)
  {
    $conn->close();
    // error_log("Closed connection on error: {$e->getMessage()} (ID {$id})");
  }
}