<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Workers\DBWorker;
use \PDO;
use \PDOException;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Core\Container;
class UPWorker
{
  // use DependencyInjections;
  use Utilit;
  private $UPDB;
  private $logIdOrder; // A little bit later this will be removed
  private $logIdDealer;
  private $logIdPoint;
  private $logIdInvoice;
  private $logIdFirm;
  /** @var Container */
  private $ServicesContainer;
  /** @var DBWorker */
  private $DBWorker;
  /** @var DBUtilities */
  private $DBUtilities;
  /** @var DataUtilities */
  private $DataUtilities;
  /** @var CatcherBugs */
  private $CatcherBugs;
  /** @var SimpleUtilities */
  private $SimpleUtilities;
  public function __construct($ServicesContainer)
  {
    $this->ServicesContainer = &$ServicesContainer;
    $this->DBWorker = $this->ServicesContainer->get('DBWorker');
    $this->DBUtilities = $this->ServicesContainer->get('DBUtilities');
    $this->DataUtilities = $this->ServicesContainer->get('DataUtilities');
    $this->CatcherBugs = $this->ServicesContainer->get('CatcherBugs');
    $this->SimpleUtilities = $this->ServicesContainer->get('SimpleUtilities');

    $this->logIdOrder = $this->DBWorker->selectVarSimple('LogSincUP', 'id', 1, 'last_log_id');
    $this->logIdPoint = $this->DBWorker->selectVarSimple('LogSincUP', 'id', 2, 'last_log_id');
    $this->logIdDealer = $this->DBWorker->selectVarSimple('LogSincUP', 'id', 3, 'last_log_id');
    $this->logIdInvoice = $this->DBWorker->selectVarSimple('LogSincUP', 'id', 4, 'last_log_id');
    $this->logIdFirm = $this->DBWorker->selectVarSimple('LogSincUP', 'id', 5, 'last_log_id');
  }
  private function initialisationUP()
  {
    try {
      $host = '86.57.128.78';
      $port = '1433';

      $db = 'gi02';
      $user = 'gi_Dealer';
      $pass = 'Kuwe4724';
      $dsn = "dblib:host=$host;port=$port;dbname=$db;charset=cp1251;Encrypt=yes;TrustServerCertificate=yes";
      $this->UPDB = new PDO($dsn, $user, $pass);
      $this->UPDB->exec("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");

      error_log(print_r('initialisationUP committed', true));
    } catch (PDOException $e) {
      error_log('DB connection failed: ' . $e->getMessage());
      $this->UPDB = null; // Explicitly set to null on failure
    }
  }
  public function getOrderListFromUP($orderIDs)
  {
    $this->initialisationUP();
    if ($this->UPDB && $statement = $this->UPDB->prepare("exec GetProductsExt @OrderIds=:orders_string")) {
      $ordersString = implode(', ', $orderIDs);
      $statement->bindValue(':orders_string', $ordersString);
      $statement->execute();
      $orderList = [];
      while ($row = $statement->fetch()) {
        $orderList[] = $row;
      }
      $this->closeConnection();
      return $orderList;
    }
    return []; // Return empty array if connection failed
  }
  public function packageOrderList($item, $i)
  {
    $order = [];
    if ($item["SeatNumber"] === null) {
      $item["SeatNumber"] = 'изделие не упаковано';
    }
    $order['№ п/п'] = $i;
    $order['№ места'] = $item['SeatNumber'];
    $order['Изделие'] = $this->changeVariable($item['ProductName']);
    $order['Кол-во'] = $this->changeVariable($item['ProductQuantity']);
    $order['Склад 1'] = '';
    $order['Склад 2'] = '';
    return $order;
  }
  private function prepareOrders($data)
  {
    $orders = [];
    $i = 0;
    foreach ($data as $row) {
      $orders[$i] = [
        'PKId' => $row['PKId'], //
        'orderName' => $this->changeVariable($row['OrderName']),
        'clientName' => $this->changeVariable($row['ClientName']), //
        'receptionDate' => $this->checkDateUP($row['ReceptionDate']), //
        'proformaDate' => $this->checkDateUP($row['ProformaDate']), //
        'confirmationDate' => $this->checkDateUP($row['ConfirmationDate']), //
        'invoiceDate' => $this->checkDateUP($row['InvoiceDate']), //
        'requiredDate' => $this->checkDateUP($row['RequiredDate']), //
        'shipmentDate' => $this->checkDateUP($row['ShipmentDate']), //
        'status' => $this->checkStatusOrder($row['OrderStatus']), //
        'shipmentId' => $this->changeVariable($row['ShipmentId']), //
        'pointName' => $this->changeVariable($row['PointName']), //
        'pointId' => $this->changeVariable($row['PointId']), //
        'brutto' => $this->changeVariable($row['Brutto']), //
        'netto' => $this->changeVariable($row['Netto']), //
        'volume' => $this->changeVariable($row['Volume']), //
        'shipmentPassword' => $this->changeVariable($row['ShipmentPassword']),
        'contractSN' => $this->changeVariable($row['SN']), //
        'InvoiceId' => $this->changeVariable($row['InvoiceId']),
      ];
      if (isset($row['logId'])) {
        $orders[$i]['logId'] = $row['logId']; //
      }
      if ($row['SN'] == 1) {
      }
      $i++;

    }
    return $orders;
  }
  private function fetchUP($statement)
  {
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);  // Получаем все данные сразу
    $statement->closeCursor();  // Освобождает ресурсы, связанные с запросом
    // $this->CatcherBugs->convPrintLog($result, 'fetchUP', '$result');
    return $result;
  }
  private function preparePoint($data)
  {
    $oldPoints = $this->DBWorker->selectResultsFromDBU_2('gi_new_points');
    $oldPointsIds = !empty($oldPoints) ? array_column($oldPoints, 'idPoint') : [];
    $newPoints = [];
    $updatePoints = [];
    foreach ($data as $row) {
      $dataNew = [
        'PKId' => $this->checkForNumeric($row['PKId']),
        'PointName' => $this->changeVariable($row['PointName']),
        'DealerId' => $this->checkForNumeric($row['DealerId']),
        'idManager' => 0,
        'State' => $this->checkForNumeric($row['State']),
      ];
      if (in_array($dataNew['PKId'], $oldPointsIds)) {
        $managerPoint = $this->DataUtilities->sortArray($oldPoints, 'idPoint', $dataNew['PKId'], 'idManager');
        $dataNew['idManager'] = !empty($managerPoint[0]) ? $managerPoint[0] : 0;
        $updatePoints[] = $dataNew;
      } else {
        $newPoints[] = $dataNew;
      }
    }
    return [
      'new' => $newPoints,
      'update' => $updatePoints,
    ];
  }
  private function prepareDealer($data)
  {
    $oldDealers = $this->DBWorker->selectResultsFromDBU_2('gi_new_dealers');
    $oldDealersIds = !empty($oldDealers) ? array_column($oldDealers, 'idDealer') : []; // Получаем массив всех idPoint из старых точек
    $updateDealers = [];
    $newDealers = [];
    foreach ($data as $row) {
      $dealerData = [
        'PKId' => $this->checkForNumeric($row['PKId']),
        'DealerName' => $this->changeVariable($row['DealerName']),
        'DistributorId' => $this->checkForNumeric($row['DistributorId']),
        'State' => $this->checkForNumeric($row['State']),
      ];
      // Проверяем, есть ли точка в старых
      if (in_array($dealerData['PKId'], $oldDealersIds)) {
        $updateDealers[] = $dealerData;
      } else {
        $newDealers[] = $dealerData;

      }
    }
    return [
      'new' => $newDealers,
      'update' => $updateDealers
    ];
  }
  private function prepareInvoice_NEW($data)
  {
    // $this->CatcherBugs->convPrintLog($data, 'prepareInvoice_NEW', '$data', 1);
    $invoiceData = [
      'PKId' => $this->checkForNumeric($data['PKId']),
      'InvoiceDate' => $this->checkDateUP($data['InvoiceDate']),
      'FirmId' => $this->checkForNumeric($data['FirmId']),
      'VAT' => $this->checkForNumeric($data['VAT']),
      'VATIncluded' => $this->checkForNumeric($data['VATIncluded']),
      'InvoiceChange' => $this->checkForNumeric($data['InvoiceChange']),
      'InvoiceSum' => $this->checkForNumeric($data['InvoiceSum']),
      'PaidSum' => $this->checkForNumeric($data['PaidSum']),
    ];
    return $invoiceData;
  }
  private function prepareFirm($data)
  {
    $oldFirms = $this->DBWorker->selectResultsFromDBU_2('gi_new_firms');
    $oldFirmsIds = !empty($oldFirms) ? array_column($oldFirms, 'idFirm') : [];
    $updateFirms = [];
    $newFirms = [];
    foreach ($data as $row) {
      $firmData = [
        'PKId' => $this->checkForNumeric($row['PKId']),
        'DealerId' => $this->checkForNumeric($row['DealerId']),
        'FirmName' => $this->changeVariable($row['FirmName']),
        'CurrId' => $this->checkForNumeric($row['CurrId']),
        'FullFirmName' => $this->changeVariable($row['FullFirmName']),
        'AccountDetailsRUB' => $this->changeVariable($row['AccountDetailsRUB']),
        'AccountDetailsRUB2' => $this->changeVariable($row['AccountDetailsRUB2']),
        'AccountDetailsEUR' => $this->changeVariable($row['AccountDetailsEUR']),
        'AccountDetailsBYN' => $this->changeVariable($row['AccountDetailsBYN']),
        'Agreement' => $this->changeVariable($row['Agreement']),
        'Manager' => $this->changeVariable($row['Manager']),
        'ManagerPosition' => $this->changeVariable($row['ManagerPosition']),
      ];
      if (in_array($firmData['PKId'], $oldFirmsIds)) {
        $updateFirms[] = $firmData;
      } else {
        $newFirms[] = $firmData;
      }
    }
    return [
      'new' => $newFirms,
      'update' => $updateFirms
    ];
  }
  private function prepareInvoiceOrders($data)
  {
    $invoiceOrders = [];
    foreach ($data as $row) {
      $order['OrderName'] = $this->checkForNumeric($row['OrderName']);
      $order['ModelName'] = $this->checkForNumeric($row['ModelName']);
      $order['Price'] = $this->changeVariable($row['Price']);
      $invoiceOrders[] = $order;
    }
    return $invoiceOrders;
  }
  private function changeVariable($value)
  {
    if (!$value) {
      $result = '-';
    } else {
      $result = $value;
    }
    $result = mb_convert_encoding($result, "UTF-8", "windows-1251");
    return $result;
  }
  private function checkForNumeric($value)
  {
    if (empty($value)) {
      $result = 0;
    } else {
      $result = $value;
    }
    $result = mb_convert_encoding($result, "UTF-8", "windows-1251");
    if (is_numeric($result)) {
      // Приводим строку к числу, сохраняя целостность
      $result = strpos($result, '.') === false ? intval($result) : floatval($result);
    }
    return $result;
  }
  private function checkDateUP($value)
  {
    if (!$value) {
      $result = 0;
    } else {
      $stage = mb_convert_encoding($value, "UTF-8", "windows-1251");
      // $timestamp = strtotime($stage);
      if (is_numeric($stage)) {
        $result = date('Y-m-d H:i:s', $stage);
      } else {
        $result = $stage;
      }
    }
    return $result;
  }

  private function executionUpdateState($data, $table, $columnWhere)
  {
    $setColumn = $this->DBWorker->showColumnDB($table);
    if ($table === 'gi_new_points') {
      $setColumn = $this->DataUtilities->removeFromArray($setColumn, 'id_manager');
      $setColumn = array_values($setColumn);
      $data = $this->DataUtilities->removeFromArrayKey($data, 'idManager');
    }
    if ($table === 'gi_new_invoices') {
      // $this->CatcherBugs->convPrintLog($data, 'executionUpdateState', '$data', 1);
      // $this->CatcherBugs->convPrintLog($columnWhere, 'executionUpdateState', '$setColumn', 1);
      // $this->CatcherBugs->convPrintLog($table, 'executionUpdateState', '$table', 1);
    }
    foreach ($data as $item) {
      $indexedItem = array_values($item);
      $id = $indexedItem[0];
      $whereSqlUpdate = $this->DBUtilities->preparenSingleOperSepar($columnWhere, $id, ' = ', '');
      $setSqlUpdate = $this->DBUtilities->preparenSingleOperSepar($setColumn, $indexedItem, ' = ', ', ');

      $valuesEnable = $whereSqlUpdate['values'];
      $valuesEnable = array_merge(array_values($setSqlUpdate['values']), $valuesEnable);

      if (!empty($whereSqlUpdate['sql'])) {
        if ($table === 'gi_new_invoices') {
          // $this->CatcherBugs->convPrintLog($whereSqlUpdate['sql'], 'executionUpdateState', '$whereSqlUpdate', 1);
          // $this->CatcherBugs->convPrintLog($setSqlUpdate['sql'], 'executionUpdateState', '$setSqlUpdate', 1);
          // $this->CatcherBugs->convPrintLog($valuesEnable, 'executionUpdateState', '$valuesEnable', 1);
        }
        $this->DBWorker->updateDB($table, $whereSqlUpdate['sql'], $setSqlUpdate['sql'], $valuesEnable);
      }
    }
    return $whereSqlUpdate['values'];
  }
  private function writeInDBFromUP($data, $where)
  {
    /** @var string $method This is the name of the method, e.g. 'prepareDealer' or 'preparePoint' */

    $table = 'gi_new_' . $where . 's';
    $columnWhere = 'id_' . $where;
    if (!empty($data)) {
      $newData = $this->switchPrepareData($where, $data);

      $returnIds = [];
      if (!empty($newData['new'])) {
        $idUP = $this->DataUtilities->getFromArrByKeyU($newData['new'], 'PKId');
        $this->DBWorker->insertDBU_2($table, $newData['new']);

        $returnIds['new'] = $idUP;
      }
      if (!empty($newData['update'])) {
        $idEnable = $this->executionUpdateState($newData['update'], $table, $columnWhere);
      }
      return $newData;
    }
  }
  private function writeInDBFromUP_NEW($data, $where)
  {
    $table = 'gi_new_' . $where . 's';

    $columnWhere = 'id_' . $where;
    if (!empty($data)) {
      $sortData = [];
      $sortData['new'] = [];
      $sortData['update'] = [];
      $newData = $this->switchPrepareData($where, $data);
      // $this->CatcherBugs->convPrintLog($newData, 'writeInDBFromUP_NEW', '$newData', 1);
      if (!empty($newData)) {
        foreach ($newData as $row) {
          $checkExist = $this->DBWorker->selectVarSimple(ucfirst($where) . 's', $columnWhere, $row['PKId'], $columnWhere);
          if (empty($checkExist)) {
            $this->DBWorker->insertDBU_2($table, $row);
            $sortData['new'][] = $row;
          } else {
            $idEnable = $this->executionUpdateState([$row], $table, $columnWhere);
            $sortData['update'][] = $row;
          }
        }
      }
      return $sortData;
    }
  }
  private function checkStatusOrder($value)
  {
    $status = '';
    switch ($value) {
      case 1:
        $status = 'Обработка';
        break;
      case 2:
        $status = 'Проформа';
        break;
      case 3:
        $status = 'Подтверждён';
        break;
      case 4:
        $status = 'Производство';
        break;
      case 5:
        $status = 'Упакован';
        break;
      case 6:
        $status = 'Отгружен частично';
        break;
      case 7:
        $status = 'Отгружен';
        break;
    }
    return $status;
  }
  private function idWhereLog($whereLog)
  {
    $idWhere = 0;
    switch ($whereLog) {
      case 'logIdOrder':
        $idWhere = 1;
        break;
      case 'logIdPoint':
        $idWhere = 2;
        break;
      case 'logIdDealer':
        $idWhere = 3;
        break;
      case 'logIdInvoice':
        $idWhere = 4;
        break;
      case 'logIdFirm':
        $idWhere = 5;
        break;
    }
    return $idWhere;
  }
  public function writeLog($logName = 'logIdOrder', $newLogId)
  {
    $newLogId = !empty($newLogId) ? $newLogId : 1;
    $idWhereLog = $this->idWhereLog($logName);
    $currentTime = $this->SimpleUtilities->currentTime();
    $keys = ['lasttime_request', 'last_log_id'];
    $values = [$currentTime, $newLogId];
    $set = $this->DBUtilities->preparenSingleOperSepar($keys, $values, ' = ', ', ');
    $where = $this->DBUtilities->preparenSingleOperSepar('id', $idWhereLog, ' = ', '');
    $valuesUpdate = array_merge(array_values($set['values']), array_values($where['values']));
    $this->DBWorker->updateDB('gi_new_log_sinc_UP', $where['sql'], $set['sql'], $valuesUpdate);
    $this->$logName = $newLogId;
  }
  public function requestUpdateFromUP($where, $logIdRequest = 0)
  {
    $GetExt = $this->switchGetExt($where);
    $logName = 'logId' . ucfirst($where);
    $logId = $this->$logName < $logIdRequest + 1 ? $this->$logName : 0;
    $this->initialisationUP();
    if ($this->UPDB) {
      $statement = $this->UPDB->prepare("exec $GetExt @logId=:logId");

      if ($statement) {
        $statement->bindValue(':logId', $logId);
        $statement->execute();

        $data = $this->fetchUP($statement);
        $this->closeConnection();
        $sortData = $this->writeInDBFromUP($data, $where);
        if (!empty($data[0]) && !empty($data[0]['logId'])) {
          $this->writeLog($logName, $data[0]['logId']);
        } else if (!empty($data[0]) && !isset($data[0]['logId'])) {
          $this->writeLog($logName, 1);
        }
        return $sortData;
      } else {
        error_log('requestUpdatePointUP else ($statement = $this->UPDB->prepare("exec GetOrdersExt5 @Id=:Id"))');
      }
    } else {
      error_log('requestUpdatePointUP else ($this->UPDB)');
    }
  }
  public function requestUpdateOrdersUP($logId = null)
  {
    $this->initialisationUP();
    // $this->CatcherBugs->convPrintLog('orders', 'requestUpdateOrdersUP', '$where', 1);
    if ($this->UPDB) {
      $logIdRequest = $this->logIdOrder < $logId + 1 ? $this->logIdOrder : 0;
      $statement = $logIdRequest ?
        $this->UPDB->prepare("exec GetOrdersExt4 @logId=:logId") :
        $this->UPDB->prepare("exec GetOrdersExt4");
      // $statement = $logId ?
      //   $this->UPDB->prepare("exec GetOrdersExt5 @logId=:logId") :
      //   $this->UPDB->prepare("exec GetOrdersExt5");
      if ($statement) {
        if ($logIdRequest) {
          $statement->bindValue(':logId', $logIdRequest);
        }
        $statement->execute();
        $data = $this->fetchUP($statement);
        $this->closeConnection();
        $orders = $this->prepareOrders($data);
        $this->logIdOrder = $logId;
        return $orders;
      } else {
        error_log('requireOrderFromWWUPNEW else ($statement = $this->UPdb->prepare("exec GetOrdersExt5 @Id=:Id"))');
      }
    } else {
      error_log('requireOrderFromUP_2 else ($this->UPdb)');
    }
  }
  public function requestUpdateFromUP_NEW($where, $logIdRequest = 0)
  {
    $this->initialisationUP();
    // $this->CatcherBugs->convPrintLog($where, 'requestUpdateFromUP_NEW', '$where', 1);
    if ($this->UPDB) {
      $GetExt = $this->switchGetExt($where);
      $logName = 'logId' . ucfirst($where);
      $logId = $this->$logName < $logIdRequest + 1 ? $this->$logName : 0;
      $statement = $logIdRequest ?
        $this->UPDB->prepare("exec $GetExt @logId=:logId") :
        $this->UPDB->prepare("exec $GetExt");
      if ($statement) {
        if ($logIdRequest) {
          $statement->bindValue(':logId', $logId);
        }
        $statement->execute();
        $data = $this->fetchUP($statement);
        $this->closeConnection();
        $sortData = [];
        // $this->CatcherBugs->convPrintLog($where, 'requestUpdateFromUP_NEW', '$where', 1);
        // $this->CatcherBugs->convPrintLog($logIdRequest, 'requestUpdateFromUP_NEW', '$logIdRequest', 1);
        // $this->CatcherBugs->convPrintLog($data, 'requestUpdateFromUP_NEW', '$data', 1);
        if (empty($logIdRequest)) {
          $sortData = $this->firstInicializationUP($where, $data);
          $this->writeLog($logName, 1);
        } else {
          $sortData = $this->writeInDBFromUP_NEW($data, $where);
          if (!empty($data[0]['logId'])) {
            $this->writeLog($logName, $data[0]['logId']);
          }
        }
        return $sortData;
      }
    }
  }
  public function getInvoiceOrdersFromUP($InvoiceId)
  {
    $this->initialisationUP();
    // $this->CatcherBugs->convPrintLog('invoice', 'getInvoiceOrdersFromUP', '$where', 1);
    if ($this->UPDB) {
      $InvoiceId = intval($InvoiceId);
      $statement = $this->UPDB->prepare("exec GetInvoiceOrdersExt @InvoiceId=:InvoiceId");
      $statement->bindValue(':InvoiceId', $InvoiceId);
      $statement->execute();
      $data = $this->fetchUP($statement);
      $data = $this->prepareInvoiceOrders($data);
      $this->closeConnection();
      return $data;
    }
  }
  private function switchGetExt($where)
  {
    $getExt = '';
    switch ($where) {
      case 'order':
        // $getExt = 'GetOrdersExt4';
        $getExt = 'GetOrdersExt5';
        break;
      case 'point':
        $getExt = 'GetPointsExt';
        break;
      case 'dealer':
        $getExt = 'GetDealersExt';
        break;
      case 'firm':
        $getExt = 'GetFirmsExt';
        break;
      case 'invoice':
        $getExt = 'GetInvoicesExt';
        break;
    }
    return $getExt;
  }
  private function switchPrepareData($where, $data)
  {
    $preparedData = [];
    switch ($where) {
      case 'invoice':
        $preparedData = $this->prepareInvoice_2($data);
        break;
      case 'order':
        $preparedData = $this->prepareOrders($data);
        break;
      case 'point':
        $preparedData = $this->preparePoint($data);
        break;
      case 'dealer':
        $preparedData = $this->prepareDealer($data);
        break;
      case 'firm':
        $preparedData = $this->prepareFirm($data);
        break;
    }
    return $preparedData;
  }
  public function firstInicializationUP($where, $data)
  {
    $whereTable = ucfirst($where) . 's';
    $table = $this->DBWorker->getPathTable($whereTable);
    $this->DBWorker->clearTable($table);
    $columns = $this->DBWorker->showColumnDB($table);
    $preparedData = $this->switchPrepareData($where, $data);
    $indexedData = $this->DataUtilities->convertToIndexArr($preparedData);
    $prepareData = $this->DBUtilities->prepareInsertTrust($indexedData, 100);
    $this->DBWorker->insertMultipleTrustDB($table, $columns, $prepareData);
    return $preparedData;
  }
  private function prepareInvoice_2($data)
  {
    $data = !isset($data[0]) ? [$data] : $data;
    $preparedData = [];
    foreach ($data as $row) {
      $preparedData[] = [
        'PKId' => $this->checkForNumeric($row['PKId']),
        'InvoiceDate' => $this->checkDateUP($row['InvoiceDate']),
        'FirmId' => $this->checkForNumeric($row['FirmId']),
        'VAT' => $this->checkForNumeric($row['VAT']),
        'VATIncluded' => $this->checkForNumeric($row['VATIncluded']),
        'InvoiceChange' => $this->checkForNumeric($row['InvoiceChange']),
        'InvoiceSum' => $this->checkForNumeric($row['InvoiceSum']),
        'PaidSum' => $this->checkForNumeric($row['PaidSum']),
      ];
    }
    return $preparedData;
  }

  public function getLogIdDealer()
  {
      return $this->logIdDealer;
  }
  public function getLogIdPoint()
  {
    return $this->logIdPoint;
  }
  public function getLogIdOrder()
  {
      return $this->logIdOrder;
  }
  public function getLogIdInvoice()
  {
      return $this->logIdInvoice;
  }
  public function getLogIdFirm()
  {
      return $this->logIdFirm;
  }

  public function closeConnection()
  {
    if ($this->UPDB) {
      $this->UPDB = null;
    }
  }
}
