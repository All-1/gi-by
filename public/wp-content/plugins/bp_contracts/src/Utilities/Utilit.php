<?php
// WILL BE UNSET
// I'm going to unset this trait and release all of these methods  in the suited classes
namespace PersonalAccount\Utilities;
trait Utilit
{
  protected $wpdb;
  protected function getProperty($property)
  {
    if (property_exists($this, $property)) {
      return $this->$property;
    }
  }
  protected function setProperty($property, $value)
  {
    $this->$property = $value;
  }
  protected function initializationWPDB()
  {
    global $wpdb;
    $this->wpdb = &$wpdb;
  }
  // I'm going to release this method in class Factory
  // protected function createDependentObjects($newObject, $closures, $paramDependentObjects)
  // {
  //   $dependentObjects = [];
  //   // $paramDependentObjects = $single ? [$paramDependentObjects] : $paramDependentObjects;
  //   // $this->CatcherBugs->convPrintLog($paramDependentObjects, 'Utilit', 'allPointsData');
  //   if ($closures && !empty($paramDependentObjects)) {
  //     foreach ($paramDependentObjects as $params) {
  //       // $this->CatcherBugs->convPrintLog($params, 'Utilit', 'params');
  //       $dependentObjects[] = new $newObject($params, $closures);
  //     }
  //   }
  //   return $dependentObjects;
  // }
  // protected function createDependentObjects_2($newObject, $closures, $paramDependentObjects)
  // {
  //   $dependentObjects = [];
  //   $check = is_array($paramDependentObjects) && !empty($paramDependentObjects) && !isset($paramDependentObjects[0]);
  //   $paramDependentObjects = $check ? [$paramDependentObjects] : $paramDependentObjects;
  //   // $this->CatcherBugs->convPrintLog($paramDependentObjects, 'Utilit', 'allPointsData');
  //   if ($closures && !empty($paramDependentObjects)) {
  //     foreach ($paramDependentObjects as $params) {
  //       // $this->CatcherBugs->convPrintLog($params, 'Utilit', 'params');
  //       $dependentObjects[] = new $newObject($params, $closures);
  //     }
  //   }
  //   return $dependentObjects;
  // }
  // public function sortContracts($contracts)
  // {
  //   usort($contracts, function ($a, $b) {
  //     return $b['dateLastActivityTimestamp'] - $a['dateLastActivityTimestamp'];
  //   });
  //   $sortedContracts = [];
  //   foreach ($contracts as $contract) {
  //     $sortedContracts[$contract['serialNumber']] = $contract;
  //   }
  //   return $sortedContracts;
  // }

  // protected function prepareOrdersForUserU($sqlOrders, $outputTime)
  // {
  //   $allOrders = [];
  //   $i = 0;
  //   $outputTime = $outputTime * 24 * 60 * 60;
  //   foreach ($sqlOrders as $order) {
  //     $outputDate = convert_timestamp_index($order->confirmation_date) + $outputTime;
  //     $allOrders[$i] = [
  //       'id' => $order->id,
  //       'orderNumber' => $order->order_name,
  //       'clientName' => $order->client_name,
  //       'receptionDate' => $order->receiption_date,
  //       'proformaDate' => $order->proforma_date,
  //       'confirmationDate' => $order->confirmation_date,
  //       'invoiceDate' => $order->invoice_date,
  //       'paymentDate' => $order->payment_date,
  //       'requiredDate' => $order->required_date,
  //       'shipmentDate' => $order->shipping_date,
  //       'receptionDateTS' => convert_timestamp_index($order->receiption_date),
  //       'confirmationDateTS' => convert_timestamp_index($order->confirmation_date),
  //       'invoiceDateTS' => convert_timestamp_index($order->invoice_date),
  //       'paymentDateTS' => convert_timestamp_index($order->payment_date),
  //       'requiredDateTS' => convert_timestamp_index($order->required_date),
  //       'outputDateTS' => $outputDate,
  //       'shipmentDateTS' => convert_timestamp_index($order->shipping_date),
  //       'status' => $order->status_order,
  //       'shipmentNumber' => $order->shipping_number,
  //       'namePoint' => $order->point_name,
  //       'brutto' => $order->brutto,
  //       'netto' => $order->netto,
  //       'volume' => $order->volume,
  //       'serialNumber' => $order->contract_sn,
  //       'shipmentPassword' => $order->shipment_password,
  //     ];
  //     $allOrders[$i]['nameContract'] = !empty($allOrders[$i]['serialNumber'])
  //       ? $this->DBWorker->selectVarFromDBU('gi_new_contract', 'sn', intval($order->contract_sn), 'name_contract')
  //       : '';
  //     $allOrders[$i]['orderStatusDate'] = !empty($allOrders[$i]['status']) ? $this->defineStatusDate($allOrders[$i]['status'], $allOrders[$i]) : '';
  //     $allOrders[$i]['orderStatusDateTS'] = !empty($allOrders[$i]['status']) ? convert_timestamp_index($allOrders[$i]['orderStatusDate']) : '';
  //     $i++;
  //   }
  //   return $allOrders;
  // }
  // protected function defineStatusDate($orderStatus, $order)
  // {
  //   switch ($orderStatus) {

  //     case 'Проформа':
  //       $dateStatus = $order['proformaDate'];
  //       break;
  //     case 'Подтверждён':
  //       $dateStatus = $order['confirmationDate'];
  //       break;
  //     case 'Обработка':
  //     case 'Производство':
  //     case 'Упакован':
  //     case 'Отгружен частично':
  //     case 'Отгружен':
  //       $dateStatus = '';
  //       break;
  //   }
  //   return $dateStatus;
  // }
  // protected function checkUserAccess($idUser)
  // {
  //   $userRole = serviceUser::getUserRole($idUser);
  //   return serviceUser::partyVerification($userRole);
  // }
  // protected function snakeToCamelU($string)
  // {
  //   return lcfirst(str_replace('_', '', ucwords($string, '_')));
  // }
  // protected function checkStringCompU($string, $value)
  // {
  //   $result = $string ?
  //     mb_stripos($string, $value) !== false :
  //     false;
  //   return $result;
  // }
  // protected function checkIncludedByStringU($array, $propertiesForSearch, $value)
  // {
  //   $string = '';
  //   $propertiesForSearch = is_array($propertiesForSearch) ? $propertiesForSearch : [$propertiesForSearch];
  //   if (!empty($array)) {
  //     foreach ($propertiesForSearch as $property) {
  //       if (isset($array[$property])) {
  //         if (is_array($array[$property])) {
  //           foreach ($array[$property] as $item) {
  //             if (is_string($item)) {
  //               $string .= $item . ' ';
  //             }
  //           }
  //         } else if (is_string($array[$property])) {
  //           $string .= $array[$property] . ' ';
  //         }
  //       }
  //     }
  //   }
  //   $result = $string !== '' ? mb_stripos($string, $value) !== false : false;
  //   return $result;
  // }
  // protected function changeStatusToRus($status, $mode = 2)
  // {
  //   $statusRus = '';
  //   if ($mode === 2) {
  //     if ($status === 'active' || $status === 'noactive') {
  //       $statusRus = 'активный';
  //     } else {
  //       $statusRus = 'заблокирован';
  //     }
  //   } else if ($mode === 3) {
  //     if ($status === 'active') {
  //       $statusRus = 'активный';
  //     } else if ($status === 'noactive') {
  //       $statusRus = 'не работает';
  //     } else if ($status === 'blocked') {
  //       $statusRus = 'заблокирован';
  //     }
  //   }
  //   return $statusRus;
  // }

  // protected function currentTime()
  // {
  //   date_default_timezone_set('Europe/Moscow');
  //   return date('Y-m-d H:i:s');
  // }
  // protected function getUserEmail($userId)
  // {
  //   $userInfo = get_userdata($userId);

  //   if ($userInfo) {
  //     $email = $userInfo->user_email;
  //     return $email;
  //   } else {
  //     error_log("Пользователь с ID $userId не найден.");
  //   }
  // }
  // protected function getInfoAboutUsers($users, $separate = false)
  // {
  //   $allInfoUsers = [];
  //   $users = is_array($users) ? $users : [$users];
  //   foreach ($users as $id) {
  //     if ($id && (is_string($id) || is_int($id))) {
  //       $allInfoUsers[$id] = [];
  //       $userInfo = get_userdata($id);
  //       $allInfoUsers[$id]['id'] = $id;

  //       $allInfoUsers[$id]['role'] = serviceUser::getUserRole($id);
  //       $allInfoUsers[$id]['whose'] = serviceUser::partyVerification($allInfoUsers[$id]['role']);
  //       $allInfoUsers[$id]['displayName'] = $userInfo->last_name . ' ' . $userInfo->first_name;
  //       $allInfoUsers[$id]['rank'] = $this->determinationRank($id);
  //       if (!$separate) {
  //         $allInfoUsers[$id]['name'] = $userInfo->first_name;
  //         $allInfoUsers[$id]['lastname'] = $userInfo->last_name;
  //       }
  //     }
  //   }
  //   ;
  //   return $allInfoUsers;
  // }
  // protected function getNameUser($userId)
  // {
  //   $author = $this->UserUtilities->getInfoAboutUsers($userId);
  //   $nameAuthor = $author[$userId]['name'] . ' ' . $author[$userId]['lastname'];
  //   return $nameAuthor;
  // }
  //
  // protected function packageUser($sqlUsers)
  // {
  //   $users = [];
  //   $sqlUsers = is_array($sqlUsers) ? $sqlUsers : [$sqlUsers];
  //   foreach ($sqlUsers as $idUser) {
  //     $idUser = intval($idUser);
  //     $users[$idUser] = [];
  //     $users[$idUser]['id'] = $idUser;
  //     $users[$idUser]['role'] = serviceUser::getUserRole(intval($idUser));
  //     $users[$idUser]['area'] = serviceUser::areaVerification($users[$idUser]['role']);
  //     $users[$idUser]['statusActivity'] = $this->getStatusActivityDB(intval($idUser));
  //   }
  //   return $users;
  // }
  // protected function packegeUserForClient($users, $idUserInArr, $userId)
  // {
  //   $role = $users[$idUserInArr]['role'];
  //   $user = [
  //     'idUser' => $idUserInArr,
  //     'role' => $role,
  //     'firstname' => get_user_meta($idUserInArr, 'first_name', true),
  //     'lastname' => get_user_meta($idUserInArr, 'last_name', true),
  //     'statusActivity' => $this->getStatusActivityDB($idUserInArr),
  //     'whose' => serviceUser::partyVerification($role)
  //   ];
  //   $this->CatcherBugs->convPrintLog($users[$userId]['statusActivity'], 'packegeUserForClient', '$users[$userId][statusActivity]');
  //   if ($role === 'manager') {
  //     $where = ['id_manager', 'state'];
  //     $value = [
  //       'id_manager' => $user['idUser'],
  //       'state' => 1
  //     ];
  //     $user['points'] = $this->DBWorker->selectResultsFromDBU_2('gi_new_points', $where, $value, 'id_point');
  //   }
  //   return $user;
  // }


  // protected function packagePointsForUser($where = null, $idUser = null)
  // {
  //   $sqlPoints = $this->selectResultsFromDBU('gi_new_points', $where, $idUser);
  //   return $sqlPoints;
  // }
  // protected function toArray($value)
  // {
  //   if (is_array($value)) {
  //     $newValue = $value;
  //   } else {
  //     $newValue[] = $value;
  //   }
  //   return $newValue;
  // }
  // public function extractUserParam($userInfo, $param, $userId, $where)
  // {
  //   if (!empty($userInfo)) {
  //     $value = $userInfo->$param;
  //   } else {
  //     error_log("extractUserParam: User with id $userId didn't find in $where");
  //   }
  //   return $this->CatcherBugs->checkReturn($value, 'extractUserParam', $param);
  // }
  // protected function getFromArrByKeyU_OLD($array, $keyArray)
  // {
  //   $result = [];
  //   $keyArray = is_array($keyArray) ? $keyArray : [$keyArray];
  //   if (!empty($array) && !empty($keyArray)) {
  //     foreach ($array as $item) {
  //       //Переписать в понедельник.
  //       $temp = [];
  //       foreach ($keyArray as $keyNeedle) {
  //         if (isset($item[$keyNeedle]) && $item[$keyNeedle]) {
  //           if (count($keyArray) > 1) {
  //             $temp[$keyNeedle] = $item[$keyNeedle];
  //           } else {
  //             $result = $this->getForOneKey($result, $array, $item, $keyNeedle);
  //           }
  //         } else if (isset($array[$keyNeedle]) && $array[$keyNeedle]) {
  //           // error_log("print_r(item[keyNeedle], true )");
  //           // error_log(print_r($array[$keyNeedle], true));
  //         }
  //       }
  //       if (!empty($temp)) {
  //         $result[] = $temp;
  //       }
  //     }
  //   }
  //   if (!is_array($result) && $result) {
  //     return $result;
  //   }
  //   return !empty($result) ? $result : null;
  // }
  // protected function getFromArrByKeyU_OLD2($array, $keyArray)
  // {
  //   $result = [];
  //   $keyArray = is_array($keyArray) ? $keyArray : [$keyArray];
  //   // Проверка наличия данных и ключей
  //   if (!empty($array) && !empty($keyArray)) {
  //     $temp = [];
  //     if (count($keyArray) === 1) {
  //       $key = $keyArray[0];
  //       // Проверяем, если ключ существует в текущем массиве
  //       if (isset($array[$key])) {
  //         return $array[$key]; // Если есть, возвращаем сразу значение
  //       }
  //       // Если ключ не найден, проверяем каждый элемент массива
  //       foreach ($array as $item) {
  //         if (is_array($item)) {
  //           $value = $this->getFromArrByKeyU($item, $key);
  //           if ($value !== null) {
  //             $temp[] = $value;
  //           }
  //         }
  //       }
  //       // $temp = $this->callBackArrByKey($array, $temp, $key);
  //     } else {
  //       // Если ключей больше одного, проверяем каждый ключ
  //       foreach ($keyArray as $keyNeedle) {
  //         if (isset($array[$keyNeedle])) {
  //           $temp[$keyNeedle] = $array[$keyNeedle];
  //         } else {
  //           foreach ($array as $item) {
  //             if (is_array($item)) {
  //               $value = $this->getFromArrByKeyU($item, $keyNeedle);
  //               if ($value !== null) {
  //                 $temp[$keyNeedle] = $value;
  //                 break; // Останавливаем, если нашли значение
  //               }
  //             }
  //           }
  //         }
  //       }
  //     }
  //     // Окончательная проверка результата
  //     $result = !empty($temp) ? $temp : null;
  //   }
  //   return $result;
  // }
  // protected function getFromArrByKeyU($array, $keyArray)
  // {
  //   $result = [];
  //   $keyArray = is_array($keyArray) ? $keyArray : [$keyArray];
  //   // Проверка наличия данных и ключей
  //   if (!empty($array) && !empty($keyArray)) {
  //     $temp = [];
  //     if (count($keyArray) === 1) {
  //       $key = $keyArray[0];
  //       // Проверяем, если ключ существует в текущем массиве
  //       if (isset($array[$key])) {
  //         return $array[$key]; // Если есть, возвращаем сразу значение
  //       }
  //       // Если ключ не найден, проверяем каждый элемент массива
  //       $temp = $this->callBackArrByKey($array, $temp, $key);
  //     } else {
  //       // Если ключей больше одного, проверяем каждый ключ
  //       foreach ($keyArray as $keyNeedle) {
  //         if (isset($array[$keyNeedle])) {
  //           $temp[$keyNeedle] = $array[$keyNeedle];
  //         } else {
  //           $temp = $this->callBackArrByKey($array, $temp, $keyNeedle, true);
  //         }
  //       }
  //     }
  //     // Окончательная проверка результата
  //     $result = !empty($temp) ? $temp : null;
  //   }
  //   return $result;
  // }
  // protected function callBackArrByKey($array, $temp, $key, $break = false)
  // {
  //   foreach ($array as $item) {
  //     if (is_array($item)) {
  //       $value = $this->DataUtilities->getFromArrByKeyU($item, $key);
  //       if ($value !== null) {
  //         if ($break) {
  //           $temp[$key] = $value;
  //           break; // Останавливаем, если нашли значение
  //         } else {
  //           $temp[] = $value;
  //         }
  //       }
  //     }
  //   }
  //   return $temp;
  // }

  // protected function getForOneKey($result, $array, $item, $keyNeedle)
  // {
  //   if (count($array) > 1) {
  //     $result[$keyNeedle][] = $item[$keyNeedle];
  //   } else {
  //     $result = $array[$keyNeedle];
  //     return $result ? $result : null;
  //   }
  //   return !empty($result) ? $result : null;
  // }
  // protected function sortArrayU($array, $whereArr, $valueArr, $whatArr = [])
  // {
  //   $result = [];
  //   $array = is_array($array) ? $array : [$array];
  //   $whereArr = is_array($whereArr) ? $whereArr : [$whereArr];
  //   $valueArr = is_array($valueArr) ? $valueArr : [$valueArr];
  //   $whatArr = is_array($whatArr) ? $whatArr : [$whatArr];
  //   foreach ($array as $item) {
  //     foreach ($whereArr as $where) {
  //       foreach ($valueArr as $value) {
  //         if ($item[$where] === $value) {
  //           $result[] = $item;
  //         }
  //       }
  //     }
  //   }
  //   // error_log(print_r('sortArrayU', true));
  //   // error_log(print_r($result, true));
  //   if (!empty($whatArr)) {
  //     $extract = $this->DataUtilities->getFromArrByKeyU($result, $whatArr);
  //     return $extract;
  //   }
  //   if (!empty($result)) {
  //     $check = count($result) === 1 && !is_array($result[0]);
  //     if ($check) {
  //       // error_log(print_r($result[0], true));
  //       return $result[0];
  //     }
  //     return $result;
  //   } else {
  //     return null;
  //   }
  // }
  // protected function getFromArrayMinMax($array, $key, $which = true)
  // {
  //   if (is_array($array)) {
  //     // error_log(print_r($array, true));
  //     if (count($array) > 1 && isset($array[0])) {
  //       usort($array, function ($a, $b) use ($key, $which) {
  //         // Определяем тип данных
  //         $valueA = is_numeric($a[$key]) ? $a[$key] : strtotime($a[$key]);
  //         $valueB = is_numeric($b[$key]) ? $b[$key] : strtotime($b[$key]);
  //         // Сравниваем значения
  //         if ($which) {
  //           return $valueB - $valueA; // Сортировка по убыванию
  //         }
  //         return $valueA - $valueB; // Сортировка по возрастанию
  //       });
  //     } else {
  //       return $array ?? null;
  //     }
  //     return $array[0] ?? null;
  //   }
  //   // Возвращаем первый элемент, который соответствует минимальному или максимальному значению
  //   // Если массив пустой, возвращаем null
  // }
  // private function mergeArrays($array1, $array2)
  // {
  //   if (!empty($array1) && !empty($array2)) {
  //     return array_merge($array1, $array2);
  //   } else if (empty($array1) && !empty($array2)) {
  //     return $array2;
  //   } else if (!empty($array1) && empty($array2)) {
  //     return $array1;
  //   }
  //   return null;
  // }
  // protected function getFromArrToArrU($fromArray, $toArray, $needle, $property)
  // {
  //   if (!empty($fromArray)) {
  //     $temp = [];
  //     if (!isset($fromArray[$needle])) {
  //       foreach ($fromArray as $item) {
  //         if (isset($item[$needle])) {
  //           $temp[] = $item[$needle];
  //         }
  //       }
  //     } else {
  //       $toArray[$property] = $fromArray[$needle];
  //     }
  //     if (!empty($temp)) {
  //       if (count($temp) === 1) {
  //         $toArray[$property] = $temp[0];
  //       } else {
  //         $toArray[$property] = $temp;
  //       }
  //     }
  //   }
  //   return $toArray;
  // }
  // protected function diffTime($time, $previousTime)
  // {
  //   $timeTimeStamp = convert_timestamp_index($time);
  //   $previousTimeStamp = convert_timestamp_index($previousTime);
  //   $diff = $timeTimeStamp - $previousTimeStamp;
  //   return $diff;
  // }
  //Attach to trait Utilit
  // protected function sortedByQueryArrayU($array, $propertyArr, $searchQuery)
  // {
  //   $sortedArray = [];
  //   if (!empty($array) && !empty($searchQuery)) {
  //     foreach ($array as $item) {
  //       $overallCheck = $this->checkIncludedByStringU($item, $propertyArr, $searchQuery);
  //       if ($overallCheck) {
  //         $sortedArray[] = $item;
  //       }
  //     }
  //     return $sortedArray;
  //   }
  //   return null;
  // }
  // protected function defineTypeOR($typeDialog)
  // {
  //   switch ($typeDialog) {
  //     case "Consultation":
  //     case "Order":
  //     case "Complaint":
  //       $typeOR = 'contract';
  //       break;
  //     case "Shipment":
  //       $typeOR = 'shipment';
  //       break;
  //     case "Invoice":
  //       $typeOR = 'invoice';
  //       break;
  //   }
  //   return $typeOR;
  // }
  // protected function typeDialogLatToCyr($typeDialog)
  // { {
  //     switch ($typeDialog) {
  //       case "Consultation":
  //         $typeDialogCyr = 'Консультация';
  //         break;
  //       case "Order":
  //         $typeDialogCyr = 'Заказ';
  //         break;
  //       case "Complaint":
  //         $typeDialogCyr = 'Рекламация';
  //         break;
  //       case "Shipment":
  //         $typeDialogCyr = 'Отгрузка';
  //         break;
  //       case "Invoice":
  //         $typeDialogCyr = 'Счёт';
  //         break;
  //     }
  //     return $typeDialogCyr;
  //   }
  // }
  // function formatNumber($number)
  // {
  //   // Преобразуем число в строку и дополняем слева нулями до длины 6 символов
  //   return str_pad($number, 6, '0', STR_PAD_LEFT);
  // }
  

  // function checkReturn($value, $nameMethod, $what)
  // {
  //   if (!empty($value)) {
  //     return $value;
  //   } else {
  //     error_log("checkReturn: $nameMethod return null in $what");
  //     return null;
  //   }
  // }
}
