<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Core\Container;
use Exception;
class DBWorker
{
  // use DependencyInjections;
  use Utilit;
  private $tableOrder;
  private $tablePoints;
  private $tableDealers;
  private $tableParticipantsDialog;
  private $tableUsers;
  private $tableDialogues;
  private $tableLogUnreadedMessages;
  private $tableContract;
  private $tableLogSincUP;
  private $tableInvoices;
  private $tableFirms;
  private $tableDesignerArchitect;
  private $tableKitchen;
  private $tableFacadesList;
  private $tableFacadesMaterial;
  private $tableHandle;
  private $tableTabletop;
  private $tableKitchenVisualisation;
  /** @var Container */
  private $Container;
  /** @var DBUtilities */
  protected $DBUtilities;
  /** @var SimpleUtilities */
  protected $SimpleUtilities;
  /** @var CatcherBugs */
  protected $CatcherBugs;
  public function __construct($ServicesContainer)
  {
    global $wpdb;

    $this->Container = &$ServicesContainer;
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
    $this->DBUtilities = $this->Container->get('DBUtilities');
    $this->wpdb = &$wpdb;
    $this->tableOrder = 'gi_new_orders';
    $this->tableParticipantsDialog = 'gi_new_participants_dialog';
    $this->tableUsers = 'gi_new_users';
    $this->tableDealers = 'gi_new_dealers';
    $this->tableLogUnreadedMessages = 'gi_new_log_unreaded_messages';
    $this->tablePoints = 'gi_new_points';
    $this->tableDialogues = 'gi_new_dialogues';
    $this->tableContract = 'gi_new_contract';
    $this->tableLogSincUP = 'gi_new_log_sinc_UP';
    $this->tableInvoices = 'gi_new_invoices';
    $this->tableFirms = 'gi_new_firms';
    $this->tableDesignerArchitect = 'gi_designer_architect';
    $this->tableKitchen = 'gi_kitchen';
    $this->tableFacadesList = 'gi_facades_list';
    $this->tableFacadesMaterial = 'gi_facade_material';
    $this->tableHandle = 'gi_handle';
    $this->tableTabletop = 'gi_tabletop';
    $this->tableKitchenVisualisation = 'gi_kitchen_visualisation';
  }
  public function getRawSQL($sql, $params = [])
  {
    $result = $this->wpdb->get_results(
      $this->wpdb->prepare($sql, $params)
    );
    $result = $this->finalPrepareResult($result, '*');
    return $result;
  }
  public function selectSQL($sql, $var = false)
  {
    $result = $var ? $this->wpdb->get_var($sql) : $this->wpdb->get_results($sql);
    return $result;
  }
  // NEW Version SCRIPTS
  public function selectUni($table, $conditions, $what = '*')
  {
    $table = $this->getPathTable($table);
    $newWhat = $this->DBUtilities->selectColumns($what);
    $where = $conditions['sql'];
    $values = $conditions['values'];
    $sql = "SELECT $newWhat FROM `$table` WHERE $where";
    $prepare = $this->wpdb->prepare($sql, $values);
    $result = $this->wpdb->get_results($prepare);
    // if (is_array($what)) {
    //   $this->CatcherBugs->convPrintLog($what, 'getContractsDB', '$what');
    //   $this->CatcherBugs->convPrintLog($sql, 'getContractsDB', '$sql');
    //   $this->CatcherBugs->convPrintLog($result, 'getContractsDB', '$result');
    // }
    $finalResult = $this->finalPrepareResult($result, $what);
    return $finalResult;
  }
  public function selectUni_2($table, $conditions, $what = '*')
  {
    $table = $this->getPathTable($table);
    $newWhat = $this->DBUtilities->selectColumns($what);
    $where = isset($conditions['sql']) ? 'WHERE ' . $conditions['sql'] : '';
    $values = isset($conditions['values']) ? $conditions['values'] : '';
    $OrderBy = isset($conditions['OrderBy']) ? 'ORDER BY ' . $conditions['OrderBy'] : '';

    $sql = "SELECT $newWhat FROM `$table` $where $OrderBy";
    $prepare = $this->wpdb->prepare($sql, $values);
    $result = $this->wpdb->get_results($prepare);
    // if (is_array($what)) {
    //   $this->CatcherBugs->convPrintLog($what, 'getContractsDB', '$what');
    //   $this->CatcherBugs->convPrintLog($sql, 'getContractsDB', '$sql');
    //   $this->CatcherBugs->convPrintLog($result, 'getContractsDB', '$result');
    // }
    $finalResult = $this->finalPrepareResult($result, $what);
    return $finalResult;
  }
  public function selectVarUni($table, $conditions, $what)
  {
    $table = $this->getPathTable($table);
    $where = $conditions['sql'];
    $values = $conditions['values'];
    $sql = "SELECT `$what` FROM `$table` WHERE $where";
    $prepare = $this->wpdb->prepare($sql, $values);
    $result = $this->wpdb->get_var($prepare);
    return $result;
  }
  public function selectSimple($table, $where = null, $value = null, $what = '*')
  {
    $table = $this->getPathTable($table);
    $what = $this->DBUtilities->selectColumns($what);
    $conditions = $where !== null && $value !== null
      ? $this->DBUtilities->preparenSingleOperSepar($where, $value, ' = ', '')
      : null;
    $where = $conditions !== null ? $conditions['sql'] : null;
    $whereSql = !empty($conditions['sql']) ? " WHERE $where" : "";
    $values = $conditions !== null ? $conditions['values'] : null;
    $sql = "SELECT $what FROM `$table`" . $whereSql;
    $prepare = $this->wpdb->prepare($sql, $values);
    $result = $this->wpdb->get_results($prepare);
    $finalResult = $this->finalPrepareResult($result, $what);
    return $finalResult;
  }

  public function selectVarSimple($table, $where = null, $value = null, $what)
  {
    $table = $this->getPathTable($table);
    $conditions = $this->DBUtilities->preparenSingleOperSepar($where, $value, ' = ', '');
    $where = $conditions !== null ? $conditions['sql'] : null;
    $values = $conditions !== null ? $conditions['values'] : null;
    $sql = "SELECT `$what` FROM `$table` WHERE $where";
    $prepare = $this->wpdb->prepare($sql, $values);
    $result = $this->wpdb->get_var($prepare);
    return $result;
  }
  public function selectDistinctColumn($table, $column, $what = '*')
  {
    $table = $this->getPathTable($table);
    $sql = "SELECT DISTINCT `$column` FROM `$table`";
    $result = $this->wpdb->get_results($sql);
    $result = $this->finalPrepareResult($result, $column);
    return $result;
  }
  public function selectCrossTable($table1, $table2, $column1, $column2, $orderbyColumn = null, $where = null, $value = null, $what = '*')
  {
    $table1 = $this->getPathTable($table1);
    $table2 = $this->getPathTable($table2);
    $column1 = $this->DBUtilities->selectColumns($column1);
    $column2 = $this->DBUtilities->selectColumns($column2);
    $search_term = '%' . $this->wpdb->esc_like($value) . '%';
    $sql = "SELECT DISTINCT t1.$column1
			 FROM `$table1` t1
			 JOIN `$table2` t2 ON t2.$column2 = t1.$column1
			 WHERE t1.$where LIKE %s
			 ORDER BY t2.$orderbyColumn ASC";
    // error_log($sql);
    $prepare = $this->wpdb->prepare($sql, $search_term);
    $result = $this->wpdb->get_results($prepare);
    // error_log(print_r($result, true));
    $result = $this->finalPrepareResult($result, $what);
    return $result;
    // $where = $this->DBUtilities->preparenSingleOperSepar($where, $value, ' = ', '');
    // $where = $where !== null ? $where['sql'] : null;
  }
  public function selectCrossTable_2($select, $from, $where, $orderBy, $limit, $offset)
  {

  }

  public function getMax($table, $where, $value, $what)
  {
    $conditions = $this->DBUtilities->preparenSingleOperSepar($where, $value, ' = ', '');
    $where = $conditions !== null ? $conditions['sql'] : null;
    $values = $conditions !== null ? $conditions['values'] : null;
    $sql = "SELECT MAX($what) FROM `$table` WHERE $where";
    $prepare = $this->wpdb->prepare($sql, $values);
    $result = $this->wpdb->get_var($prepare);
    $result = $result !== null ? (intval($result) + 1) : 1;
    return $result;
  }
  public function deleteDB($table, $where, $value)
  {
    $sql = "DELETE FROM `$table` WHERE `$where` = %s";
    $prepare = $this->wpdb->prepare($sql, $value);
    return $this->wpdb->query($prepare);
  }
  public function deleteComplexQueryDB($table, $where, $value)
  {
    $sql = "DELETE FROM `$table` WHERE $where";
    $prepare = $this->wpdb->prepare($sql, $value);
    return $this->wpdb->query($prepare);
  }
  public function deleteDB_1($table, $where, $value)
  {
    $table = $this->getPathTable($table);
    $sql = "DELETE FROM `$table` WHERE `$where` = %s";
    $prepare = $this->wpdb->prepare($sql, $value);
    return $this->wpdb->query($prepare);
  }
  public function updateDBU($table, $whereColumn, $whereValue, $setColumn, $setValue = '')
  {
    // Подготовка SQL для обновления
    $update = "UPDATE `$table` SET `$setColumn` = %s";
    $whereSql = $this->DBUtilities->prepareWhereForUpdate($whereValue, $whereColumn);
    // Финальный запрос
    $sql = $update . $whereSql;
    $queryParams = $this->DBUtilities->prepareQueryForUpdate($whereValue, $setValue);
    // Выполнение запроса с экранированием
    $preparedQuery = $this->wpdb->prepare($sql, $queryParams);
    $this->wpdb->query($preparedQuery);
  }
  public function insertDBU_2($table, $values)
  {
    if ($this->checkExistanceTable($table)) {
      // error_log(print_r($values, true));
      $columnsStr = $this->showColumnDBU($table);

      $prepared = $this->preparePlaceholdersMultiple($values);
      $placeholdersStr = $prepared['placeholders'];
      $valuesWriteTo = $prepared['values'];
      $sql = "INSERT INTO $table ($columnsStr) VALUES $placeholdersStr";
      $prepare = $this->wpdb->prepare($sql, ...$valuesWriteTo);
      $result = $this->wpdb->query($prepare);
      if (!$result) {
        error_log('//////////////////////////// Failed');
        error_log("SQL Error: " . $this->wpdb->last_error);
        // error_log("SQL Query: $sql");
        // error_log("Values: " . print_r($valuesWriteTo, true));
        error_log('//////////////////////////// Failed');
      } else {
        error_log('//////////////////////////// Success');
        error_log(print_r($prepare, true));
        error_log('//////////////////////////// Success');
      }
    } else {
      error_log('insertDBU ' + $table + 'doesnt exist');
    }
  }
  public function insertDBU($table, $values)
  {
    if ($this->checkExistanceTable($table)) {
      $columnsStr = $this->showColumnDBU($table);
      $placeholdersStr = $this->preparePlaceholder($values);
      $sql = "INSERT INTO $table ($columnsStr) VALUES ($placeholdersStr)";

      $result = $this->wpdb->query($this->wpdb->prepare($sql, ...$values));
    } else {
      error_log('insertDBU ' + $table + 'doesnt exist');
    }
  }
  public function lastId()
  {
    return $this->wpdb->insert_id;
  }
  public function insertMultipleDB($table, $columns, $batchData)
  {
    if ($this->checkExistanceTable($table)) {
      foreach ($batchData as $data) {
        $columnsStr = '`' . implode('`, `', $columns) . '`';
        $sqlInsert = "INSERT INTO `$table` ($columnsStr) VALUES " . $data['sql'];
        $preparedSql = $this->wpdb->prepare($sqlInsert, $data['values']);
        $this->wpdb->query("LOCK TABLES `$table` WRITE");
        $this->wpdb->query("START TRANSACTION");
        $result = $this->wpdb->query($preparedSql);
        if (!$result) {
          // $this->CatcherBugs->convPrintLog($this->wpdb->last_error, 'insertMultipleDB', '$this->wpdb->last_error', 1);
          // Откатываем изменения в случае ошибки
          $this->wpdb->query("ROLLBACK");
        } else {
          // Фиксируем изменения
          $this->wpdb->query("COMMIT");
        }
        // Разблокируем таблицу
        $this->wpdb->query("UNLOCK TABLES");
      }
    }
  }
  public function insertMultipleTrustDB($table, $columns, $batchData)
  {
    if ($this->checkExistanceTable($table)) {
      foreach ($batchData as $data) {
        $columnsStr = '`' . implode('`, `', $columns) . '`';
        $sqlInsert = "INSERT INTO `$table` ($columnsStr) VALUES " . $data;
        // Блокируем таблицу перед вставкой
        $this->wpdb->query("LOCK TABLES `$table` WRITE");
        // Начинаем транзакцию
        $this->wpdb->query("START TRANSACTION");
        $result = $this->wpdb->query($sqlInsert);
        if (!$result) {
          // $this->CatcherBugs->convPrintLog($this->wpdb->last_error, 'insertMultipleTrustDB', '$this->wpdb->last_error', 1);
          // Откатываем изменения в случае ошибки
          $this->wpdb->query("ROLLBACK");
        } else {
          // Фиксируем изменения
          $this->wpdb->query("COMMIT");
        }
        // Разблокируем таблицу
        $this->wpdb->query("UNLOCK TABLES");
      }
    } else {
      error_log('insertDBU ' . $table . ' doesn\'t exist');
    }
  }
  public function clearTable($table)
  {
    if (empty($table) || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
      return false; // Invalid table name
    }
    // Prepare the SQL query with a placeholder for the table name
    // $sql = $this->wpdb->prepare("DELETE FROM `%s`", $table);
    $sql = "DELETE FROM $table";
    $result = $this->wpdb->query($sql);
    // Check for errors
    if ($result === false) {
      // Handle the error (e.g., log it or throw an exception)
      error_log("Failed to clear table: " . $this->wpdb->last_error);
      // $this->CatcherBugs->convPrintLog($this->wpdb->last_error, 'clearTable', '$wpdb->last_error');
      return false;
    }
    return true; // Success
  }
  public function selectResultsFromDBU($table, $where = null, $value = null, $what = '*')
  {
    $columns = $this->selectColumns($what);
    $select = "SELECT $columns FROM `$table`";
    if ($where !== null && $value !== null) {
      $select .= $this->wpdb->prepare(" WHERE `$where` = %s", $value); // Используем prepare для безопасного подставления значений
    }
    $sqlResults = $this->wpdb->get_results($select);
    $results = [];
    if ($what !== '*') {
      foreach ($sqlResults as $result) {
        $results[] = $result->$what;
      }
    } else {
      $results = $sqlResults;
    }
    return !empty($results) ? $results : null; // Возвращаем null, если нет данных
  }
  public function selectResultsFromDBU_2($table, $where = null, $value = null, $what = '*')
  {
    $columns = $this->selectColumns($what);
    $select = "SELECT $columns FROM `$table`";
    if ($where !== null && !empty($value) && $value !== null) {
      if (is_array($value) && !is_array($where)) {
        $placeholders = implode(',', array_fill(0, count($value), '%s'));
        $select .= " WHERE `$where` IN ($placeholders)";
        $parameters = $value; // Все значения массива передаем как параметры
      } else if (is_array($value) && is_array($where)) {
        // Массив условий
        $conditions = [];
        $parameters = [];
        foreach ($where as $column) {
          if (isset($value[$column])) { // Проверяем наличие соответствия столбца и значения
            $conditions[] = "`$column` = %s";
            $parameters[] = $value[$column];
          }
        }
        if (!empty($conditions)) {
          $select .= " WHERE " . implode(' AND ', $conditions);
        }
      } else {
        // Standart
        $select .= " WHERE `$where` = %s";
        $parameters = [$value]; // Значение упаковывается в массив для универсальности
      }
      // Final prepare
      $select = $this->wpdb->prepare($select, $parameters);
    }
    $sqlResults = $this->wpdb->get_results($select);
    $results = $this->DBUtilities->convertDataFromDB($sqlResults);
    $results = $what !== '*' && !is_array($what) ? $this->DBUtilities->whatExist($results) : $results;
    $results = is_array($results) && count($results) === 1 ? $results[0] : $results;
    return !empty($results) ? $results : null; // Возвращаем null, если нет данных
  }
  private function finalPrepareResult($data, $what)
  {
    $result = $this->DBUtilities->convertDataFromDB($data);
    $result = $what !== '*' && !is_array($what) ? $this->DBUtilities->whatExist($result) : $result;
    $result = is_array($result) && count($result) === 1 ? $result[0] : $result;
    return !empty($result) ? $result : null;
  }
  // private function finalPrepareResultDistinct($data){

  // }
  public function updateDB($table, $whereSql, $setSql, $queryParams)
  {
    try {
      // Проверяем соответствие данных для запроса
      // error_log(print_r($whereSql, true));
      $sql = "UPDATE `$table` SET $setSql WHERE $whereSql";
      // Выполнение запроса
      $preparedQuery = $this->wpdb->prepare($sql, $queryParams);

      // error_log("SQL Query: " . print_r($preparedQuery, true));
      $result = $this->wpdb->query($preparedQuery);
      // Проверка успешности выполнения запроса
      if ($result === false && $table !== 'gi_new_invoices') {
        error_log("Ошибка выполнения запроса: " . $this->wpdb->last_error);
        // $this->CatcherBugs->convPrintLog($this->wpdb->last_error, 'updateDB', '$this->wpdb->last_error', 1);
      } else {
        // $affectedRows = $this->wpdb->rows_affected;
        // if ($affectedRows > 0) {
        //   error_log("Запрос выполнен успешно, затронуто строк: $affectedRows");
        // } else {
        //   error_log("Запрос выполнен успешно, но ни одна строка не была обновлена.");
        // }
        return $result;
      }
    } catch (Exception $e) {
      error_log("Ошибка выполнения запроса: " . $e->getMessage());
    }
  }

  public function selectVarFromDBU($table, $where, $value, $what = '*')
  {
    $columns = $this->selectColumns($what);
    $sql = $this->wpdb->get_var(
      $this->wpdb->prepare(
        "SELECT $columns FROM `$table` WHERE `$where` = %s",
        $value
      )
    );
    return $sql;
  }
  private function selectColumns($what)
  {
    $columns = $what === '*'
      ? $what
      : "`" . implode('`, `', explode(',', $what)) . "`"; // Без кавычек для выбора всех столбцов
    return $columns;
  }

  //Replace into DBUtilities
  private function showColumnDBU($table)
  {
    $sql = "SHOW COLUMNS FROM $table";
    $result = $this->wpdb->get_results($sql);
    $results = [];
    foreach ($result as $column) {
      if ($column->Extra !== 'auto_increment') { // Исключаем AUTO_INCREMENT
        $results[] = $column->Field;
      }
    }
    return implode(', ', $results);
  }
  public function showColumnDB($table)
  {
    $sql = "SHOW COLUMNS FROM $table";
    $result = $this->wpdb->get_results($sql);
    $results = [];
    foreach ($result as $column) {
      if ($column->Extra !== 'auto_increment') { // Исключаем AUTO_INCREMENT
        $results[] = $column->Field;
      }
    }
    return $results;
  }
  //Replace into DBUtilities
  private function preparePlaceholderValue($value)
  {
    // error_log(print_r($value, true));

    if (is_null($value)) {
      // NULL
      return 'NULL';
    } elseif (is_bool($value)) {
      // Булевые значения
      return '%d'; // Приведение true/false к 1/0
    } elseif (is_int($value)) {
      // Целые числа
      return '%d';
    } elseif (is_float($value)) {
      // Числа с плавающей точкой
      return '%f';
    } elseif (is_array($value)) {
      // Массивы нельзя напрямую вставить — конвертируем в JSON
      return '%s';
    } elseif (is_object($value)) {
      // Объекты также конвертируем в JSON
      return '%s';
    } elseif (strtotime($value) !== false) {
      // Дата или время
      return '%s';
    } elseif (is_string($value)) {
      // Обычная строка
      return '%s';
    } else {
      // На случай неизвестного типа
      error_log('Error: DBWorker->preparePlaceholderValue');
    }
  }
  //Reduce
  private function preparePlaceholder($values)
  {
    $values = is_array($values) ? $values : [$values];
    $placeholders = array_map(function ($value) {
      return $this->preparePlaceholderValue($value);
    }, $values);
    return implode(', ', $placeholders);
  }
  //Reduce
  private function preparePlaceholdersMultiple($values)
  {
    $values = is_array(reset($values)) ? $values : [$values]; // Приводим к массиву строк
    $placeholders = [];
    $flattenedValues = []; // Все значения для prepare

    foreach ($values as $row) {
      $rowPlaceholders = [];
      foreach ($row as $value) {
        $rowPlaceholders[] = $this->preparePlaceholderValue($value);
        $flattenedValues[] = is_null($value) ? null : $value; // Добавляем null для NULL-значений
      }
      $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
    }
    // error_log(print_r($placeholders, true));
    // error_log(print_r($flattenedValues, true));
    return [
      'placeholders' => implode(', ', $placeholders),
      'values' => $flattenedValues,
    ];
  }
  public function checkExistanceTable($pathTableDB)
  {
    $querySQL = $this->wpdb->prepare("SHOW TABLES LIKE %s", $this->wpdb->esc_like($pathTableDB));
    $tableExists = $this->wpdb->get_var($querySQL);
    return $tableExists;
  }

  //Replace into DBUtilities
  // protected function whatExist($data)
  // {
  //   if (!empty($data) && is_array($data)) {
  //     if (count($data) === 1 && count($data[0]) === 1) {
  //       $singleKey = key($data[0]);
  //       $result = $data[0][$singleKey];
  //     } elseif (count($data) > 1 && count($data[0]) === 1) {
  //       $temporary = [];
  //       foreach ($data as $item) {
  //         if (is_array($item) && count($item) === 1) {
  //           $singleKey = key($item);
  //           $temporary[] = $item[$singleKey];
  //         }
  //       }
  //       $result = $temporary;
  //     }
  //     return $result;
  //   }
  // }

  //Old-fashion style ALL will reduce
  public function determinationRank($idUser)
  {
    $rank = $this->wpdb->get_var("SELECT user_rank FROM gi_new_users WHERE id_user = '$idUser'");
    return intval($rank);
  }
  //Reduce
  // public function getStatusActivityDB($idUser)
  // {
  //   $statusActivity = $this->wpdb->get_var("SELECT status_activity FROM gi_new_users WHERE id_user = '$idUser'");
  //   return $statusActivity;
  // }
  public function updateOrders($orders, $ordersIds)
  {
    if (!empty($orders)) {
      // $this->CatcherBugs->convPrintLog($orders, 'updateOrders SHOW BEFORE foreach', '$order[PKId]');
      // $this->CatcherBugs->convPrintLog($ordersIds, 'updateOrders SHOW BEFORE foreach', '$ordersIds');
      foreach ($orders as $order) {
        $ordersIds = $this->SimpleUtilities->toArray($ordersIds);
        if (in_array($order['PKId'], $ordersIds)) {
          if ($order['orderName'] === '-') {
            $this->deleteDB($this->tableOrder, 'id', $order['PKId']);
            // $this->CatcherBugs->convPrintLog($order['PKId'], 'updateOrders DELETE', '$order[PKId]');
            // $this->CatcherBugs->analizeChain('updateOrders deleteOrders DELETE', '$order', $order);
          } else {
            $this->updateOrderDB_1($order);
            // $this->CatcherBugs->convPrintLog($order, 'updateOrderDB_1 UPDATE', '$order[PKId]');
          }
        } else {
          $this->insertOrderDB_1($order);
          // $this->CatcherBugs->convPrintLog($order, 'insertOrderDB INSERT', '$order');
          // $this->CatcherBugs->analizeChain('updateOrders insertOrderDB INSERT', '$order', $order);
        }
      }
    }
  }
  private function updateOrderDB_1($order)
  {
    $sqlUpdate = "UPDATE `$this->tableOrder`
      SET order_name = %s, client_name = %s, 
        receiption_date = %s, proforma_date = %s, confirmation_date = %s, invoice_date = %s, 
        required_date = %s, shipping_date = %s, 
        status_order = %s, shipment_id = %s, point_name = %s, point_id = %d, brutto = %s, 
        netto = %s, volume = %s, shipment_password = %s, 
        contract_sn = %s, id_invoice = %d
      WHERE id = %d";
    $orderData = $this->processOrderData_2($order);
    $orderData[] = $order['PKId']; // PKId добавляем в конце для WHERE
    if ($order['contractSN'] == 1) {
      // $this->CatcherBugs->convPrintLog($order, 'updateOrderDB_1', '$orderData', 3);
      // $this->CatcherBugs->convPrintLog($orderData, 'updateOrderDB_1', '$orderData', 3);
      // $this->CatcherBugs->convPrintLog($sqlUpdate, 'updateOrderDB_1', '$sqlUpdate', 3);
    }
    $this->executeQuery($sqlUpdate, $orderData);
  }
  private function processOrderData_2($order)
  {
    $data = [
      $order['orderName'], // 
      $order['clientName'], //  
      $order['receptionDate'], // 
      $order['proformaDate'], // 
      $order['confirmationDate'], // 
      $order['invoiceDate'], // 
      $order['requiredDate'], // 
      $order['shipmentDate'], // 
      $order['status'], // 
      $order['shipmentId'], // 
      $order['pointName'], // 
      $order['pointId'],
      $order['brutto'], // 
      $order['netto'], // 
      $order['volume'], // 
      $order['shipmentPassword'], // 
      $order['contractSN'], // 
      $order['InvoiceId'] // id_invoice
    ];
    return $data;
  }
  private function executeQuery($query, $data)
  {
    $result = $this->wpdb->query($this->wpdb->prepare($query, ...$data));
    if ($result === false) {
      error_log("Ошибка выполнения запроса: " . $this->wpdb->last_error);
    }
    return $result;
  }
  private function insertOrderDB_1($order)
  {
    $PKId = $order['PKId'];
    $orderName = $order['orderName'];
    $clientName = $order['clientName'];
    $receptionDate = $order['receptionDate'];
    $proformaDate = $order['proformaDate'];
    $confirmationDate = $order['confirmationDate'];
    $invoiceDate = $order['invoiceDate'];
    $requiredDate = $order['requiredDate'];
    $shipmentDate = $order['shipmentDate'];
    $status = $order['status'];
    $shipmentId = $order['shipmentId'];
    $pointName = $order['pointName'];
    $pointId = $order['pointId'];
    $brutto = $order['brutto'];
    $netto = $order['netto'];
    $volume = $order['volume'];
    $shipmentPassword = $order['shipmentPassword'];
    $contractSN = $order['contractSN'];
    $invoiceId = $order['InvoiceId'];

    $sql = "INSERT INTO `$this->tableOrder` 
    (id, order_name, client_name, 
    receiption_date, proforma_date, confirmation_date, invoice_date, 
    required_date, shipping_date, 
    status_order, shipment_id, point_name, point_id, brutto, 
    netto, volume, shipment_password,
    contract_sn,	id_invoice) 
    VALUES (
    %d, %s, %s, 
    %s, %s, %s, %s, 
    %s, %s, 
    %s, %s, %s, %d, %s, 
    %s, %s, %s, 
    %s, %d)";

    $prepared_sql = $this->wpdb->prepare(
      $sql,
      $PKId,
      $orderName,
      $clientName,
      $receptionDate,
      $proformaDate,
      $confirmationDate,
      $invoiceDate,
      $requiredDate,
      $shipmentDate,
      $status,
      $shipmentId,
      $pointName,
      $pointId,
      $brutto,
      $netto,
      $volume,
      $shipmentPassword,
      $contractSN,
      $invoiceId
    );
    // $this->CatcherBugs->convPrintLog($prepared_sql, 'requestUpdateOrdersCOR', '$prepared_sql');
    $result = $this->wpdb->query($prepared_sql);
    if ($result === false) {
      // error_log("Ошибка выполнения запроса: " . $this->wpdb->last_error);
      // $this->CatcherBugs->convPrintLog($prepared_sql, 'insertOrderDB_1', '$prepared_sql');
    }
    return $result;
  }
  public function getPathTable($string)
  {
    $property = 'table' . $string;
    if (property_exists($this, $property)) {
      return $this->$property;
    } else {
      return $string;
    }
  }
  public function updateDateReadedMessagesDB($newMessage)
  {
    $this->wpdb->query("UPDATE `$newMessage->pathTableDB` SET date_readed='$newMessage->dateSend' WHERE id_dialog = '$newMessage->idDialog' AND id_message <= '$newMessage->idMessage' AND date_readed <= 0 AND id_author != '$newMessage->idAuthor'");
  }

  public function writeLogLastMessage($idParcipiants, $newMessage)
  {
    foreach ($idParcipiants as $parcipiantId) {
      if (intval($parcipiantId) !== intval($newMessage->idAuthor)) {
        $this->wpdb->query("INSERT INTO gi_new_log_unreaded_messages 
          (id_dialog, id_message, id_user) 
          VALUES ('$newMessage->idDialog', '$newMessage->idMessage', '$parcipiantId')");
        $this->deletePrevMessageLog($newMessage);
      }
    }
    $condition = $this->DBUtilities->prepareEqualAndEqual($newMessage->idDialog, $newMessage->idMessage, 'id_dialog', 'id_message');
    $didntRead = $this->selectUni('LogUnreadedMessages', $condition, 'id_user');
    return $didntRead;
  }
  public function deletePrevMessageLog($newMessage)
  {
    $this->wpdb->query("DELETE FROM `gi_new_log_unreaded_messages` WHERE id_dialog = '$newMessage->idDialog' AND id_message <= '$newMessage->idMessage' AND id_user = '$newMessage->idAuthor'");
  }

  public function selectContracts($userId, $pontsID, $filters)
  {
    $perPage = $filters['perPage'];
    $currentPage = $filters['currentPage'];
    $offset = ($currentPage - 1) * $perPage;
    $select = "SELECT c.*, 
      p.name_point, 
      p.id_point, 
      GROUP_CONCAT(DISTINCT o.order_name) as orders";
    $from = $this->prepareFromForContracts($filters, $userId);
    $where = $this->prepareWhereForContracts($filters, $pontsID);
    $groupBy = " GROUP BY c.sn";
    $orderBy = " ORDER BY c.date_last_activity DESC";
    $limit = " LIMIT $perPage OFFSET $offset";
    $sql = $select . $from . $where . $groupBy . $orderBy . $limit;
    $result = $this->wpdb->get_results($sql);
    return $result;
  }
  public function selectCountContracts($userId, $pontsID, $filters)
  {
    $select = "SELECT COUNT(DISTINCT c.sn)";
    $from = $from = $this->prepareFromForContracts($filters, $userId);
    $where = $this->prepareWhereForContracts($filters, $pontsID);
    $sql = $select . $from . $where;
    $result = $this->wpdb->get_var($sql);
    return $result;
  }
  public function selectUnreadedDialogues($userId)
  {
    $select = "SELECT d.id_dialog, d.sn, d.type_dialog";
    $from = " FROM `gi_new_dialogues` d
      INNER JOIN `gi_new_participants_dialog` part ON d.id_dialog = part.id_dialog AND part.id_participant = $userId
      INNER JOIN `gi_new_log_unreaded_messages` log ON d.id_dialog = log.id_dialog AND log.id_user = $userId";
    $groupBy = " GROUP BY d.id_dialog";
    $sql = $select . $from . $groupBy;
    $result = $this->wpdb->get_results($sql);
    return $result;
  }
  public function selectInvoices($filters) {
    $perPage = $filters['perPage'];
    $currentPage = $filters['currentPage'];
    $idFirm = $filters['idFirm'];
    $searchQuery = $filters['searchQuery'];
    $offset = ($currentPage - 1) * $perPage;
    $select = "SELECT i.*,
      f.firm_name,
      GROUP_CONCAT(DISTINCT o.order_name) as orders,
      c.name_contract,
      c.sn,
      f.curr_id";
    $from = $this->prepareFromForInvoices($filters);
    $where = $this->prepareWhereForInvoices($searchQuery, $idFirm);
    $groupBy = " GROUP BY i.id_invoice";
    $orderBy = " ORDER BY i.invoice_date DESC, i.id_invoice DESC";
    $limit = " LIMIT $perPage OFFSET $offset";
    $sql = $select . $from . $where . $groupBy . $orderBy . $limit;
    $result = $this->wpdb->get_results($sql);
    $result = $this->finalPrepareResult($result, '*');
    return $result;
  }
  public function selectCountInvoices($filters) {
    $searchQuery = $filters['searchQuery'];
    $idFirm = $filters['idFirm'];
    $select = "SELECT COUNT(DISTINCT i.id_invoice)";
    $from = $this->prepareFromForInvoices($filters);
    $where = $this->prepareWhereForInvoices($searchQuery, $idFirm);
    $sql = $select . $from . $where;
    $result = $this->wpdb->get_var($sql);
    return $result;
  }
  public function prepareFromForInvoices($filters) {
    $from = " FROM `gi_new_invoices` i
      LEFT JOIN `gi_new_firms` f ON i.firm_id = f.id_firm
      LEFT JOIN `gi_new_orders` o ON i.id_invoice = o.id_invoice
      LEFT JOIN `gi_new_contract` c ON o.contract_sn = c.sn";
    return $from;
  }
  private function prepareFromForContracts($filters, $userId)
  {
    $from = " FROM `gi_new_contract` c
      LEFT JOIN `gi_new_points` p ON c.id_point = p.id_point
      LEFT JOIN `gi_new_orders` o ON c.sn = o.contract_sn";
    if ($filters['My']) {
      $from .= " LEFT JOIN `gi_new_dialogues` d ON c.sn = d.sn 
        INNER JOIN `gi_new_participants_dialog` part ON d.id_dialog = part.id_dialog AND part.id_participant = $userId";
    }
    return $from;
  }
  public function prepareWhereForInvoices($searchQuery, $idFirm) {
    $where = "";
    if (!empty($searchQuery)) {
      $where = " WHERE i.id_invoice LIKE '%$searchQuery%' OR f.firm_name LIKE '%$searchQuery%' OR o.order_name LIKE '%$searchQuery%' OR c.name_contract LIKE '%$searchQuery%' OR c.sn LIKE '%$searchQuery%' ";
      $where .= !empty($idFirm) ? " AND f.id_firm IN ($idFirm)" : "";
    } else {
      $where = !empty($idFirm) ? " WHERE f.id_firm IN ($idFirm)" : "";
    }
    return $where;
  }
  private function prepareWhereForContracts($filters, $pontsID)
  {
    $where = " WHERE c.id_point IN ($pontsID)";
    if (!empty($filters['Search'])) {
      $where .= " AND (c.name_contract LIKE '%$filters[Search]%' 
        OR c.sn LIKE '%$filters[Search]%' 
        OR p.name_point LIKE '%$filters[Search]%' 
        OR o.order_name LIKE '%$filters[Search]%')";
    }
    return $where;
  }
  public function selectSeeContract($userId, $serialNumber)
  {
    $sql = "SELECT * FROM `gi_new_dialogues` d
      INNER JOIN `gi_new_participants_dialog` part ON d.id_dialog = part.id_dialog AND part.id_participant = $userId
      WHERE d.sn = '$serialNumber'";
    $result = $this->wpdb->get_results($sql);
    return $result;
  }
  public function dropTable($name){
    $sql = "DROP TABLE IF EXISTS `$name`";
    $this->wpdb->query($sql);
  }
}
