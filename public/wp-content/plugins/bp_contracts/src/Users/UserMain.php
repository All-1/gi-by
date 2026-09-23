<?php
namespace PersonalAccount\Users;

use PersonalAccount\Core\Container;

use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\DialogServices;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\UPWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Workers\MessageWorker;
use PersonalAccount\Workers\InvoiceWorker;
use PersonalAccount\Workers\OrdersWorker;
use PersonalAccount\Users\interfaces\UserMainInterface;
use PersonalAccount\Users\traits\capsule\CapsuleUserParam;
use PersonalAccount\Users\traits\WorkWithOrders;
use PersonalAccount\Users\traits\WorkWithContracts;
use PersonalAccount\Workers\ContractsWorker;
use PersonalAccount\Workers\Collector\UserCollector;  

use PersonalAccount\Controler\ControlerObjectsRelationship;
use Exception;
class UserMain implements UserMainInterface
{
  use Utilit; // Will reduce
  use CapsuleUserParam; // Will reduce
  use WorkWithOrders;
  use WorkWithContracts;

  /** @var SimpleUtilities */
  protected $SimpleUtilities;
  /** @var DataUtilities */
  protected $DataUtilities;
  /** @var DBUtilities */
  protected $DBUtilities;
  /** @var DialogServices */
  protected $DialogServices;
  /** @var UserUtilities */
  protected $UserUtilities;
  /** @var DBWorker */
  protected $DBWorker;
  /** @var UPWorker */
  protected $UPWorker;
  /** @var CatcherBugs */
  protected $CatcherBugs;
  /** @var ControlerObjectsRelationship */
  private $ControlerObjectsRelationship;
  /** @var MessageWorker */
  protected $MessageWorker;
  /** @var InvoiceWorker */
  protected $InvoiceWorker;
  /** @var ContractsWorker */
  protected $contractsWorker;
  /** @var OrdersWorker */
  protected $ordersWorker;
  /** @var UserCollector */
  protected $userCollector;
  private $userData;
  protected $userId; // userId – соответствует id на сайте.
  protected $userRole; // userRole – соответствует роли на сайте
  protected $userArea;
  protected $firstName; // firstName – забираем на сайте.
  protected $lastName; // lastName - забираем на сайте.
  protected $idPoint; // idPonts (array) – зависит от роли метод получения. По умолчанию пуст. ПОЛНОСТЬЮ ПЕРЕРАБОТАНО ВЗАИМОДЕЙСТВИЕ.
  protected $allPontsID;
  protected $statusActivity; // active - забираем из таблицы Users.
  protected $idReplacement; // idReplacement - забираем из таблицы Users, кого заменяет данный юзер.
  protected $idReplacement2; // idReplacement2 - забираем из таблицы Users, кого заменяет данный юзер.
  protected $unreadedMessages; // Получаем Информацию о всех непрочитанных сообщениях.
  protected $unreadedDialogues; // Получаем Информацию о всех непрочитанных dialogues.
  protected $myDialogues;
  protected $potencialReplacement; // Все наши потенциальные замены
  protected $myCoWorkers;
  protected $userControler; //Присваивается при создании в контролере
  protected $idWebsocket; //Присваивается при создании в контролере
  protected $rank; // rank – ранг контрагента.
  protected $parametersForClient;
  protected $emailNotification;
  /** @var Container */
  protected $Container;
  protected $connected;
  public function __construct($idUser, $Container)
  {
    $where = "UserMain";
    $this->initializationWPDB();
    $this->inicializationContracts();
    // $this->bindSystem($closures);
    $this->Container = &$Container;
    $this->UserUtilities = $this->Container->get('UserUtilities');
    $this->DBWorker = $this->Container->get('DBWorker');
    $this->UPWorker = $this->Container->get('UPWorker');
    $this->DBUtilities = $this->Container->get('DBUtilities');
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
    $this->DialogServices = $this->Container->get('DialogServices');
    $this->DataUtilities = $this->Container->get('DataUtilities');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
    $this->ControlerObjectsRelationship = $this->Container->get('ControlerObjectsRelationship');
    $this->MessageWorker = $this->Container->get('MessageWorker');
    $this->InvoiceWorker = $this->Container->get('InvoiceWorker');
    $this->ordersWorker = $this->Container->get('OrdersWorker');
    $this->contractsWorker = $this->Container->get('ContractsWorker');
    $this->userCollector = $this->Container->get('UserCollector');
    
    $this->userId = $idUser;
    $this->userData = $this->UserUtilities->getUserData($this->userId);
    $this->firstName = $this->UserUtilities->extractUserParam($this->userData, 'first_name', $this->userId, $where);
    $this->lastName = $this->UserUtilities->extractUserParam($this->userData, 'last_name', $this->userId, $where);
    // update_user_meta($this->userId, 'email_notification', true);
    $this->emailNotification = boolval($this->UserUtilities->extractUserParam($this->userData, 'email_notification', $this->userId, $where));
    $this->userRole = $this->UserUtilities->getUserRole($this->userId);
    $this->userArea = $this->UserUtilities->areaVerification($this->userRole);
    $this->statusActivity = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'status_activity');
    // $this->idReplacement = $this->getIdReplacement($this->userId);
    $this->myDialogues = $this->DBWorker->selectResultsFromDBU('gi_new_participants_dialog', 'id_participant', $this->userId, 'id_dialog');
    $this->idReplacement2 = $this->DBWorker->selectResultsFromDBU('gi_new_users', 'id_replacement', $this->userId, 'id_user');
    $this->rank = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'user_rank'); //Простое число.
    $this->connected = !empty($this->rank) ? true : false;
  }

  public function refreshDialogues()
  {
    $this->myDialogues = $this->getMyDialoguesDB($this->userId);
    $this->unreadedMessages = $this->getUnreadedMessagesDB($this->userId);
    $this->unreadedDialogues = $this->getUnreadedDialoguesDB($this->unreadedMessages);
  }

  protected function getStatusDB($idUser)
  {
    $sqlStatus = $this->wpdb->get_var("SELECT status_activity FROM gi_new_users WHERE id_user = '$idUser'");
    return $sqlStatus;
  }
  //Определяем роль юзера.
  private function getAllDialoguesUserDB($idUser)
  {
    $pathParticipiantsDialog = $this->DBWorker->getPathTable('ParticipantsDialog');
    $idDialogues = $this->DBWorker->selectResultsFromDBU_2($pathParticipiantsDialog, ['id_participant', 'state'], ['id_participant' => $idUser, 'state' => 1], 'id_dialog');
    return $idDialogues;
  }
  protected function changeUserInUreadedLog($idUnreadedMessages, $idUser)
  {
    if (!empty($idUnreadedMessages) && !empty($idUser)) {
      // $this->CatcherBugs->convPrintLog($idUnreadedMessages, 'addBackMessagesUser', '$idUnrededMessages');
      $whereSqlUpdate = $this->DBUtilities->createConditionQueryIN('id', $idUnreadedMessages);
      $setSqlUpdate = $this->DBUtilities->preparenSingleOperSepar('id_user', $idUser, ' = ', '');
      $valuesEnable = $whereSqlUpdate['values'];
      $valuesEnable = array_merge(array_values($setSqlUpdate['values']), $valuesEnable);

      if (!empty($whereSqlUpdate['sql'])) {
        $table = $this->DBWorker->getPathTable('LogUnreadedMessages');
        $this->DBWorker->updateDB($table, $whereSqlUpdate['sql'], $setSqlUpdate['sql'], $valuesEnable);
      }
    }
  }
  protected function getPotencialReplacementServer($users, $role, $userId)
  {
    $potencialReplacement = [];
    if (is_array($users)) {
      foreach ($users as $idUser => $value) {
        if (intval($this->userId) !== intval($idUser)) {
          $checkRole = $users[$idUser]['role'] === $role;
          $checkId = $idUser !== $userId;
          $checkStatus = $users[$idUser]['statusActivity'] === 'active';
          if ($checkRole && $checkId && $checkStatus) {
            $potencialReplacement[] = $this->UserUtilities->packegeUserForClient($users, $idUser);
          }
        }

      }
      if (empty($potencialReplacement)) {
        foreach ($users as $idUser => $value) {
          if (intval($this->userId) !== intval($idUser)) {
            $checkId = $idUser !== $userId;
            $checkStatus = $users[$idUser]['statusActivity'] === 'active';
            $checkRole = $users[$idUser]['role'] === 'manager';
            $checkFactoryWorker = $role === 'consultant' || $role === 'complaint_handler';
            if ($checkFactoryWorker && $checkRole && $checkId && $checkStatus) {
              $potencialReplacement[] = $this->UserUtilities->packegeUserForClient($users, $idUser);
            }
          }
        }
      }
    } else {
      // Обработка случая, когда $users не является массивом
      error_log('Warning: $users is not an array.');
    }
    return $potencialReplacement;
  }
  protected function handalingUnreadedMessageReplacement($idUser, $hisReplacementId)
  {
    $unreadedMessages = $this->getUnreadedMessagesDB($idUser); //Забираем все сообщения кого заменяем.
    // $indexedArray = $this->DataUtilities->convertToIndexArr($unreadedMessages);
    $unreadedDialogues = $this->DataUtilities->getFromArrByKeyU($unreadedMessages, 'idDialog');
    $idUnreadedLog = $this->DataUtilities->getFromArrByKeyU($unreadedMessages, 'id');
    $this->changeUserInUreadedLog($idUnreadedLog, $hisReplacementId);
    $unreadedDialogues = $this->SimpleUtilities->toArray($unreadedDialogues);
    return array_unique($unreadedDialogues);
  }
  protected function updateStatusUser($idUser, $status)
  {
    $user = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'id_user');
    if (!empty($user)) {
      // $this->wpdb->query("UPDATE gi_new_users SET status_activity = '$status' WHERE id_user = $idUser");
      $this->DBWorker->updateDBU('gi_new_users', 'id_user', $idUser, 'status_activity', $status);
    } else {
      // $userData = $this->UserUtilities->getUserData($idUser);
      $userName = $this->UserUtilities->getUserMeta($idUser, 'first_name', true);
      $userLastName = $this->UserUtilities->getUserMeta($idUser, 'last_name', true);
      $userFullName = $userName . ' ' . $userLastName;
      $userRank = $this->UserUtilities->defineUserRank($idUser);
      // $this->CatcherBugs->convPrintLog($userName, 'updateStatusUser', '$userName');
      // $this->CatcherBugs->convPrintLog($userLastName, 'updateStatusUser', '$userLastName');
      // $this->CatcherBugs->convPrintLog($userRank, 'updateStatusUser', '$userRank');
      $this->DBWorker->insertDBU('gi_new_users', [$idUser, $userFullName, '', '', '', '', $status, $userRank]);
      $user = $this->DBWorker->selectSimple('Users', 'id_user', $idUser, 'id_user');
      // $this->CatcherBugs->convPrintLog($user, 'updateStatusUser', '$user');
    }
  }
  protected function addReplacementDialogues($idWorker, $hisReplacementId)
  {
    // $dialoguesWorker = $this->getAllDialoguesUserDB($idWorker);

    $conditions = $this->DBUtilities->prepareEqualAndEqual($idWorker, 1, 'id_participant', 'state');
    $idDialogsWorker = $this->DBWorker->selectUni('ParticipantsDialog', $conditions, 'id_dialog');
    foreach ($idDialogsWorker as $idDialog) {
      $this->wpdb->query("INSERT INTO gi_new_participants_dialog 
      (id_dialog, id_participant) 
      VALUES ('$idDialog', '$hisReplacementId')");
    }
  }
  protected function addReplacementActiveDialogues($idWorker, $hisReplacementId, $idDialogues)
  {
    $table = $this->DBWorker->getPathTable('ParticipantsDialog');
    $idDialogues = $this->SimpleUtilities->toArray($idDialogues);
    foreach ($idDialogues as $idDialog) {
      $participiants = $this->DBWorker->selectResultsFromDBU_2($table, 'id_dialog', $idDialog, 'id_participant');
      // $this->CatcherBugs->convPrintLog($participiants, 'addReplacementActiveDialogues', 'participiants', 1);
      $check = is_array($participiants) ? in_array($hisReplacementId, $participiants) : $participiants == $hisReplacementId;
      if ($check) {
        $this->DialogServices->changeUserStateInDialogs($hisReplacementId, 1);
      } else {
        $this->DBWorker->insertDBU_2($table, [$idDialog, $hisReplacementId, 1]);
      }
    }
  }
  protected function removeUnreadedMessagesUser($idUser)
  {
    $unreadedMessages = $this->getUnreadedMessagesDB($idUser);

    foreach ($unreadedMessages as $message) {
      $idDialog = $message['id_dialog'];
      $idMessage = $message['idMessage'];
      $this->wpdb->query("DELETE FROM gi_new_log_unreaded_messages WHERE id_dialog = '$idDialog' AND id_message = '$idMessage' AND id_user = '$idUser'");
    }
  }
  protected function removeUserDialogues($idUser)
  {
    $idDialogues = $this->getAllDialoguesUserDB($idUser);
    foreach ($idDialogues as $dialog) {
      $idDialog = $dialog['idDialog'];
      $this->wpdb->query("DELETE FROM gi_new_participants_dialog WHERE id_dialog = '$idDialog' AND id_participant = '$idUser'");
    }
    return $idDialogues;
  }
  protected function changeResponsibleDialogsDB($idWorker, $hisReplacementId)
  {
    $idUnreadedDialogues = $this->handalingUnreadedMessageReplacement($idWorker, $hisReplacementId);
    // $this->CatcherBugs->convPrintLog($idUnreadedDialogues, 'changeResponsibleDialogsDB', '$idUnreadedDialogues');
    $this->addReplacementActiveDialogues($idWorker, $hisReplacementId, $idUnreadedDialogues);
    // $idDialogues = $this->removeUserDialogues($idWorker);
    // $idDialogues = $this->getAllDialoguesUserDB($idWorker);
    $idDialogues = $this->getAllDialoguesUserDB($idWorker);
    // $this->CatcherBugs->convPrintLog($idDialogues, 'changeResponsibleDialogsDB', '$idDialogues');
    // $this->removeUnreadedMessagesUser($idWorker);
    $this->DialogServices->changeUserStateInDialogs($idWorker, 0);
    return $idDialogues;
  }

  // protected function changeResponsibleDialogsDB_OLD($idWorker, $hisReplacementId)
  // {
  //   $this->addUnreadedMessageReplacement($idWorker, $hisReplacementId);
  //   $this->addReplacementDialogues($idWorker, $hisReplacementId);
  //   $this->removeUnreadedMessagesUser($idWorker);
  //   $idDialogues = $this->removeUserDialogues($idWorker);
  //   return $idDialogues;
  // }
  protected function getUnreadedMessagesDB($idUser)
  {
    $conditions = $this->DBUtilities->preparenSingleOperSepar('id_user', $idUser, ' = ', '');
    $unreadedMessages = $this->DBWorker->selectUni('LogUnreadedMessages', $conditions);
    return $unreadedMessages;
  }
  // Получаем все непрочитаные диалоги.
  protected function getUnreadedDialoguesDB($unreadedMessages)
  {
    if (!empty($unreadedMessages)) {
      $unreadedDialogFromLog = $this->DataUtilities->getFromArrByKeyU($unreadedMessages, 'idDialog');
      // $this->CatcherBugs->convPrintLog($unreadedDialogFromLog,'getUnreadedDialoguesDB', '$unreadedDialogFromLog');
      $unreadedDialog = is_array($unreadedDialogFromLog) ? implode(',', $unreadedDialogFromLog) : $unreadedDialogFromLog;
      $sqlUnreadedDialogues = $this->wpdb->get_results("SELECT * FROM gi_new_dialogues WHERE id_dialog IN ($unreadedDialog)");
      return $sqlUnreadedDialogues;
    } else {
      return null;
    }
  }

  protected function getUnreadedDialoguesDB_NEW($unreadedMessages)
  {
    if (!empty($unreadedMessages)) {
      $unreadedDialogFromLog = $this->DataUtilities->getFromArrByKeyU($unreadedMessages, 'idDialog');
      $conditions = $this->DBUtilities->createConditionQueryIN('id_dialog', $unreadedDialogFromLog);
      $sqlUnreadedDialogues = $this->DBWorker->selectUni('Dialogues', $conditions);
      return $sqlUnreadedDialogues;
    } else {
      return null;
    }
  }

  protected function getMyDialoguesDB($idUser)
  {
    $myDialogues = $this->DBWorker->selectResultsFromDBU('gi_new_participants_dialog', 'id_participant', $idUser, 'id_dialog');
    if (!empty($myDialogues)) {
      $myDialoguesString = implode(',', $myDialogues);
      $sqlUnreadedDialogues = $this->wpdb->get_results("SELECT * FROM gi_new_dialogues WHERE id_dialog IN ($myDialoguesString)");
      return $sqlUnreadedDialogues;
    } else {
      return null;
    }
  }
  // messageReaded - Отметить, как прочитанное для него, но у всех остальных участников диалога с его стороны сообщения остаются "Не прочитаны". 
  protected function messageReaded($idDialog, $idMessage, $idUser)
  {
    $this->wpdb->query("DELETE FROM gi_new_log_unreaded_messages WHERE id_dialog = '$idDialog' AND id_message = '$idMessage' AND id_user = '$idUser'");
  }
  // shutDownDialog - Возможность отключения от диалога, после момента просмотра сообщения, направленного ему.  
  protected function shutDownDialog($idDialog, $userId)
  {
    $this->wpdb->query("DELETE FROM participants_dialog WHERE id_dialog = '$idDialog' AND id_participant = '$userId'");
  }

  public function addUsersControler($usersControler, $link)
  {
    $this->setProperty($usersControler, $link);
  }
  protected function prepareParametrForClientUM()
  {
    $data = [
      'idUser' => $this->userId,
      'firstname' => $this->firstName,
      'lastname' => $this->lastName,
      'role' => $this->userRole,
      'statusActivity' => $this->statusActivity,
      'whose' => $this->UserUtilities->partyVerification($this->userRole),
      'myCoWorkers' => $this->myCoWorkers,
      'idPoint' => $this->idPoint,
      'potencialReplacement' => $this->potencialReplacement,
      'emailNotification' => $this->emailNotification,
      'connected' => $this->connected,
    ];
    if ($this->userRole === 'administrator' || $this->userRole === 'sales_manager') {
      $allPoints = $this->DBWorker->selectResultsFromDBU_2('gi_new_points', 'state', 1);
      // $this->CatcherBugs->convPrintLog($allPoints, 'prepareParametrForClientUM', '$allPoints', 2);
      $data['allPoints'] = $this->SimpleUtilities->sortedByAlphabet($allPoints, 'namePoint');
    }

    // $this->CatcherBugs->convPrintLog($data, 'prepareParametrForClientUM', '$data');
    return $data;
  }
  public function addControlerObjectRelationship($ControlerObjectsRelationship, $link)
  {
    $this->setProperty($ControlerObjectsRelationship, $link);
  }
  public function outsideIdPoints()
  {
    return $this->idPoint;
  }
  public function setIdWebsocket($idWebsocket)
  {
    $idWebsocket = is_array($idWebsocket) ? $idWebsocket : [$idWebsocket];
    $this->idWebsocket = $idWebsocket;
  }
  public function addWebsocketId($idWebsocket)
  {
    $this->idWebsocket[] = $idWebsocket;
  }
  public function outsideWebsocketId()
  {
    $websocketsId = &$this->idWebsocket;
    return $websocketsId;
  }
  public function outsideUserRank(): string
  {
      return $this->rank;
  }

  public function changeResponsibleDialogs($idWorker, $hisReplacementId)
  {
    $this->DBWorker->updateDBU('gi_new_users', 'id_user', $idWorker, 'id_replacement', $hisReplacementId);
    $idDialogues = $this->changeResponsibleDialogsDB($idWorker, $hisReplacementId);
    // $this->CatcherBugs->convPrintLog($idDialogues, 'changeResponsibleDialogs', '$idDialogues');
    return $idDialogues;
  }

  public function updateStatus()
  {
    $this->statusActivity = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'status_activity');
  }
  public function updateReplacement()
  {
    // $this->idReplacement = $this->getIdReplacement($this->userId);
    $this->idReplacement2 = $this->DBWorker->selectVarFromDBU('gi_new_users', 'id_replacement', $this->userId, 'id_user');
  }
  public function changeStatusUserUM($userId, $statusActivity)
  {
    // $this->CatcherBugs->convPrintLog($statusActivity, 'changeStatusUserUM', '$statusActivity');
    $this->updateStatusUser($userId, $statusActivity);
  }
  // ПЕРЕПИСАТЬ
  public function updateForClientStatusWorkers()
  {

  }
  public function deleteReplacementUM($userId)
  {
    $this->DBWorker->updateDBU('gi_new_users', 'id_user', $userId, 'id_replacement', '');
  }
  public function outsideParamForClient()
  {
    return $this->prepareParametrForClientUM();
  }
  //This is a shell. This method determines in child classes: Contractor, Dealer, Distributor, FactoryWorker, FreeDealer
  public function updateAfterBindUser()
  {
  }

  public function setEmailNotification($value)
  {
    $this->emailNotification = is_bool($value) ? $value : boolval($value);
    update_user_meta($this->userId, 'email_notification', $this->emailNotification);
    return $this->prepareParametrForClientUM();
  }


  public function updatePoints()
  {
  }

  public function bindOnMyOwn()
  {
    // Default implementation that can be overridden by child classes
    return false;
  }
  public function showInvoice($serialNumber)
  {

  }

  public function showInvoices()
  {

  }

  public function showContract($serialNumber)
  {

  }

  public function showContracts()
  {

  }
  public function getAllPointsId()
  {
    return $this->allPontsID;
  }
  public function writeNewMessage($newMessage)
  {
    // $this->DialogServices->checkFactoryWorkerInDialog($newMessage['typeDialog'], $newMessage['idDialog'], $newMessage['idPoint']);
    $condition = $this->DBUtilities->prepareEqualAndEqual($newMessage->idDialog, $newMessage->serialNumber, 'id_dialog', 'sn');
    $dialog = $this->DBWorker->selectUni('Dialogues', $condition);
    $contract = $this->DBWorker->selectSimple('Contract', 'sn', $newMessage->serialNumber);
    $idParticipants = $this->DialogServices->getDialogParticipant($newMessage->idDialog, 1);

    $this->DialogServices->checkFactoryWorkerInDialog($newMessage->typeDialog, $newMessage->idDialog, $contract['idPoint']);
    $this->DialogServices->checkContractor($idParticipants, $dialog['idCreator'], $dialog['idDialog']);
    $newMessage = $this->MessageWorker->workWithNewMessage($newMessage, $contract);


    $this->DBWorker->updateDBU('gi_new_dialogues', 'id_dialog', $newMessage->idDialog, 'date_last_activity', $newMessage->dateSend);
    $this->DBWorker->updateDBU('gi_new_contract', 'sn', $newMessage->serialNumber, 'date_last_activity', $newMessage->dateSend);

    return $newMessage;
  }
}