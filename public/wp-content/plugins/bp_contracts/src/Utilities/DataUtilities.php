<?php
namespace PersonalAccount\Utilities;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\SimpleUtilities;

class DataUtilities
{
  // use DependencyInjections;
  /** @var Container */
  private $Container;
  /** @var DBWorker */
  protected $DBWorker;
  /** @var CatcherBugs */
  private $CatcherBugs;
  /** @var DBUtilities */
  private $DBUtilities;
  /** @var SimpleUtilities */
  private $SimpleUtilities;
  public function __construct($Container)
  {

    $this->Container = &$Container;
    $this->DBWorker = $this->Container->get('DBWorker');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
    $this->DBUtilities = $this->Container->get('DBUtilities');
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
  }
  public function sortArray($array, $whereArr, $valueArr, $whatArr = [])
  {
    $result = [];
    $array = is_array($array) ? $array : [$array];
    $whereArr = is_array($whereArr) ? $whereArr : [$whereArr];
    $valueArr = is_array($valueArr) ? $valueArr : [$valueArr];
    $whatArr = is_array($whatArr) ? $whatArr : [$whatArr];
    foreach ($array as $item) {
      if (is_array($item)) {
        foreach ($whereArr as $where) {
          foreach ($valueArr as $value) {
            if ($item[$where] === $value) {
              $result[] = $item;
            }
          }
        }
      } else {
        foreach ($whereArr as $where) {
          foreach ($valueArr as $value) {
            if ($array[$where] === $value) {
              foreach ($whatArr as $what) {
                $result[$what] = $array[$what];
              }
            }
          }
        }
      }
    }
    if (!empty($result)) {
      if (!empty($whatArr)) {
        $extract = $this->getFromArrByKeyU($result, $whatArr);
        $extract = $this->extractSingleValue($extract);
        // $this->CatcherBugs->convPrintLog($result, 'sortArray', '$result');
        // $this->CatcherBugs->convPrintLog($whatArr, 'sortArray', '$whatArr');
        // $this->CatcherBugs->convPrintLog($extract, 'sortArray', '$extract');
        // $this->CatcherBugs->convPrintLog($whereArr, 'sortArray', '$whereArr');
        return $extract;

      }
      $check = count($result) === 1 && !is_array($result[0]);
      if ($check) {
        return $result[0];
      }
      return $result;
    } else {
      return null;
    }
  }
  public function getFromArrByKeyU($array, $keyArray)
  {
    $result = [];
    $keyArray = is_array($keyArray) ? $keyArray : [$keyArray];
    // Проверка наличия данных и ключей
    if (!empty($array) && !empty($keyArray)) {
      $temp = [];
      if (count($keyArray) === 1) {
        $key = $keyArray[0];
        // Проверяем, если ключ существует в текущем массиве
        if (isset($array[$key])) {
          return $array[$key]; // Если есть, возвращаем сразу значение
        }
        // Если ключ не найден, проверяем каждый элемент массива
        $temp = $this->callBackArrByKey($array, $temp, $key);
      } else {
        // Если ключей больше одного, проверяем каждый ключ
        foreach ($keyArray as $keyNeedle) {
          if (isset($array[$keyNeedle])) {
            $temp[$keyNeedle] = $array[$keyNeedle];
          } else {
            $temp = $this->callBackArrByKey($array, $temp, $keyNeedle, true);
          }
        }
      }
      // Окончательная проверка результата
      $result = !empty($temp) ? $temp : null;
    }
    return $result;
  }
  private function callBackArrByKey($array, $temp, $key, $break = false)
  {
    foreach ($array as $item) {
      if (is_array($item)) {
        $value = $this->getFromArrByKeyU($item, $key);
        if ($value !== null) {
          if ($break) {
            $temp[$key] = $value;
            break; // Останавливаем, если нашли значение
          } else {
            $temp[] = $value;
          }
        }
      }
    }
    return $temp;
  }
  public function sortEmptyOrders($orders)
  {
    $emptyOrders = [];
    foreach ($orders as $order) {
      if (!empty($orders['pointId'])) {
        $emptyOrders[] = $order['PKId'];
      }
    }
    return $emptyOrders;
  }
  public function removeFromArray($array, $valueToRemove)
  {
    $result = array_filter($array, function ($item) use ($valueToRemove) {
      return $item !== $valueToRemove;
    });
    return $result;
  }
  public function convertToIndexArr($nestedArray)
  {
    $result = [];
    foreach ($nestedArray as $associativeArray) {
      $result[] = array_values($associativeArray);
    }
    return $result;
  }
  public function getFromArrayMinMax($array, $key, $which = true)
  {
    if (is_array($array)) {
      // error_log(print_r($array, true));
      if (count($array) > 1 && isset($array[0])) {
        usort($array, function ($a, $b) use ($key, $which) {
          // Определяем тип данных
          $valueA = is_numeric($a[$key]) ? $a[$key] : strtotime($a[$key]);
          $valueB = is_numeric($b[$key]) ? $b[$key] : strtotime($b[$key]);
          // Сравниваем значения
          if ($which) {
            return $valueB - $valueA; // Сортировка по убыванию
          }
          return $valueA - $valueB; // Сортировка по возрастанию
        });
      } else {
        return $array ?? null;
      }
      return $array[0] ?? null;
    }
  }
  public function getFromArrToArr($fromArray, $toArray, $needle, $property)
  {
    if (!empty($fromArray)) {
      $temp = [];
      if (!isset($fromArray[$needle])) {
        foreach ($fromArray as $item) {
          if (isset($item[$needle])) {
            $temp[] = $item[$needle];
          }
        }
      } else {
        $toArray[$property] = $fromArray[$needle];
      }
      if (!empty($temp)) {
        if (count($temp) === 1) {
          $toArray[$property] = $temp[0];
        } else {
          $toArray[$property] = $temp;
        }
      }
    }
    return $toArray;
  }
  public function sortedByQueryArray($array, $propertyArr, $searchQuery)
  {
    $sortedArray = [];
    if (!empty($array) && !empty($searchQuery)) {
      foreach ($array as $item) {
        $overallCheck = $this->checkIncludedByString($item, $propertyArr, $searchQuery);
        if ($overallCheck) {
          $sortedArray[] = $item;
        }
      }
      return $sortedArray;
    }
    return null;
  }
  public function checkIncludedByString($array, $propertiesForSearch, $value)
  {
    $string = '';
    $propertiesForSearch = is_array($propertiesForSearch) ? $propertiesForSearch : [$propertiesForSearch];
    if (!empty($array)) {
      foreach ($propertiesForSearch as $property) {
        if (isset($array[$property])) {
          if (is_array($array[$property])) {
            foreach ($array[$property] as $item) {
              if (is_string($item)) {
                $string .= $item . ' ';
              }
            }
          } else if (is_string($array[$property])) {
            $string .= $array[$property] . ' ';
          }
        }
      }
    }
    $result = $string !== '' ? mb_stripos($string, $value) !== false : false;
    return $result;
  }
  public function prepareOrdersForUserU($sqlOrders, $outputTime)
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
        'invoiceId' => isset($order->id_invoice) ? intval($order->id_invoice) : 0,
        'requiredDate' => $order->required_date,
        'shipmentDate' => $order->shipping_date,
        'receptionDateTS' => convert_timestamp_index($order->receiption_date),
        'confirmationDateTS' => convert_timestamp_index($order->confirmation_date),
        'invoiceDateTS' => convert_timestamp_index($order->invoice_date),
        'requiredDateTS' => convert_timestamp_index($order->required_date),
        'outputDateTS' => $outputDate,
        'shipmentDateTS' => convert_timestamp_index($order->shipping_date),
        'status' => $order->status_order,
        'shipmentNumber' => $order->shipment_id,
        'namePoint' => $order->point_name,
        'brutto' => $order->brutto,
        'netto' => $order->netto,
        'volume' => $order->volume,
        'serialNumber' => $order->contract_sn,
        'shipmentPassword' => $order->shipment_password,
      ];
      $allOrders[$i]['nameContract'] = !empty($allOrders[$i]['serialNumber'])
        ? $this->DBWorker->selectVarFromDBU('gi_new_contract', 'sn', intval($order->contract_sn), 'name_contract')
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
  public function sortContracts($contracts)
  {
    usort($contracts, function ($a, $b) {
      return $b['dateLastActivityTimestamp'] - $a['dateLastActivityTimestamp'];
    });
    $sortedContracts = [];
    foreach ($contracts as $contract) {
      $sortedContracts[$contract['serialNumber']] = $contract;
    }
    return $sortedContracts;
  }
  private function extractSingleValue($array)
  {
    // $this->CatcherBugs->convPrintLog($array, 'extractSingleValue', '$array');
    if (is_array($array) && count($array) === 1) {
      foreach ($array as $value) {
        if (is_array($value)) {
          // $this->CatcherBugs->convPrintLog($value, 'extractSingleValue if (is_array($value))', '$array');
          return $this->extractSingleValue($value);
        } else {
          // $this->CatcherBugs->convPrintLog($value, 'extractSingleValue else', '$value');
          return $value;
        }
      }
    } else {
      // $this->CatcherBugs->convPrintLog($array, 'extractSingleValue else', '$array');
      return $array;
    }
  }
  public function removeFromArrayKey($array, $key)
  {
    foreach ($array as $index => &$innerArray) {
      if (is_array($innerArray)) {
        $innerArray = $this->removeFromArrayKey($innerArray, $key);
      }
    }
    unset($array[$key]); // Remove the key at the current level
    return $array;
  }
  public function getFromMultiArrayKey($array, $key)
  {
    $result = [];
    foreach ($array as $item) {
      if (is_array($item) && isset($item[$key])) {
        $result[] = $item[$key];
      }
    }
    return $result;
  }
  public function prepareContracts($contracts)
  {

  }
  public function getKeyFromArrayByValue(&$stackHay, $needle, $key, $unset = false)
  {
    $result = [];
    foreach ($stackHay as $k => &$item) {
      if ($item[$key['keyCompare']] === $needle) {
        $result[] = $item[$key['keyFind']];
        if ($unset) {
          unset($stackHay[$k]);
        }
      }
    }
    return $result;
  }
  public function packageContracts($contracts, $orders, $pointsName)
  {
    // $this->CatcherBugs->convPrintLog($contracts, 'packageContracts', '$contracts');

    // $this->CatcherBugs->convPrintLog($pointsName, 'packageContracts', '$pointsName');
    $contractsNew = [];
    $ordersNew = [];
    if (!isset($contracts[0])) {
      $contractsNew[0] = $contracts;
    } else {
      $contractsNew = $contracts;
    }
    if (!isset($orders[0])) {
      $ordersNew[0] = $orders;
    } else {
      $ordersNew = $orders;
    }
    // $this->CatcherBugs->convPrintLog($contractsNew, 'packageContracts', '$contracts');
    foreach ($contractsNew as &$item) {
      if (!empty($item)) {
        $item['serialNumber'] = $item['sn'];
        unset($item['sn']);
        $conditions = ['keyCompare' => 'contractSn', 'keyFind' => 'orderName'];
        if (!empty($orders)) {
          // $this->CatcherBugs->convPrintLog($orders, 'packageContracts', '$orders');
          $item['idOrders'] = $this->getKeyFromArrayByValue($ordersNew, $item['serialNumber'], $conditions, true);
          // $this->CatcherBugs->convPrintLog($item['idOrders'], 'packageContracts', 'idOrders');
        }
        $conditions = ['keyCompare' => 'idPoint', 'keyFind' => 'namePoint'];
        $item['pointName'] = array_is_list($pointsName) ? $this->sortArray($pointsName, 'idPoint', $item['idPoint'], 'namePoint') : $pointsName['namePoint'];
        if (isset($item['dateLastActivity'])) {
          $item['dateLastActivityTimestamp'] = convert_timestamp_index($item['dateLastActivity']);
        }
        if (isset($item['dateCreation'])) {
          $item['dateCreationTimestamp'] = convert_timestamp_index($item['dateCreation']);
        }
      }
    }
    // $this->CatcherBugs->convPrintLog($contractsNew, 'packageContracts', '$contracts');
    return $contractsNew;
  }
  public function packageContracts_2($contracts, $unreadedDialogues)
  {
    $contractsNew = [];
    if (!isset($contracts[0])) {
      $contracts[0] = $contracts;
    }
    if (!isset($unreadedDialogues[0])) {
      $unreadedDialoguesNew[0] = $unreadedDialogues;
    } else {
      $unreadedDialoguesNew = $unreadedDialogues;
    }
    if (!empty($contracts)) {
      $i = 0;
      foreach ($contracts as &$item) {
        if (!empty($item)) {
          $contractsNew[$i]['serialNumber'] = $item->sn;
          $contractsNew[$i]['nameContract'] = $item->name_contract;
          $contractsNew[$i]['idPoint'] = $item->id_point;
          $contractsNew[$i]['pointName'] = $item->name_point;
          $contractsNew[$i]['idOrders'] = $item->orders;
          $contractsNew[$i]['dateLastActivity'] = $item->date_last_activity;
          $contractsNew[$i]['dateLastActivityTimestamp'] = convert_timestamp_index($item->date_last_activity);
          $contractsNew[$i]['dateCreation'] = $item->date_creation;
          $contractsNew[$i]['dateCreationTimestamp'] = convert_timestamp_index($item->date_creation);
          if (!empty($unreadedDialoguesNew)) {
            foreach ($unreadedDialoguesNew as $unreadedDialogue) {
              if (isset($unreadedDialogue->sn) && isset($unreadedDialogue->type_dialog)) {
                if ($item->sn === $unreadedDialogue->sn) {
                  $contractsNew[$i]['markerUnread' . $unreadedDialogue->type_dialog] = $unreadedDialogue->type_dialog;
                }
              }
            }
          }
          $i++;
        }
      }
    }
    return $contractsNew;
  }
  // public function prepareToWriteDB($keys = null, $values, $separator = '', $operator = ' = ', $rowOrTurn = false)
  // {
  //   $keys = is_array($keys) ? $keys : [$keys];
  //   $values = is_array($values) ? $values : [$values];
  //   $result = [];
  //   if (!$rowOrTurn) {
  //       $result[''] = $values;
  //   } else {
  //     foreach ($keys as $index => $key) {
  //       $result[$key] = $values[$index]; 
  //     }
  //   }

  //   $result['condition']['separator'] = $separator;
  //   $result['condition']['operator'] = $operator;
  //   return $result;
  // }

  public function packagData($sqlObjectsRelationship, $typeСapitalLetter, $type)
  {
    date_default_timezone_set('Europe/Moscow');
    $objectRelationships = [];
    $name = 'name_' . $type;
    $idObject = ($type === 'contract') ? 'id_point' : 'id_creator';
    $linkId = ($type === 'contract') ? 'idPoint' : 'idCreator';
    foreach ($sqlObjectsRelationship as $sqlObjectRelationship) {
      $objectRelationships[$sqlObjectRelationship->sn] = [
        'serialNumber' => $sqlObjectRelationship->sn,
        'serialNumberForUser' => str_pad($sqlObjectRelationship->sn, 6, 0, STR_PAD_LEFT),
        'name' . $typeСapitalLetter => $sqlObjectRelationship->$name,
        $linkId => $sqlObjectRelationship->$idObject,
        'dateLastActivity' => $sqlObjectRelationship->date_last_activity,
        'dateCreation' => $sqlObjectRelationship->date_creation,
        'dateLastActivityTimestamp' => convert_timestamp_index($sqlObjectRelationship->date_last_activity),
        'dateCreationTimestamp' => convert_timestamp_index($sqlObjectRelationship->date_creation),
      ];
    }
    return $objectRelationships;
  }
  public function sortMultiArrayByKey($array, $key)
  {
    $keys = $this->SimpleUtilities->toArray($key);
    $result = [];
    // $this->CatcherBugs->convPrintLog($keys, 'sortMultiArrayByKey', '$keys');
    // $this->CatcherBugs->convPrintLog($array, 'sortMultiArrayByKey', '$array');
    foreach ($array as $item) {
      foreach ($keys as $key) {
        $result[$key][] = $item[$key];
      }
    }
    return $result;
  }
}
