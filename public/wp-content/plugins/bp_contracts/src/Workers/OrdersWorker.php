<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Core\Container;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\UPWorker;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
class OrdersWorker
{
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
  private $dbUtilities;
  /** @var UserUtilities */
  private $userUtilities;
  private $upWorker;
  public function __construct($Container)
  {
    $this->container = $Container;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->upWorker = $this->container->get('UPWorker');
    $this->userUtilities = $this->container->get('UserUtilities');
  }
  public function getOrders($points, $filters)
  {
    $perPage = $filters['perPage'] ? $filters['perPage'] : 20;
    $currentPage = $filters['currentPage'] ? $filters['currentPage'] : 1;
    $offset = $perPage * ($currentPage - 1);
    // Строим фильтры
    $whereSql = $this->prepareQueryForDB($points, $filters);
    // Формируем основной запрос для каждой точки
    $unionQueries = $this->selectOrdersDB($whereSql);
    // Объединяем все запросы через UNION ALL
    $sql = $unionQueries;
    // Добавляем ORDER BY, LIMIT и OFFSET для пагинации
    $sql .= " ORDER BY receiption_date DESC LIMIT $perPage OFFSET $offset";
    // Выполняем запрос
    // $this->catcherBugs->convPrintLog($sql, 'getOrders', '$sql');
    $sqlOrders = $this->dbWorker->selectSQL($sql);
    $outputTime = 60;
    $allOrders = $this->dataUtilities->prepareOrdersForUserU($sqlOrders, $outputTime);
    // Подсчет количества страниц для пагинации
    $countOrders = isset($countOrders) ? $countOrders : $this->countAllOrders($points, $filters);
    $countPageOrders = ceil($countOrders / $perPage);
    $allOrders[] = ['countPage' => $countPageOrders];

    return $allOrders;
  }
  protected function countAllOrders($points, $filters)
  {
    $totalCount = 0;
    $whereSql = $this->prepareQueryForDB($points, $filters);
    $unionQueries = $this->selectOrdersCountDB($whereSql);
    // Если есть хотя бы один запрос для объединения
    if (!empty($unionQueries)) {
      // Объединяем все запросы через UNION ALL
      $sql = "SELECT SUM(total) as total_count FROM (" . implode(' UNION ALL ', $unionQueries) . ") AS combined_totals";
      // Выполняем запрос
      $totalCount = $this->dbWorker->selectSQL($sql, true);
    }
    return $totalCount;
  }
  protected function prepareQueryForDB($points, $filters)
  {
    $whereConditions = [];
    $whereConditions = $this->conditionsByDefault($whereConditions);
    $whereConditions = $this->prepareConditionsPoint($points, $whereConditions);
    $whereConditions = $this->prepareConditionSearch($whereConditions, $filters['whatSearch'], $filters['searchQueryOrders']);
    $whereConditions = $this->prepareConditionDate($whereConditions, $filters['startDateOrders'], $filters['endDateOrders']);
    $whereConditions = $this->prepareConditionFilter($whereConditions, $filters['filterOrders']);
    $whereSql = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';
    return $whereSql;
  }
  protected function selectOrdersCountDB($whereSql)
  {
    $unionQueries = [];
    $path = $this->dbWorker->getPathTable('Order');
    if ($this->dbWorker->checkExistanceTable($path)) {
      // Если таблица существует, добавляем запрос для подсчёта строк
      $unionQueries[] = "SELECT COUNT(*) AS total FROM `$path`" . $whereSql;
    }
    return $unionQueries;
  }
  protected function selectOrdersDB($whereSql)
  {
    $path = $this->dbWorker->getPathTable('Order');
    if ($this->dbWorker->checkExistanceTable($path)) {
      // Подготовка SQL-запроса для каждой точки с применением фильтров
      $unionQueries = "SELECT * FROM `$path`" . $whereSql;
    }
    return $unionQueries;
  }
  protected function prepareConditionsPoint($points, $whereConditions)
  {
    $points = is_array($points) ? $points : [$points];
    if (is_array($points)) {
      $conditions = [];
      foreach ($points as $point) {
        $conditions[] = "point_id = '" . esc_sql($point) . "'";
      }
      $whereConditions[] = count($points) > 1
        ? '(' . implode(' OR ', $conditions) . ')'
        : $conditions[0];
    }
    return $whereConditions;
  }
  protected function prepareConditionSearch($whereConditions, $whatSearch, $searchQueryOrders)
  {
    if (!empty($searchQueryOrders)) {
      $searchConditions = [];
      // Добавляем условия поиска по каждому полю
      $fields = [];
      if ($whatSearch === 'invoices') {
        $fields = ['id_invoice'];
      } else {
        $fields = ['contract_sn', 'point_name', 'order_name', 'status_order', 'client_name', 'id_invoice'];
      }
      foreach ($fields as $field) {
        $searchConditions[] = "$field LIKE '%" . esc_sql($searchQueryOrders) . "%'";
      }
      // Объединяем условия через OR и добавляем в whereConditions
      $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
    }
    return $whereConditions;
  }
  protected function prepareConditionDate($whereConditions, $startDateOrders, $endDateOrders)
  {
    if (!empty($startDateOrders) && !empty($endDateOrders)) {
      // Преобразуем timestamp в формат, который поддерживает MySQL (YYYY-MM-DD HH:MM:SS)
      $startDate = date('Y-m-d H:i:s', $startDateOrders);
      $endDate = date('Y-m-d H:i:s', $endDateOrders);
      // Добавляем фильтр по диапазону дат
      $whereConditions[] = "shipping_date BETWEEN '" . esc_sql($startDate) . "' AND '" . esc_sql($endDate) . "'";
    }
    // $this->catcherBugs->convPrintLog($whereConditions, 'prepareConditionDate', '$whereConditions');
    return $whereConditions;
  }
  protected function prepareConditionFilter($whereConditions, $filterOrders)
  {
    if (!empty($filterOrders)) {
      $conditions = [];
      foreach ($filterOrders as $value) {
        $conditions[] = "status_order = '" . esc_sql($value) . "'";
      }
      // Если фильтров больше одного, объединяем их с OR, иначе просто добавляем один фильтр
      $whereConditions[] = count($filterOrders) > 1
        ? '(' . implode(' OR ', $conditions) . ')'
        : $conditions[0];
    }
    return $whereConditions;
  }
  protected function conditionsByDefault($whereConditions)
  {
    $whereConditions[] = "
    order_name NOT LIKE BINARY '%б1%' 
    AND order_name NOT LIKE BINARY '%б2%' 
    AND order_name NOT LIKE BINARY '%б3%' 
    AND order_name NOT LIKE BINARY '%б4%' 
    AND order_name NOT LIKE BINARY '%б5%' 
    AND order_name NOT LIKE BINARY '%б6%' 
    AND order_name NOT LIKE BINARY '%б7%' 
    AND order_name NOT LIKE BINARY '%б8%' 
    AND order_name NOT LIKE BINARY '%б9%'";
    return $whereConditions;
  }
  public function getOrdersReport($orders)
  {
    $whereSql = $this->prepareOrderReportQuery($orders, 'id');
    $unionQueries = $this->selectOrdersDB($whereSql);
    // Объединяем все запросы через UNION ALL
    $sql = $unionQueries;
    $sql .= " ORDER BY receiption_date DESC";
    $sqlOrders = $this->dbWorker->selectSQL($sql);
    return $sqlOrders;
  }
  protected function prepareOrderReportQuery($array, $column)
  {
    if (!empty($array)) {
      $whereSql = " WHERE `$column` IN ('" . implode("', '", $array) . "')";
      return $whereSql;
    }
  }
  public function prepareOrdersForPrint($sqlOrders)
  {
    $preparedOrders = [];
    $i = 0;
    foreach ($sqlOrders as $order) {
      $preparedOrders[$i] = [
        'Номер заказа' => $order->order_name,
        'Номер клиента' => $order->client_name,
        'Дата приема' => $order->receiption_date,
        'Дата проформы' => $order->proforma_date,
        'Дата подтверждения' => $order->confirmation_date,
        'Дата оплаты' => '-',
        'Дата выпуска' => convert_timestamp_index($order->required_date),
        'Дата отгрузки' => convert_timestamp_index($order->shipping_date),
        'Состояние' => $order->status_order,
        'Номер отгрузки' => $order->shipment_id,
        'Точка' => $order->point_name,
        'Брутто' => $order->brutto,
        'Нетто' => $order->netto,
        'Объем' => $order->volume,
        'Пароль' => $order->shipment_password
      ];
      $preparedOrders[$i]['Дата выпуска'] = $this->checkExistDate($order->required_date);
      $preparedOrders[$i]['Дата отгрузки'] = $this->checkExistDate($order->shipping_date);
      $i++;
    }
    return $preparedOrders;
  }
  protected function checkExistDate($date)
  {
    $value = convert_timestamp_index($date) > 0 ? $date : '-';
    return $value;
  }
  public function getInfoOrdersUP($orderIDs)
  {
    $orderList = $this->upWorker->getOrderListFromUP($orderIDs);
    $ordersPackage = $this->prepareOrderList($orderList);
    return $ordersPackage;
  }
  protected function prepareOrderList($orderList)
  {
    $orders = [];
    $ordersFromDB = $this->getOrdersReport($orders);
    foreach ($ordersFromDB as $order) {
      $orderNumber = $order->order_name;
      $id = $order->id;
      $iPackage = 0;
      $pointName = $order->point_name;
      $iUnPackage = 0;
      foreach ($orderList as $item) {
        if (!empty($item)) {
          if ($id == $item['OrderId']) {
            if ($item["SeatNumber"] !== null) {
              $iPackage++;
              $orders[$orderNumber]['Упакованное'][$iPackage] = $this->upWorker->packageOrderList($item, $iPackage);
              $orders[$orderNumber]['Точка'] = $pointName;
            } else {
              $iUnPackage++;
              $orders[$orderNumber]['Неупакованное'][$iUnPackage] = $this->upWorker->packageOrderList($item, $iUnPackage);
              $orders[$orderNumber]['Точка'] = $pointName;
            }
          }
        }
      }
    }
    return $orders;
  }
}