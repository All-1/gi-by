<?php
namespace PersonalAccount\Utilities;
use PersonalAccount\Core\Container;
use PersonalAccount\Factory\Factory;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;

use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\Utilit;

class DialogServices
{
  // use DependencyInjections;
  /** @var Container */
  protected $Container;
  /** @var DBWorker */
  protected $DBWorker;
  /** @var DBUtilities */
  protected $DBUtilities;
  /** @var UserUtilities */
  protected $UserUtilities;
  /** @var SimpleUtilities */
  protected $SimpleUtilities;
  /** @var Factory */
  protected $Factory;
  /** @var CatcherBugs */
  protected $CatcherBugs;
  use Utilit;
  public function __construct($Container)
  {
    $this->Container = &$Container;
    $this->DBUtilities = $this->Container->get('DBUtilities');
    $this->DBWorker = $this->Container->get('DBWorker');
    $this->UserUtilities = $this->Container->get('UserUtilities');
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
    $this->Factory = $this->Container->get('Factory');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
  }
  public function defineTypeOR($typeDialog)
  {
    switch ($typeDialog) {
      case "Consultation":
      case "Order":
      case "Complaint":
        $typeOR = 'contract';
        break;
      case "Shipment":
        $typeOR = 'shipment';
        break;
      case "Invoice":
        $typeOR = 'invoice';
        break;
    }
    return $typeOR;
  }
  public function typeDialogLatToCyr($typeDialog)
  {
    switch ($typeDialog) {
      case "Consultation":
        $typeDialogCyr = 'Консультация';
        break;
      case "Order":
        $typeDialogCyr = 'Заказ';
        break;
      case "Complaint":
        $typeDialogCyr = 'Рекламация';
        break;
      case "Shipment":
        $typeDialogCyr = 'Отгрузка';
        break;
      case "Invoice":
        $typeDialogCyr = 'Счёт';
        break;
    }
    return $typeDialogCyr;
  }
  protected function checkEngageUserDialog($idUser, $idDialog)
  {
    $conditions = $this->DBUtilities->prepareEqualAndEqual($idUser, $idDialog, 'id_participant', 'id_dialog');
    $result = $this->DBWorker->selectUni('ParticipantsDialog', $conditions);
    return $result;
  }
  public function changeStateUserDialog($idDialog, $idUser, $state)
  {
    $tableParticipant = $this->DBWorker->getPathTable('ParticipantsDialog');
    $whereSqlUpdate = $this->DBUtilities->prepareEqualAndEqual($idUser, $idDialog, 'id_participant', 'id_dialog');
    $setSqlUpdate = $this->DBUtilities->preparenSingleOperSepar('state', $state, ' = ', '');
    $valuesEnable = $whereSqlUpdate['values'];
    $valuesEnable = array_merge(array_values($setSqlUpdate['values']), $valuesEnable);
    $this->DBWorker->updateDB($tableParticipant, $whereSqlUpdate['sql'], $setSqlUpdate['sql'], $valuesEnable);
  }
  protected function choiceFactoryWorkerByRole($role)
  {
    $users = get_users(array('role' => $role, 'fields' => 'ID'));
    $comparedUsers = [];
    if (!empty($users)) {
      foreach ($users as $userId) {
        $conditions = $this->DBUtilities->preparenSingleOperSepar('id_user', $userId, ' = ', '');
        $status = $this->DBWorker->selectVarUni('Users', $conditions, 'status_activity');
        if ($status === 'active') {
          $conditions = $this->DBUtilities->prepareEqualAndEqual($userId, 1, 'id_participant', 'state');
          $idDialogues = $this->DBWorker->selectUni('ParticipantDialog', $conditions, 'id_dialog');
          if (!empty($idDialogues)) {
            $conditions = $this->DBUtilities->prepareEqualAndEqual($idDialogues, 'yes', 'id_dialog', 'status_activity');
            $idActiveDialogue = $this->DBWorker->selectUni('Dialogues', $conditions, 'id_dialog');
            $comparedUsers[$userId] = !empty($idDialogues) ? count($idActiveDialogue) : 0;
          } else {
            $comparedUsers[$userId] = 0;
          }
        }
      }
      if (!empty($comparedUsers)) {
        $minUserId = null;
        $minDialogCount = PHP_INT_MAX;
        foreach ($comparedUsers as $userId => $dialogCount) {
          if ($dialogCount < $minDialogCount) {
            $minUserId = $userId;
            $minDialogCount = $dialogCount;
          }
        }
        if ($minUserId) {
          return $minUserId;
        } else {
          return $users[0];
        }
      }
    }
    return null;
  }
  public function choiseManagerCurrentDialog($type, $idUser) //
  {
    switch ($type) {
      case 'Order':
        $idManager = $idUser;
        break;
      case 'Consultation':
        $idManager = $this->choiceFactoryWorkerByRole('consultant');
        break;
      case 'Complaint':
        $idManager = $this->choiceFactoryWorkerByRole('complaint_handler');
        break;
      case 'Invoice':
        $idManager = $this->choiceFactoryWorkerByRole('bookkeeper');
        break;
      case 'Shipment':
        $idManager = $this->choiceFactoryWorkerByRole('shipment_manager');
        break;
    }
    if (!$idManager) {
      return $idUser;
    } else {
      return $idManager;
    }
  }
  public function addParticipant($idParticipant, $idDialog)
  {
    $query = $this->DBUtilities->prepareEqualAndEqual($idParticipant, 'active', 'id_user', 'status_activity');
    $checkStatusActivity = $this->DBWorker->selectUni('Users', $query);
    // $this->CatcherBugs->convPrintLog($checkStatusActivity, 'addParticipant', '$checkStatusActivity');
    if (!empty($checkStatusActivity)) {
      $table = $this->DBWorker->getPathTable('ParticipantsDialog');
      $checkEngage = $this->checkEngageUserDialog($idParticipant, $idDialog);
      // $this->CatcherBugs->convPrintLog($idParticipant, 'addParticipant', '$idParticipant');
      // $this->CatcherBugs->convPrintLog($checkEngage, 'addParticipant', '$checkEngage');
      if (!empty($checkEngage)) {
        $this->changeStateUserDialog($idDialog, $idParticipant, 1);
      } else {
        $this->DBWorker->insertDBU_2($table, [$idDialog, $idParticipant, 1]);
      }
      return true;
    }
    return false;
  }
  public function changeUserStateInDialogs($idUser, $state = 0)
  {
    if (!empty($idUser)) {
      $whereSqlUpdate = $this->DBUtilities->preparenSingleOperSepar('id_participant', $idUser, ' = ', '');
      $setSqlUpdate = $this->DBUtilities->preparenSingleOperSepar('state', $state, ' = ', '');
      $valuesEnable = $whereSqlUpdate['values'];
      $valuesEnable = array_merge(array_values($setSqlUpdate['values']), $valuesEnable);
      $pathParticipiantsDialog = $this->DBWorker->getPathTable('ParticipantsDialog');
      $result = $this->DBWorker->updateDB($pathParticipiantsDialog, $whereSqlUpdate['sql'], $setSqlUpdate['sql'], $valuesEnable);
      return $result;
    }
  }
  
  public function getDialogParticipant($idDialog, $state = null)
  {
   
    $conditions = $state !== null
      ? $this->DBUtilities->prepareEqualAndEqual($state, $idDialog, 'state', 'id_dialog')
      : $this->DBUtilities->preparenSingleOperSepar('id_dialog', $idDialog, ' = ', '');
    $idParticipant = $this->DBWorker->selectUni('ParticipantsDialog', $conditions, 'id_participant');
    return $idParticipant;
  }
  public function checkFactoryWorkerInDialog($typeDialog, $idDialog, $idPoint)
  {
    $flag = false;
    $idParticipant = $this->getDialogParticipant($idDialog, 1);
    $idParticipant = $this->SimpleUtilities->toArray($idParticipant);
    // $this->CatcherBugs->convPrintLog($idParticipant,'checkFactoryWorker', '$idParticipant');
    foreach ($idParticipant as $idUser) {
      if (!empty($this->UserUtilities)) {
        // $this->CatcherBugs->convPrintLog($idUser,'checkFactoryWorker', '$idUser');
        $whose = $this->UserUtilities->checkUserAccess($idUser);
        if ($whose === 'factory_worker') {
          $flag = true;
          break;
        }
      }
    }
    if (!$flag) {
      $idManagerInPoint = $this->UserUtilities->getManagerForPoint($idPoint);
      $idFactoryWorker = $this->choiseManagerCurrentDialog($typeDialog, $idManagerInPoint);
      // $this->CatcherBugs->convPrintLog($idFactoryWorker, 'checkFactoryWorker', '$idFactoryWorker');
      $this->addParticipant($idFactoryWorker, $idDialog);
    }
  }

  public function checkContractor($idParticipants, $idCreator, $idDialog)
  {
    $checkContractor = $this->checkParticipantInDialog($idParticipants, $idDialog, 'contractor');
    if (!$checkContractor) {
      $this->addContractorEmptyDialog($idCreator, $idDialog);
    }
  }
  private function checkParticipantInDialog($idParticipants, $idDialog, $party)
  {
    $idCurrentParticipiant = $this->checkActivePart($idParticipants, $party, 'active');
    // $this->CatcherBugs->convPrintLog($idCurrentParticipiant, 'checkParticipantInDialog', 'idCurrentParticipiant', 1);
    if (!$idCurrentParticipiant) {
      $idPrevParticipantActive = $this->checkPrevParticipant($idDialog, $party, 'active');
      // $this->CatcherBugs->convPrintLog($idPrevParticipantActive, 'checkParticipantInDialog', 'idPrevParticipantActive', 2);
      if (!empty($idPrevParticipantActive)) {
        $this->changeUserStateInDialogs($idPrevParticipantActive, 1);
        return true;
      } else {
        $idPrevParticipantNoactive = $this->checkPrevParticipant($idDialog, $party, 'noactive');
        if ($idPrevParticipantNoactive) {
          $idReplacementForNoactive = $this->UserUtilities->getUserReplacement($idPrevParticipantNoactive);
          if ($idReplacementForNoactive) {
            return $this->addParticipant($idReplacementForNoactive, $idDialog);
          }
        } else {
          $idPrevParticipantBlocked = $this->checkPrevParticipant($idDialog, $party, 'blocked');
          if ($idPrevParticipantBlocked) {
            $idReplacementForBlocked = $this->UserUtilities->getUserReplacement($idPrevParticipantNoactive);
            if ($idReplacementForBlocked) {
              return $this->addParticipant($idReplacementForBlocked, $idDialog);
            }
          }
        }
      }
    } else {
      return true;
    }
  }
  private function checkActivePart($idParticipants, $party, $activity)
  {
    $idParticipants = $this->SimpleUtilities->toArray($idParticipants);
    foreach ($idParticipants as $idUser) {
      $userRole = $this->UserUtilities->getUserRole($idUser);
      $whose = $this->UserUtilities->partyVerification($userRole);
      $statusActivity = $this->DBWorker->selectVarSimple('Users', 'id_user', $idUser, 'status_activity');
      if ($whose === $party && $statusActivity === $activity) {
        return $idUser;
      }
    }
    return null;
  }
  private function checkPrevParticipant($idDialog, $party, $activity)
  {
    $idParticipiant = $this->getDialogParticipant($idDialog, 0);
    $checkState = $this->checkActivePart($idParticipiant, $party, $activity);
    return $checkState;
  }
  private function addContractorEmptyDialog($idContractor, $idDialog)
  {
    $statusActivity = $this->DBWorker->selectVarSimple('Users', 'id_user', $idContractor, 'status_activity');
    if ($statusActivity === 'active') {
      $this->wpdb->query("INSERT INTO gi_new_participants_dialog 
      (id_dialog, id_participant) 
      VALUES ('$idDialog, '$idContractor')");
      return true;
    } else {
      $currentUserContractor = $this->Factory->createUser($idContractor, $this->Container);
      $idContractorHigherRank = $currentUserContractor->outsideContractorHigherRank();
      unset($currentUserContractor);
      if ($idContractorHigherRank) {
        $this->addContractorEmptyDialog($idContractorHigherRank, $idDialog);
      } else {
        return false;
      }
    }
  }
}