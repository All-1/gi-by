<?php
namespace PersonalAccount\Controler;
use PersonalAccount\Controler\traits\CapsuleControlerObjectsRelationship;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\UPWorker;
use PersonalAccount\Workers\ORWorker;
use PersonalAccount\factory\Factory;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
use PersonalAccount\ObjectRelationship\Contract;
use PersonalAccount\ObjectRelationship\ObjectRelationship;
use PersonalAccount\Dialog\Dialog;
use Exception;

class ControlerObjectsRelationship
{
  use Utilit; //Содержаться все функции которые имеют к базе данных, но нужны в разных объектах, а так же общая функция для создания капсулы
  use CapsuleControlerObjectsRelationship;
  // use catcherBugsTemporary;
  // use workWithUP;

  /** @var Container */
  private $container;
  /** @var Factory */
  private $factory;
  /** @var DBWorker */
  protected $dbWorker;
  /** @var UPWorker */
  protected $upWorker;
  /** @var DataUtilities */
  protected $dataUtilities;
  /** @var DBUtilities */
  protected $dbUtilities;
  /** @var CatcherBugs */
  protected $catcherBugs;
  /** @var UserUtilities */
  protected $userUtilities;
  /** @var SimpleUtilities */
  protected $simpleUtilities;
  /** @var ORWorker */
  private $orWorker;
  private $allPoints;
  private $allShipments;
  private $allInvoices;
  private $allContracts;
  private $contractsForfactory; //Ссылка на все Contracts на точках.
  // private $orderForfactory; //Ссылка на все Order.
  private $lastLogIdOrdersUP;

  // private $closures;
  public function __construct($container)
  {

    $this->container = &$container;
    $this->upWorker = $this->container->get('UPWorker');
    $this->dbWorker = $this->container->get('DBWorker');
    $this->factory = $this->container->get('Factory');
    $this->orWorker = $this->container->get('ORWorker');

    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');

    $this->initializationWPDB();
    $logIdPoint = $this->upWorker->getLogIdPoint();
    // $logIdInvoice = $this->upWorker->getLogIdInvoice();
    $logIdInvoice = 0;
    $lastLogIdOrdersUP = $this->upWorker->getLogIdOrder();

    $this->allPoints = [];

    $this->allContracts = [];
    $this->allInvoices = [];
    $this->allShipments = [];
    // $allInvoicesData = $this->getObjectsRelationshipDB('Invoice', 'invoice');
    // $allShipmentsData = $this->getObjectsRelationshipDB('Shipment', 'shipment');


    $this->requestUpdateInvoiceUP($logIdInvoice);


    // $this->firstInicializationOrdersUP();

    $lastLogIdOrdersUP = $lastLogIdOrdersUP == 0 ? 1 : $lastLogIdOrdersUP;

    // $this->catcherBugs->convPrintLog($lastLogIdOrdersUP, 'ControlerObjectsRelationship constructor', '$lastLogIdOrdersUP');

    $logIdPoint = $logIdPoint == 0 ? 1 : $logIdPoint;

    $this->requestUpdatePointUP($logIdPoint);


    $orders = $this->requestUpdateOrdersCOR($lastLogIdOrdersUP);
    if (!empty($orders)) {
      $this->updateOrdersPointsCOR($orders);
    }


    // $this->requestUpdateInvoicesUP_NEW($logIdInvoice);

    // if (!empty($orders)) {
    //   $this->updateOrdersPointsCOR($orders);
    // }

    // $logIdInvoice = $this->dbWorker->selectVarSimple('LogSincUP', 'id', 4, 'last_log_id');



    // $allPointsData = $this->dbWorker->selectSimple('Points');
    // $this->allPoints = $this->factory->createDependentObjects('Point', $container, $allPointsData, $this);
  }
  private function firstInicializationOrdersUP()
  {
    // $this->catcherBugs->convPrintLog('KYKYKYKYKYKYKYK', 'firstInicializationOrdersUP', 'KYKYKYKYKYKYKYK');
    $orders = $this->upWorker->requestUpdateOrdersUP();
    if (!empty($orders)) {
      // $this->catcherBugs->convPrintLog('KYKYKYKYKYKYKYK', 'firstInicializationOrdersUP', 'KYKYKYKYKYKYKYK');
      // $orderTable = $this->dbWorker->getPathTable('Orders');
      $this->dbWorker->clearTable('gi_new_orders');
      // $this->catcherBugs->convPrintLog('clearTable', 'firstInicializationOrdersUP', 'clearTable');
      $columns = $this->dbWorker->showColumnDB('gi_new_orders');
      $indexedOrders = $this->dataUtilities->convertToIndexArr($orders);
      $prepareData = $this->dbUtilities->prepareInsertTrust($indexedOrders, 1000);
      $this->dbWorker->insertMultipleTrustDB('gi_new_orders', $columns, $prepareData);
      // $this->catcherBugs->convPrintLog($prepareData, 'firstInicializationOrdersUP', 'prepareData');
      // $this->catcherBugs->convPrintLog('END Insert', 'firstInicializationOrdersUP', 'END Insert');

      // $this->updateOrdersPointsCOR($orders);
    } else {
      error_log("print_r(orders, true)");
    }
  }


  public function deleteContract($serialNumber, $idUser)
  {
    /** @var Contract $currentContract  */
    $idPoint = $this->orWorker->deleteOR($serialNumber, 'Contract');
    if (!empty($idPoint)) {
      $currentContract = $this->findContract($serialNumber, $idUser);
      if ($currentContract) {
        if ($this->allContracts[$serialNumber]) {
          unset($this->allContracts[$serialNumber]);
        }
      } else {
        error_log('deleteContractOnPoint $currentContract empty');
      }
      // $this->catcherBugs->convPrintLog($idPoint, 'deleteContractOnPoint', '$idPoint');
      return $idPoint;
    } else {
      error_log('deleteContractOnPoint $idPoint');
    }
  }

  public function findPointByContract($serialNumber, $idUser)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($serialNumber, $idUser);
    // $this->catcherBugs->convPrintLog($serialNumber, 'findPointByContract', '$idPoint');
    if (!empty($currentContract)) {
      $idPoint = $currentContract->getOutSideIdPoint();
      // $this->catcherBugs->convPrintLog($idPoint, 'findPointByContract', '$idPoint');
      return $idPoint;
    } else {
      error_log('findPointByContract $point empty');
    }
  }

  public function addDialogInContract($newDialog, $idUser)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($newDialog->serialNumber, $idUser);
    // $this->catcherBugs->convPrintLog($newDialog, 'addDialogInContract', '$newMessage', 3);
    if (!empty($currentContract)) {
      // $this->catcherBugs->convPrintLog($newDialog, 'addDialogInContract', '$newDialog', 3);
      $currentContract->registrationNewDialog($newDialog->typeDialog, $newDialog->idDialog);
    } else {
      error_log('currentContract $currentContract empty');
    }
    // $this->contractsForfactory = $this->setContractsForfactory($this->allPoints);
  }

  public function addMessageInContract($newMessage, $idUser)
  {
    /** @var Contract $currentContract */
    /**
     * @var Contract
     */
    $currentContract = $this->findContract($newMessage->serialNumber, $idUser);
    if (!empty($currentContract)) {
      /** @var Dialog $dialog */
      // $this->catcherBugs->convPrintLog($newMessage, 'regNewMessage', '$newMessage', 3);
      $dialog = $currentContract->findDialog($newMessage->idDialog);
      $currentContract->updateDateLastActivityContract();
    } else {
      error_log('addMessageInContract $currentContract empty');
    }
    if (!empty($dialog)) {
      $dialog->workWithNewMessage($newMessage);
    } else {
      error_log('addMessageInContract $dialog empty');
    }

    // $this->contractsForfactory = $this->setContractsForfactory($this->allPoints);
  }
  public function setReadedMessage($message)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($message->serialNumber, $message->whoRead);

    if (!empty($currentContract)) {
      /** @var Dialog $dialog */
      $dialog = $currentContract->findDialog($message->idDialog);
    } else {
      error_log('setReadedMessage $currentContract empty');
    }
    if (!empty($dialog)) {
      $dialog->workWithReadedMessage($message);
    } else {
      error_log('setReadedMessage $dialog empty');
    }
  }
  public function getAddParticipiant($message)
  {

    /** @var Contract */
    $currentContract = $this->findContract($message->serialNumber, $message->whoAdded);
    if (!empty($currentContract)) {
      /** @var Dialog */
      $dialog = $currentContract->findDialog($message->idDialog);
    } else {
      error_log('getAddParticipiant $currentContract empty');
    }
    if (!empty($dialog)) {
      $dialog->addParticipiantToDialog($message);
    } else {
      error_log('getAddParticipiant $dialog empty');
    }
  }
  public function userQuitDialog($message)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($message->serialNumber, $message->idUser);
    if (!empty($currentContract)) {
      /** @var Dialog $dialog */
      $dialog = $currentContract->findDialog($message->idDialog);
    } else {
      error_log('userQuitDialog $currentContract empty');
    }
    if (!empty($dialog)) {
      $dialog->participantLeftDialog($message);
    } else {
      error_log('userQuitDialog $dialog empty');
    }
  }
  private function callContract($currentContract, $idWebsocket, $idUser)
  {
    /** @var Contract $currentContract */
    if (!empty($currentContract) && !empty($idWebsocket)) {
      $currentContract->callContract();
      // $this->catcherBugs->convPrintLog($idWebsocket, 'callContract', 'idWebsocket');
      $currentContract->regWhoOpenOR($idWebsocket);
      $dataContract = $currentContract->getDataContract($idUser);
      // $this->catcherBugs->convPrintLog($dataContract, 'ControlerObjectsRelationship', 'dataContract');
      return $dataContract;
    }
  }

  private function findContract($serialNumber, $idUser)
  {
    if (!empty($this->allContracts)) {
      if (isset($this->allContracts[$serialNumber])) {
        $usersHaveAccess = $this->allContracts[$serialNumber]->getIdUsersHaveAccess();
        if (in_array($idUser, $usersHaveAccess)) {
          $linkContract = &$this->allContracts[$serialNumber];
          return $linkContract;
        } else {
          // $this->catcherBugs->convPrintLog($idUser, 'findContract else', '$this->usersHaveAccess');
        }
      } else {
        // foreach ($this->allContracts as $key => $contract) {
        //   $this->catcherBugs->convPrintLog($key, 'findContract', '$key');
        // }
      }
    }
  }
  private function instantiateContract($serialNumber)
  {
    $contractData = $this->getNewContractDB($serialNumber);
    $contract = $this->factory->createDependentObjects('Contract', $this->container, $contractData, $this);
    // $this->catcherBugs->convPrintLog($contract, 'refreshContractsOnPoint', '$contract');

    $this->allContracts[$serialNumber] = isset($contract[0]) ? $contract[0] : null;
  }
  private function getNewContractDB($serialNumber)
  {
    $sqlContract = $this->wpdb->get_results("SELECT * FROM gi_new_contract 
    WHERE sn='$serialNumber'");
    $contract = $this->dataUtilities->packagData($sqlContract, 'Contract', 'contract');
    // $this->catcherBugs->convPrintLog($contract, 'getNewContractDB', '$contract');
    return $contract;
  }
  public function getContractData_NEW($idUser, $serialNumber, $idWebsocket)
  {
    /** @var Contract $currentContract */
    // $point = $this->findPointForUser($idUser, $serialNumber);
    $currentContract = $this->findContract($serialNumber, $idUser);
    if (!empty($currentContract)) {
      // $this->catcherBugs->convPrintLog($idWebsocket, 'getContractData_NEW', 'idWebsocket');
      $dataContract = $this->callContract($currentContract, $idWebsocket, $idUser);
      return $dataContract;
    } else {
      $this->instantiateContract($serialNumber);
      $currentContract = $this->findContract($serialNumber, $idUser);
      $dataContract = $this->callContract($currentContract, $idWebsocket, $idUser);
      return $dataContract;
    }
  }

  public function getOpenContractData($idUser, $serialNumber, $idWebsocket)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($serialNumber, $idUser);
    // $this->catcherBugs->convPrintLog('point', 'getContractData', '$point');
    if (!empty($currentContract)) {
      // $this->catcherBugs->convPrintLog('!empty($currentContract)', 'getContractData', '!empty($currentContract)');
      $dataContract = $this->callContract($currentContract, $idWebsocket, $idUser);
      return $dataContract;
    } else {
      // $this->catcherBugs->convPrintLog($serialNumber, 'getOpenContractData','$serialNumber');
    }
  }
  public function getHaveAccessPoint($idUser, $serialNumber)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($serialNumber, $idUser);

    if (!empty($currentContract)) {
      $haveAccessContract = $currentContract->outsideWhoHaveAccess();
    } else {
      error_log('getHaveAccessPoint $currentContract empty');
    }
    if (!empty($haveAccessContract)) {
      return $haveAccessContract;
    } else {
      error_log('getHaveAccessPoint $haveAccessContract empty');
    }
  }
  public function updateDialogAfterReplacement($idDialogues, $message)
  {
    $condition = $this->dbUtilities->createConditionQueryIN('id_dialog', $idDialogues);
    $contracts = $this->dbWorker->selectUni('Dialogues', $condition, ['sn', 'id_dialog']);
    // $this->catcherBugs->convPrintLog($contracts, 'updateDialogAfterReplacementNew', '$contracts');
    foreach ($contracts as $contract) {
      /** @var Contract $currentContract */
      $currentContract = $this->findContract($contract['sn'], $message->hisReplacementId);
      if (!empty($currentContract)) {
        /** @var Dialog $dialog */
        $dialog = $currentContract->findDialog($contract['idDialog']);
      } else {
        error_log('updateDialogAfterReplacement $currentContract empty');
      }
      if (!empty($dialog)) {

        $dialog->addReplacementDialog();
      } else {
        error_log('updateDialogAfterReplacement $dialog empty');
      }
    }
    $contracts = $this->dataUtilities->getFromMultiArrayKey($contracts, 'sn');
    // $this->catcherBugs->convPrintLog($contracts, 'updateDialogAfterReplacementNew', '$contracts');
    return $contracts;
  }

  public function changeNameContractCOR($contract, $idUser)
  {
    /** @var Contract $currentContract */
    $currentContract = $this->findContract($contract->serialNumber, $idUser);
    $idPoint = null;

    if (!empty($currentContract)) {
      $idPoint = $currentContract->getOutSideIdPoint();
      $currentContract->changeNameContractC($contract->serialNumber, $contract->nameContract);
    } else {
      error_log('changeNameContractCOR $currentContract empty');
    }
    // $this->contractsForfactory = $this->setContractsForfactory($this->allPoints);
    return $idPoint;
  }
  public function requestUpdateOrdersCOR($logId)
  {
    //Check reload logId in UP

    //Get the orders from UP
    // $this->catcherBugs->convPrintLog($logId, 'requestUpdateOrdersCOR', '$logId');
    $orders = $this->upWorker->requestUpdateOrdersUP($logId);
    return $orders;
  }

  // public function requestUpdateInvoiceUP($logId)
  // {
  //     $invoices = $this->upWorker->requestUpdateFromUP('invoice', $logId);
  //     $firmIdNew = [];
  //     $firmIdUpdate = [];
  //     if (!empty($invoices['new'])) {
  //       $firmIdNew = $this->dataUtilities->sortMultiArrayByKey($invoices['new'], 'firmId');
  //     }
  //     if (!empty($invoices['update'])) {
  //       $firmIdUpdate = $this->dataUtilities->sortMultiArrayByKey($invoices['update'], 'firmId');
  //     }
  //     $firmId = array_merge($firmIdNew, $firmIdUpdate);
  //     $firmId = array_unique($firmId);
  //     if (!empty($firmId)) {
  //       $condition = $this->dbUtilities->createConditionQueryIN('id_firm', $firmId);
  //       $idDealers = $this->dbWorker->selectUni('Firms', $condition, 'id_dealer');
  //       $condition = $this->dbUtilities->createConditionQueryIN('id_dealer', $idDealers);
  //       $idPoints = $this->dbWorker->selectUni('Points', $condition, 'id_point');
  //       return $idPoints;
  //     }
  // }
  public function requestUpdateInvoiceUP($logId)
  {
    $invoices = $this->upWorker->requestUpdateFromUP_NEW('invoice', $logId);
    $firmIdNew = [];
    $firmIdUpdate = [];
    if (!empty($invoices['new'])) {
      $firmIdNew = $this->dataUtilities->sortMultiArrayByKey($invoices['new'], 'FirmId');
      $firmIdNew = $firmIdNew['FirmId'];
    }
    if (!empty($invoices['update'])) {
      $firmIdUpdate = $this->dataUtilities->sortMultiArrayByKey($invoices['update'], 'FirmId');
      $firmIdUpdate = $firmIdUpdate['FirmId'];
    }
    $firmId = $this->simpleUtilities->mergeArrays($firmIdNew, $firmIdUpdate);
    $firmId = !empty($firmId) ? array_unique($firmId) : [];
    // $this->catcherBugs->convPrintLog($firmId, 'requestUpdateInvoicesUP_NEW', '$firmId');
    if (!empty($firmId)) {
      $condition = $this->dbUtilities->createConditionQueryIN('id_firm', $firmId);
      $idDealers = $this->dbWorker->selectUni('Firms', $condition, 'id_dealer');
      $condition = $this->dbUtilities->createConditionQueryIN('id_dealer', $idDealers);
      $idPoints = $this->dbWorker->selectUni('Points', $condition, 'id_point');
      return $idPoints;
    }
  }
  public function requestUpdatePointUP($logId)
  {
    try {
      $pointsForUpdate = $this->upWorker->requestUpdateFromUP('point', $logId);
      //Extract IDPoints
      // $this->catcherBugs->convPrintLog($pointsForUpdate, 'requestUpdatePointUP', '$pointsForUpdate', 3);
      if (!empty($pointsForUpdate)) {
        $idPointsNew = $this->dataUtilities->getFromArrByKeyU($pointsForUpdate['new'], 'PKId');
        $idPointsUpdate = $this->dataUtilities->getFromArrByKeyU($pointsForUpdate['update'], 'PKId');
        //Instanciate new points
        // $newPointsFromDB = !empty($idPointsNew) ? $this->dbWorker->selectResultsFromDBU_2('gi_new_points', 'id_point', $idPointsNew) : [];
        // $this->catcherBugs->convPrintLog($newPointsFromDB, 'Point ', '$pointFromDB');
        // $newPoints = !empty($newPointsFromDB) ? $this->factory->createDependentObjects_2('Point', $this->container, $newPointsFromDB) : [];
        //Merge and return for following operation
        $idPoints = array_merge($idPointsNew, $idPointsUpdate);

        // `if (!empty($this->allPoints) && !empty($newPoints)) {
        //   // $this->addPoints($newPoints);
        //   // $point = $this->findPointByPointId(454);
        //   // $this->catcherBugs->convPrintLog($point, 'requestUpdatePointUP', '$ckekExist', 3);
        // }`
        return $idPoints;
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'onMessage'];
    }
  }
  // private function addPoints($newPoints)
  // {
  //   if (is_array($newPoints)) {
  //     // Merge arrays if $newPoints is an array
  //     $this->allPoints = array_merge($this->allPoints, $newPoints);
  //   } else {
  //     // Add single value to the array
  //     $this->allPoints[] = $newPoints;
  //   }
  // }

  public function updateOrdersPointsCOR($orders)
  {
    // $this->catcherBugs->convPrintLog($orders, 'updateOrdersPointsCOR', '$orders]');
    $serialNumbersNew = $this->dataUtilities->getFromArrByKeyU($orders, 'contractSN');


    $ordersIdsNew = $this->dataUtilities->getFromArrByKeyU($orders, 'PKId');
    $pathOrderTable = $this->dbWorker->getPathTable('Order');
    $ordersExist = $this->dbWorker->selectResultsFromDBU_2($pathOrderTable, 'id', $ordersIdsNew);
    $ordersIdsOld = $this->dataUtilities->getFromArrByKeyU($ordersExist, 'id');
    $selectSN = $this->dataUtilities->getFromArrByKeyU($ordersIdsNew, 'contractSn');
    $normilizeSN = $this->simpleUtilities->combineValues($selectSN, $serialNumbersNew);
    $normilizeSN = $this->dataUtilities->removeFromArray($normilizeSN, '-');

    $this->dbWorker->updateOrders($orders, $ordersIdsOld);
    // $this->catcherBugs->convPrintLog($ordersIds, 'updateOrdersPointsCOR', '$ordersIds]');
    // $this->catcherBugs->convPrintLog($normilizeSN, 'updateOrdersPointsCOR', '$normilizeSN]');

    $newLogId = $orders[0]['logId'];
    if ($newLogId) {
      $this->dbWorker->updateDBU('gi_new_log_sinc_UP', 'id', 1, 'last_log_id', $newLogId);
    }
    $this->lastLogIdOrdersUP = $this->dbWorker->selectVarFromDBU('gi_new_log_sinc_UP', 'id', 1, 'last_log_id');

    return $normilizeSN;
  }
  private function getContractsThatSee($usersWebsocketsId)
  {
    $result = [];
    foreach ($usersWebsocketsId as $idWebsocket) {
      $contract = $this->findContractThatSee($idWebsocket);
      $result[] = $contract;
    }

    return $result;
  }
  private function getContractsSN($contracts)
  {
    $result = [];
    foreach ($contracts as &$contract) {
      if (!empty($contract)) {
        $serialNumber = $contract->getSerialNumber();
        $result[] = $serialNumber;
      }
    }
    return $result;
  }
  public function getIdPointsForUpdateCOR($orders)
  {
    $pointsId = [];
    $noPoints = [];
    /** @var Contract $contract */
    if (!empty($this->allContracts)) {
      foreach ($this->allContracts as &$contract) {
        if (!empty($contract)) {
          $pointId = $contract->getOutSideIdPoint();

          foreach ($orders as $order) {
            $checkID = intval($order['pointId']) === intval($pointId);
            if ($checkID) {
              $pointsId[] = $pointId;
            } else {
              $noPoints[] = $order['pointId'];
            }
          }
        }
      }
      $noPoints = array_unique($noPoints);
      $pointsId = array_unique($pointsId);
      return $pointsId;
    }
  }

  public function userSeeContractsMassUpdateCOR($serialNumber)
  {
    // $contractsSN = $this->simpleUtilities->toArray($contractsSN);
    // $pointsId = $this->simpleUtilities->toArray($pointsId);
    if (isset($this->allContracts[$serialNumber])) {
      /** @var Contract $contract  */
      $contract = &$this->allContracts[$serialNumber];
      $contract->updateOrdersInOR();
      $usersOnline = $contract->getWhoOpenedOR();
      return $usersOnline;
    }
  }
  public function getWhoLookContract($serialNumber)
  {
    if (isset($this->allContracts[$serialNumber])) {
      /** @var Contract $contract  */
      $contract = &$this->allContracts[$serialNumber];
      $usersOnline = $contract->getWhoOpenedOR();
      return $usersOnline;
    }
  }
  private function findContractThatSee($idWebsocket)
  {
    if (!empty($this->allContracts)) {
      foreach ($this->allContracts as &$contract) {
        if (!empty($contract)) {
          /** @var Contract $contract  */
          $whoOpened = $contract->getWhoOpenedOR();
          if (!empty($whoOpened)) {
            if (in_array($idWebsocket, $whoOpened)) {
              return $contract;
            }
          }
        }
      }
    }
  }
  //Rewrite this
  public function shutDownContractCOR($idUser, $serialNumber, $idWebsocket)
  {

    $currentContract = $this->findContract($serialNumber, $idUser);

    if (!empty($currentContract)) {
      // $this->catcherBugs->convPrintLog($serialNumber, 'if (!empty($currentContract)) shutDownContractCOR', '$serialNumber');
      $this->shutDownObjectRelationship($currentContract, $idWebsocket);
      // if (isset($this->allContracts[$serialNumber])) {
      //   $this->catcherBugs->convPrintLog($serialNumber, 'if (isset($this->allContracts[$serialNumber])) shutDownContractCOR', '$serialNumber');
      // }
      // if (!empty($this->allContracts)) {
      //   foreach ($this->allContracts as &$contract) {
      //     if (!empty($contract)) {
      //       $serialNumber = $contract->getSerialNumber();
      //       $this->catcherBugs->convPrintLog($serialNumber, 'if (!empty($this->allContracts)) shutDownContractCOR', '$serialNumber');
      //     }
      //   }
      // }
    } else {
      error_log('shutDownContract $contract empty');
    }
  }

  public function updateSeeUsersCOR($idWebsocket, $idUser)
  {
    // $this->catcherBugs->convPrintLog($idWebsocket, 'updateSeeUsersCOR', '$idWebsocket');
    // $this->catcherBugs->convPrintLog($idUser, 'updateSeeUsersCOR', '$idUser');
    /** @var Contract $contract  */
    if (!empty($this->allContracts)) {
      /** @var Contract $contract  */
      foreach ($this->allContracts as &$contract) {
        if (!empty($contract)) {
          $usersHaveAccess = $contract->getIdUsersHaveAccess();
          $usersOnline = $contract->getWhoOpenedOR();
          // $this->catcherBugs->convPrintLog($usersHaveAccess, 'updateSeeUsersCOR', '$usersHaveAccess');
          if (in_array($idUser, $usersHaveAccess) && in_array($idWebsocket, $usersOnline)) {
            // $this->catcherBugs->convPrintLog($usersHaveAccess, 'if (in_array($idUser, $usersHaveAccess)) updateSeeUsersCOR', '$usersHaveAccess');
            if (!empty($contract)) {

              $this->shutDownObjectRelationship($contract, $idWebsocket);
              $contract = $this->findContractThatSee($idWebsocket);
              // if (!empty($this->allContracts)) {
              //   foreach ($this->allContracts as &$contract) {
              //     if (!empty($contract)) {
              //       $serialNumber = $contract->getSerialNumber();
              //       $this->catcherBugs->convPrintLog($serialNumber, 'if (!empty($this->allContracts)) updateSeeUsersCOR', '$serialNumber');
              //     }
              //   }
              // }
            }
          }
        }
      }
    }
  }
  private function shutDownObjectRelationship($objectRelashinship, $idWebsocket)
  {
    /** @var ObjectRelationship $objectRelashinship  */
    $objectRelashinship->userClosedOR( $idWebsocket);
    $usersOnline = $objectRelashinship->getWhoOpenedOR();

    if (empty($usersOnline)) {
      // $this->catcherBugs->convPrintLog($usersOnline, 'shutDownObjectRelationship', '$usersOnline');
      $objectRelashinship->shutDownDialoguesOR();
      $serialNumber = $objectRelashinship->getSerialNumber();
      unset($this->allContracts[$serialNumber]);
    }
  }
  public function updateUserHaveAccess($idPoints)
  {
    $idPoints = $this->simpleUtilities->toArray($idPoints);
    if (!empty($this->allContracts)) {
      /** @var Contract $contract  */
      foreach ($this->allContracts as &$contract) {
        if (!empty($contract)) {
          $idPoint = $contract->getOutSideIdPoint();
          foreach ($idPoints as $id) {
            if ($id === $idPoint) {
              $contract->updateUsersHaveAccess();
            }
          }
        }
      }
    }
  }
  public function getOpenedContractsSN($usersWebsocketsId)
  {
    if (!empty($findPoints)) {
      $contractsThatSee = $this->getContractsThatSee($usersWebsocketsId);
    } else {
      error_log('getOpenedContractsSN online !empty($findPoints)');
    }
    if (!empty($contractsThatSee)) {
      $contrectsSN = $this->getContractsSN($contractsThatSee);
    }
    return !empty($contrectsSN) ? $contrectsSN : null;
  }



}