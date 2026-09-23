<?php
namespace PersonalAccount\ObjectRelationship;
use PersonalAccount\ObjectRelationship\traits\capsule\CapsuleContract;
use Exception;
use PersonalAccount\Dialog\Dialog;
use PersonalAccount\ObjectRelationship\ObjectRelationship;
class Contract extends ObjectRelationship
{
  use CapsuleContract;
  // use DependencyInjections;
  // use catcherBugsTemporary;
  private $idPoint;
  private $pointName;
  private $nameContract;

  public function __construct($paramCurrentObject, $container, $parent)
  {
    parent::__construct($paramCurrentObject, $container, $parent);

    $this->type = 'Contract';
    $this->nameContract = $paramCurrentObject['nameContract'];
    $this->idPoint = $paramCurrentObject['idPoint'];
    $this->pathTableOrderDB = $this->dbWorker->getPathTable('Order');
    $this->pointName = $this->getNamePointDB();
    // $this->dataDialogues = [
    //   'dataDialoguesConsultation' => $this->getDialogues('Consultation'),
    //   'dataDialoguesOrder' => $this->getDialogues('Order'),
    //   'dataDialoguesComplaint' => $this->getDialogues('Complaint'),
    // ];
    // $this->catcherBugs->convPrintLog($this->serialNumber, 'Contract', '$this->serialNumber');
    $this->updateOrdersDataOR($this->pathTableOrderDB, 'contract_sn', $this->serialNumber);
    $this->idOrders = $this->setIdOrders($this->ordersData, $this->idOrders);
    $this->idUsersHaveAccess = $this->setUsersHaveAccess($this->idPoint);
    // $this->catcherBugs->convPrintLog($this->idUsersHaveAccess, 'Contract', '$this->idUsersHaveAccess');
    $this->usersHaveAccess = $this->userUtilities->getInfoAboutUsers($this->idUsersHaveAccess);
  }
  protected function getNamePointDB()
  {
    //Будем запрашивать из Parent object
    $pointName = $this->wpdb->get_var("SELECT name_point FROM gi_new_points WHERE id_point = '$this->idPoint'");
    ;
    return $pointName;
  }
  private function setUsersHaveAccess($idPoint)
  {
    $users = $this->userUtilities->getUsersByRoles($this->rolesFactory);
    $idsfactoryWorker = $this->userUtilities->getUsersIds($users);
    $idDealerUP = $this->dbWorker->selectResultsFromDBU_2('gi_new_points', 'id_point', $idPoint, 'id_dealer');
    $idDistributorUP = $this->dbWorker->selectResultsFromDBU_2('gi_new_dealers', 'id_dealer', $idDealerUP, 'id_distributor');
    $idPointsDealer = $this->dbWorker->selectSimple('Points', 'id_dealer', $idDealerUP, 'id_point');
    $idDealer = $this->dbWorker->selectResultsFromDBU_2('gi_new_users', 'id_dealer', $idDealerUP, 'id_user');
    $idDistributor = null;
    if (!empty($idDistributorUP)) {
      $idDistributor = $this->dbWorker->selectResultsFromDBU_2('gi_new_users', 'id_distributor', $idDistributorUP, 'id_user');
    }
    // $conditions = $this
    $idDesignerDealer = $this->dbWorker->selectResultsFromDBU_2('gi_new_users', 'id_point', $idPointsDealer, 'id_user');
    $idUsers = $this->simpleUtilities->combineValues($idsfactoryWorker, $idDealer, $idDistributor, $idDesignerDealer);
    // $this->catcherBugs->convPrintLog($idUsers, 'setUsersHaveAccess', '$idUsers');
    $usersHaveAccess = $this->checkWhoBlockedAccess($idUsers);
    return $usersHaveAccess;
  }
  private function checkWhoBlockedAccess($idUsers)
  {
    $haveAccess = [];
    foreach ($idUsers as $id) {
      $check = $this->dbWorker->selectVarSimple('Users', 'id_user', $id, 'status_activity');
      if ($check !== 'blocked') {
        $haveAccess[] = $id;
      }
    }
    return $haveAccess;
  }
  private function packageDialogues($dialogues)
  {
    $dataDialoguesForUser = [];
    if (!empty($dialogues)) {
      foreach ($dialogues as $dialogType) {
        // Проверяем, что в типе диалога есть данные
        if (!empty($dialogType)) {
          // Перебираем каждый диалог и получаем данные для пользователя
          foreach ($dialogType as $dialog) {
            /** @var Dialog $dialog */
            $dataDialoguesForUser[] = $dialog->getDataDialoguesForUser();
          }
        }
      }
    }
    // Перебираем каждый тип диалога
    return $dataDialoguesForUser;
  }
  public function callContract()
  {
    try {
      $this->updateOrdersDataOR($this->pathTableOrderDB, 'contract_sn', $this->serialNumber);
      $this->dialogues = [];

      // get data dialogues from DB

      $dataConsultation = $this->getDialogues('Consultation');
      $dataOrder = $this->getDialogues('Order');
      $dataComplaint = $this->getDialogues('Complaint');

      if (empty($this->dialogues) && (!empty($dataConsultation) || !empty($dataOrder) || !empty($dataComplaint))) {
        $this->dialogues['dialoguesConsultation'] = $this->factory->createDependentObjects('DialogConsultation', $this->container, $dataConsultation, $this);
        $this->dialogues['dialoguesOrder'] = $this->factory->createDependentObjects('DialogOrder', $this->container, $dataOrder, $this);
        $this->dialogues['dialoguesComplaint'] = $this->factory->createDependentObjects('DialogComplaint', $this->container, $dataComplaint, $this);
        // $this->leafs = $this->simpleUtilities->collectLeafs($this->dialogues['dialoguesConsultation'], $this->dialogues['dialoguesOrder'], $this->dialogues['dialoguesComplaint']);
        // $this->catcherBugs->convPrintLog($this->dialogues, 'Contract', 'dialogues');
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'An error occurred while processing the callContract'];
    }
  }
  public function getDataContract($idUser)
  {
    try {
      // Упаковываем данные диалогов для пользователя
      $this->updateOrdersDataOR($this->pathTableOrderDB, 'contract_sn', $this->serialNumber);
      $dataContract = [
        'serialNumber' => $this->serialNumber,
        'nameContract' => $this->nameContract,
        'dataDialogues' => $this->packageDialogues($this->dialogues),
        'dataOrders' => $this->ordersData,
        'potencialParticipiant' => $this->userUtilities->preparingPotencialParticipant($this->usersHaveAccess, $idUser),
      ];
      // Возвращаем или используем данные для дальнейшей обработки
      return $dataContract;
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'An error occurred while processing the getDataContract'];
    }
  }
  public function deleteContract($serialNumber)
  {
    if (count($this->dataDialogues) === 0) {
      $this->wpdb->query("DELETE FROM gi_new_contract WHERE sn = '$serialNumber'");
      // unset($this);
    }
  }
  public function getOutsideParamContract()
  {
    $contract = [
      //'linkContract' => &$this,
      'serialNumber' => $this->serialNumber,
      'nameContract' => $this->nameContract,
      'dateLastActivity' => $this->dateLastActivity,
      'dateLastActivityTimestamp' => $this->dateLastActivityTimestamp,
      'dateCreation' => $this->dateCreation,
      'dateCreationTimestamp' => $this->dateCreationTimestamp,
      'idPoint' => $this->idPoint,
      'pointName' => $this->pointName,
      'idOrders' => $this->idOrders,
    ];
    return $contract;
  }
  // public function getContractForCheck()
  // {
  //     $contract = [
  //       //'linkContract' => &$this,
  //       'serialNumber' => $this->serialNumber,
  //       'nameContract' => $this->nameContract,
  //       'dateLastActivity' => $this->dateLastActivity,
  //       'dateLastActivityTimestamp' => $this->dateLastActivityTimestamp,
  //       'dateCreation' => $this->dateCreation,
  //       'dateCreationTimestamp' => $this->dateCreationTimestamp,
  //       'idPoint' => $this->idPoint,
  //       'pointName' => $this->pointName,
  //       'idOrders' => $this->idOrders,
  //     ];
  //     return $contract;
  // }
  public function getIdPoint()
  {
    return $this->idPoint;
  }
  public function getDateCreation()
  {
    return $this->getProperty('dateCreation');
  }
  private function getDataOneDialog($idDialog)
  {
    $sqlDialog = $this->wpdb->get_results("SELECT * FROM gi_new_dialogues 
    WHERE id_dialog = '$idDialog'");
    $newDialog = $this->packageDataDialogues($sqlDialog);
    return $newDialog;
  }
  public function registrationNewDialog($typeDialog, $idDialog)
  {
    $newDialogDB = $this->getDataOneDialog($idDialog);
    $nameArray = 'dialogues' . $typeDialog;
    $nameClass = 'Dialog' . $typeDialog;
    // $this->catcherBugs->convPrintLog($newDialogDB, 'registrationNewDialog', '$newDialogDB');
    $newObjectDialog = $this->factory->createDependentObjects($nameClass, $this->container, $newDialogDB, $this);
    if (!isset($this->dialogues[$nameArray])) {
      $this->dialogues[$nameArray] = [];
    }
    if (is_array($newDialogDB)) {
      foreach ($newDialogDB as $key => $value) {
        $this->dialogues[$nameArray][$key] = $newObjectDialog[0];
      }
    }
    // array_push($this->dialogues[$nameArray], $newObjectDialog[0]);
  }

  public function findDialog($idDialog)
  {
    foreach ($this->dialogues as $dialoguesType => $typesDialogues) {
      if (!empty($typesDialogues)) {
        foreach ($typesDialogues as &$dialog) {
          $idDialogInContract = $dialog->getIdDialog();
          if (intval($idDialogInContract) === intval($idDialog)) {
            $linkDialog = &$dialog;
            return $linkDialog;
          }
        }
      }
    }
  }



  public function checkEmptyContract()
  {
    foreach ($this->dataDialogues as $type) {
      if (!empty($type)) {
        return false; // Нашли непустой элемент, возвращаем true
      }
    }
    return true; // Все элементы пустые, возвращаем false
  }
  public function changeNameContractC($serialNumber, $nameContract)
  {
    $this->setNameContractDB($serialNumber, $nameContract);
    $this->setNameContract($serialNumber, $nameContract);
  }
  private function setNameContractDB($serialNumber, $nameContract)
  {
    if ($this->serialNumber === $serialNumber) {
      $this->wpdb->query("UPDATE gi_new_contract SET name_contract = '$nameContract' WHERE sn = '$this->serialNumber'");
    }
  }
  private function setNameContract($serialNumber, $nameContract)
  {
    if ($this->serialNumber === $serialNumber) {
      $this->nameContract = $nameContract;
    }
  }
  protected function prepareOrdersForUserU($sqlOrders, $outputTime)
  {
    $allOrders = [];
    $i = 0;
    $outputTime = $outputTime * 24 * 60 * 60;
    foreach ($sqlOrders as $order) {
      $outputDate = convert_timestamp_index($order->confirmation_date) + $outputTime;
      $allOrders[$i] = [
        'id' => $order->id,
        'orderNumber' => $order->order_name,
        'clientName' => $order->client_name,
        'receptionDate' => $order->receiption_date,
        'proformaDate' => $order->proforma_date,
        'confirmationDate' => $order->confirmation_date,
        'invoiceDate' => $order->invoice_date,
        'paymentDate' => $order->payment_date,
        'requiredDate' => $order->required_date,
        'shipmentDate' => $order->shipping_date,
        'receptionDateTS' => convert_timestamp_index($order->receiption_date),
        'confirmationDateTS' => convert_timestamp_index($order->confirmation_date),
        'invoiceDateTS' => convert_timestamp_index($order->invoice_date),
        'paymentDateTS' => convert_timestamp_index($order->payment_date),
        'requiredDateTS' => convert_timestamp_index($order->required_date),
        'outputDateTS' => $outputDate,
        'shipmentDateTS' => convert_timestamp_index($order->shipping_date),
        'status' => $order->status_order,
        'shipmentNumber' => $order->shipping_number,
        'namePoint' => $order->point_name,
        'brutto' => $order->brutto,
        'netto' => $order->netto,
        'volume' => $order->volume,
        'serialNumber' => $order->contract_sn,
        'shipmentPassword' => $order->shipment_password,
      ];
      $allOrders[$i]['nameContract'] = !empty($allOrders[$i]['serialNumber'])
        ? $this->dbWorker->selectVarFromDBU('gi_new_contract', 'sn', intval($order->contract_sn), 'name_contract')
        : '';
      $allOrders[$i]['orderStatusDate'] = !empty($allOrders[$i]['status']) ? $this->defineStatusDate($allOrders[$i]['status'], $allOrders[$i]) : '';
      $allOrders[$i]['orderStatusDateTS'] = !empty($allOrders[$i]['status']) ? convert_timestamp_index($allOrders[$i]['orderStatusDate']) : '';
      $i++;
    }
    return $allOrders;
  }
  protected function defineStatusDate($orderStatus, $order)
  {
    switch ($orderStatus) {

      case 'Проформа':
        $dateStatus = $order['proformaDate'];
        break;
      case 'Подтверждён':
        $dateStatus = $order['confirmationDate'];
        break;
      case 'Обработка':
      case 'Производство':
      case 'Упакован':
      case 'Отгружен частично':
      case 'Отгружен':
        $dateStatus = '';
        break;
    }
    return $dateStatus;
  }

  public function getOutSideIdPoint()
  {
    return $this->idPoint;
  }
  public function updateUsersHaveAccess()
  {
    $this->idUsersHaveAccess = $this->setUsersHaveAccess($this->idPoint);
    $this->usersHaveAccess = $this->userUtilities->getInfoAboutUsers($this->idUsersHaveAccess);
  }
}