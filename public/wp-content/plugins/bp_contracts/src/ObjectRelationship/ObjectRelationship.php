<?php
// Другие сущности
// objectRelationship - родитель
namespace PersonalAccount\ObjectRelationship;
use PersonalAccount\ObjectRelationship\traits\capsule\CapsuleObjectRelationship;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Factory\Factory;
use PersonalAccount\Core\Container;
use PersonalAccount\Dialog\Dialog;

class ObjectRelationship
{
  use Utilit; //Содержаться все функции которые имеют к базе данных, но нужны в разных объектах, а так же общая функция для создания капсулы
  use CapsuleObjectRelationship;
  /** @var Container */
  protected $container;
  /** @var UserUtilities */
  protected $userUtilities;
  /** @var DBWorker */
  protected $dbWorker;
  /** @var DataUtilities */
  protected $dataUtilities;
  /** @var SimpleUtilities */
  protected $simpleUtilities;
  /** @var Factory */
  protected $factory;
  /** @var CatcherBugs */
  protected $catcherBugs;
  protected $serialNumber;
  protected $serialNumberForUser;
  protected $dateLastActivity;
  protected $dateLastActivityTimestamp;
  protected $dateCreation;
  protected $dateCreationTimestamp;
  protected $type;
  protected $idOrders; // This is a mistake. Here contains
  protected $dataDialogues;
  protected $dialogues; // Объекты создаваемые при вызове диалога
  protected $usersHaveAccess;
  protected $idUsersHaveAccess;
  protected $openedUsers;
  protected $ordersData;
  protected $pathTableOrderDB;
  protected $rolesFactory;
  // protected $closures;
  protected function __construct($paramCurrentObject, $container, $parent)
  {


    $this->container = &$container;
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->dbWorker = $this->container->get('DBWorker');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->factory = $this->container->get('Factory');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->initializationWPDB();

    $this->serialNumber = $paramCurrentObject['serialNumber'];
    $this->serialNumberForUser = $paramCurrentObject['serialNumberForUser'];
    $this->dateLastActivity = $paramCurrentObject['dateLastActivity'];
    $this->dateCreation = $paramCurrentObject['dateCreation'];
    $this->dateLastActivityTimestamp = $paramCurrentObject['dateLastActivityTimestamp'];
    $this->dateCreationTimestamp = $paramCurrentObject['dateCreationTimestamp'];
    $this->dialogues = [];
    $this->openedUsers = [];
    $this->rolesFactory = $this->userUtilities->initializationRolesfactory();


  }

  protected function updateOrdersDataOR($pathTableOrderDB, $whoseObject, $serialNumber)
  {
    $ordersData = $this->dbWorker->selectResultsFromDBU($pathTableOrderDB, $whoseObject, $serialNumber);
    if ($ordersData) {
      $timeOutput = 60;
      $this->ordersData = $this->dataUtilities->prepareOrdersForUserU($ordersData, $timeOutput);
    } else {
      $this->ordersData = [];
    }
  }
  protected function setIdOrders($orderData, $idOrders)
  {
    $idOrders = [];
    if (!empty($orderData)) {
      foreach ($orderData as $order) {
        $idOrders[] = $order['orderNumber'];
      }
    }
    return $idOrders;
  }
  protected function packageDataDialogues($sqlDataDialogues)
  {
    date_default_timezone_set('Europe/Moscow');
    $dataDialogues = [];
    foreach ($sqlDataDialogues as $sqlDialog) {
      $dataDialogues[$sqlDialog->id_dialog] = [
        'idDialog' => $sqlDialog->id_dialog,
        'serialNumber' => $sqlDialog->sn,
        'idCreator' => $sqlDialog->id_creator,
        'typeDialog' => $sqlDialog->type_dialog,
        'dateLastActivity' => $sqlDialog->date_last_activity,
        'statusActivity' => $sqlDialog->status_activity,
        'dateLastActivityTimestamp' => convert_timestamp_index($sqlDialog->date_last_activity),
        'idOrder' => isset($sqlDialog->id_order) ? $sqlDialog->id_order : null,
        // 'usersHaveAccess' => $this->usersHaveAccess,
      ];
    }
    return $dataDialogues;
  }
  protected function getDialogues($type)
  {
    $sqlDialogues = $this->wpdb->get_results("SELECT * FROM gi_new_dialogues 
    WHERE sn = '$this->serialNumber' AND type_dialog = '$type'");
    if (!empty($sqlDialogues)) {
      $dialogues = $this->packageDataDialogues($sqlDialogues);
      return $dialogues;
    }
    return null;
  }
  public function shutDownDialoguesOR()
  {
    foreach ($this->dialogues as $dialogType) {
      if (!empty($dialogType)) {
        foreach ($dialogType as $dialog) {
          /** @var Dialog $dialog */
          $dialog->shutDownAllMessages();
        }
      }
    }
    $this->dialogues = [];
  }
  public function updateDateLastActivityContract()
  {
    $dateLastActivity = $this->dbWorker->selectResultsFromDBU_2('gi_new_contract', 'sn', $this->serialNumber, 'date_last_activity');
    $this->dateLastActivity = $dateLastActivity;
    $this->dateLastActivityTimestamp = convert_timestamp_index($dateLastActivity);
    // $this->wpdb->query("UPDATE gi_new_contract SET date_last_activity = '$dateLastActivity' WHERE sn = '$this->serialNumber'");
  }
  public function outsideWhoHaveAccess()
  {
    // $this->catcherBugs->convPrintLog($this->usersHaveAccess, 'outsideWhoHaveAccess','$this->usersHaveAccess');
    return $this->usersHaveAccess;
  }
  public function checkOpened()
  {
      if (empty($this->openedUsers)) {
        return false;
      } else {
        return true;
      }
  }
  public function userClosedOR($idWebsocket)
  {

    $this->openedUsers = array_filter($this->openedUsers, function ($item) use ($idWebsocket) {
      return $item !== $idWebsocket;
    });
    // $this->catcherBugs->convPrintLog($this->openedUsers, 'userClosedOR', '$this->openedUsers');
    $this->openedUsers = array_values($this->openedUsers);
  }
  public function getWhoOpenedOR()
  {
    return $this->openedUsers;
  }
  public function regWhoOpenOR($idWebsocket)
  {
    $this->openedUsers[] = $idWebsocket;
    $this->openedUsers = array_unique($this->openedUsers);
    // $this->catcherBugs->convPrintLog($this->openedUsers, 'regWhoOpenOR', '$this->openedUsers');
  }
  public function getOutsideOrdersDataOR()
  {
      return $this->ordersData;
  }
  public function updateOrdersInOR()
  {
    $this->updateOrdersDataOR($this->pathTableOrderDB, 'contract_sn', $this->serialNumber);
    $this->idOrders = $this->setIdOrders($this->ordersData, $this->idOrders);
    // $this->catcherBugs->convPrintLog($this->serialNumber, 'updateOrdersInOR', '$this->serialNumber');
  }

  public function getUsersHaveAccess()
  {
      return $this->usersHaveAccess;
  }
  public function getIdUsersHaveAccess()
  {
    return $this->idUsersHaveAccess;
  }
}