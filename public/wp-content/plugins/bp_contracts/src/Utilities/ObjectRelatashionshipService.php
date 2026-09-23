<?php
namespace PersonalAccount\Utilities;
//WILL BE REBUILT
class ObjectRelatashionshipService
{
  private static $wpdb;
  public static function setWPDB()
  {
    global $wpdb;
    self::$wpdb = &$wpdb;
  }
  private static function addDateLastActivityFilter($filters, $contracts)
  {
    foreach ($contracts as $contract) {
      $latestDate = null;
      foreach ($filters as $filter) {
        $dateKey = 'dateLastActivity' . $filter;
        if (isset($contract[$dateKey]) && !empty($contract[$dateKey])) {
          $date = $contract[$dateKey];
          if ($latestDate === null || strtotime($date) > strtotime($latestDate)) {
            $latestDate = $date;
          }
        }
      }
      $contract['dateLastActivityFilter'] = strtotime($latestDate);
    }
    return $contracts;
  }
  public static function packagData($sqlObjectsRelationship, $typeСapitalLetter, $type)
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
  // getContracts - Возможность видеть все договора на точках, к которым они относятся.
  // searchContracts - Поиск по базе контрактов по sn, name_contract, order_number.
  public static function searchContracts($searchQuery, $points)
  {
    $searchQueryLike = '%' . $searchQuery . '%';
    $searchQuerySql = " (sn LIKE '$searchQueryLike' OR name_contract LIKE '$searchQueryLike' OR order_number LIKE '$searchQueryLike')";
    $sqlResult = self::$wpdb->get_results("SELECT * FROM gi_new_contract 
        WHERE id_point IN ($points) 
        AND $searchQuerySql
        ORDER BY date_last_activity DESC
      ");
    $contracts = self::packagData($sqlResult, 'Contract', 'contract');
    return $contracts;
  }
  public static function filterContracts($filters, $contracts)
  {
    return array_filter($contracts, function ($contract) use ($filters) {
      // Проверяем наличие хотя бы одного свойства из filters в contract
      foreach ($filters as $filter) {
        if (array_key_exists($filter, $contract)) {
          return true;
        }
      }
      // Если ни одного свойства нет, возвращаем false
      return false;
    });
  }
  // Функция для сортировки контрактов по наибольшей дате 'dateLastActivity' для свойств из $filters
  public static function sortContractsByDateLastActivityFilter($filters, $contracts)
  {
    $contracts = self::addDateLastActivityFilter($filters, $contracts);
    usort($contracts, function ($a, $b) {
      $dateA = isset($a['dateLastActivityFilter']) ? $a['dateLastActivityFilter'] : null;
      $dateB = isset($b['dateLastActivityFilter']) ? $b['dateLastActivityFilter'] : null;
      if ($dateA === $dateB) {
        return 0;
      }
      return ($dateA > $dateB) ? -1 : 1;
    });
    foreach ($contracts as $contract) {
      unset($contract['dateLastActivityFilter']);
    }
    return $contracts;
  }

  public static function prepareListObjectsRelatashionShip($array, $perPage, $currentPage)
  {
    $countPage = 0;
    if (!empty($array)) {
      $countPage = ceil(count($array) / $perPage);
      $offset = $perPage * ($currentPage - 1);
      $pagedContracts = array_slice($array, $offset, $perPage);
    }
    $pagedContracts[] = ['countPage' => $countPage];
    return $pagedContracts;
  }
  public static function sortDialogues($unreadedDialogues, $typeObjectRelatashionship)
  {
    $unreadedDialoguesObjectRelatashionship = [];
    if (!empty($unreadedDialogues)) {
      foreach ($unreadedDialogues as $unreadedDialog) {
        $type = $unreadedDialog->type_dialog;
        if (($type === 'Consultation' || $type === 'Order' || $type === 'Complaint') && $typeObjectRelatashionship === 'Contract') {
          $unreadedDialoguesObjectRelatashionship[] = $unreadedDialog;
        } elseif ($type === 'Invoices' && $typeObjectRelatashionship === 'Invoices') {
          $unreadedDialoguesObjectRelatashionship[] = $unreadedDialog;
        } elseif ($type === 'Shipments' && $typeObjectRelatashionship === 'Shipments') {
          $unreadedDialoguesObjectRelatashionship[] = $unreadedDialog;
        }
      }
      return $unreadedDialoguesObjectRelatashionship;
    }
  }
  public static function markUnreaded($contracts, $unreadedDialogues)
  {
    $markedContracts = $contracts;
    foreach ($markedContracts as &$contract) {
      if (!empty($unreadedDialogues)) {
        foreach ($unreadedDialogues as $unreadedDialog) {
          $type = $unreadedDialog->type_dialog;
          $keyType = 'markerUnread' . $type;
          $dateLastActivityType = $unreadedDialog->date_last_activity;
          $keyDate = 'dateLast' . $type;
          //Помечаем все контракты в которых есть не прочитаные диалоги
          if ($contract['serialNumber'] === intval($unreadedDialog->sn)) {
            $contract[$keyType] = $type;
            $contract[$keyDate] = $dateLastActivityType;
          }
        }
      }
    }
    return $markedContracts;
  }
  public static function sortMyObject($contracts, $myDialogues)
  {
    $myContracts = [];
    if (!empty($contracts)) {
      foreach ($contracts as $contract) {
        if (!empty($myDialogues)) {
          foreach ($myDialogues as $dialog) {
            if (isset($contract['serialNumber']) && isset($dialog->sn)) {
              if ($contract['serialNumber'] === intval($dialog->sn)) {
                $myContracts[$contract['serialNumber']] = $contract;
              }
            }
          }
        }
      }
    }
    // Преобразуем ассоциативный массив обратно в простой
    return array_values($myContracts);
  }
  public static function packageContracts(){
    
  }
  // Другие методы для работы с контрактами
}