<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Core\Container;
use PersonalAccount\factory\Factory;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Dialog\traits\WorkWithMessages;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\DialogServices;
use DateTime;
class Dialog
{
  // use DependencyInjections;
  use Utilit; //Содержаться все функции которые имеют к базе данных, но нужны в разных объектах, а так же общая функция для создания капсулы
  use workWithMessages;
  // use workWithDialog;
  // use catcherBugsTemporary;
  /** @var Container */
  protected $container;
  /** @var Factory */
  protected $factory;
  /** @var UserUtilities */
  protected $userUtilities;
  /** @var DBWorker */
  protected $dbWorker;
  /** @var DataUtilities */
  protected $dataUtilities;
  /** @var SimpleUtilities */
  protected $simpleUtilities;
  /** @var DBUtilities */
  protected $dbUtilities;
  /** @var DialogServices */
  protected $dialogServices;
  /** @var CatcherBugs */
  protected $catcherBugs;
  protected $idDialog; // IdDialog (unic) – Вне зависимости от типа диалога.
  protected $serialNumber; // serialNumber – присваивается в момент создания диалога и зависит от типа дочернего объекта.
  protected $idCreator; // idCreator – присуждается при создание диалога.
  protected $typeDialog; // typeDialog – присуждается в момент создания дочернего объекта
  protected $dateLastActivity; // dateLastActivity – присваивается в соответствии с последним сообщением.
  protected $dateLastActivityTimestamp;
  protected $statusActivity; // statusActivity - при создании устанавливается на active. Если в диалоге нет сообщений со статусом unreadead меняем на “no active”
  protected $idParticipant; // idParcipiant (Array) - Неограниченное количество участников.
  protected $usersHaveAccess;
  protected $objectRelationship; //Ссылка на объект которому принадлежит данный диалог.
  protected $year;
  protected $pathTableDB;

  public function __construct($dialog, $container, $parent)
  {
    // $this->bindSystem($closures);
    // $closures['Dialog'] = &$this;
    $this->container = &$container;
    $this->objectRelationship = &$parent;
    $this->dbWorker = $this->container->get('DBWorker');
    $this->dataUtilities = $this->container->get('DataUtilities');
    $this->dbUtilities = $this->container->get('DBUtilities');
    $this->factory = $this->container->get('Factory');
    $this->dialogServices = $this->container->get('DialogServices');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->catcherBugs = $this->container->get('CatcherBugs');
    $this->initializationWPDB();
    $this->idDialog = $dialog['idDialog'];
    $this->serialNumber = $dialog['serialNumber'];
    $this->idCreator = $dialog['idCreator'];
    $this->typeDialog = $dialog['typeDialog'];
    $this->dateLastActivity = $dialog['dateLastActivity'];
    $this->dateLastActivityTimestamp = $dialog['dateLastActivityTimestamp'];
    $this->statusActivity = $dialog['statusActivity'];
    $this->idParticipant = $this->dialogServices->getDialogParticipant($this->idDialog, 1);
    $this->year = $this->detectionYear();
  }
  // protected function getDialogParticipant($idDialog, $state = null)
  // {
  //   $conditions = $state !== null
  //     ? $this->dbUtilities->prepareEqualAndEqual($state, $idDialog, 'state', 'id_dialog')
  //     : $this->dbUtilities->preparenSingleOperSepar('id_dialog', $idDialog, ' = ', '');
  //   $idParticipant = $this->dbWorker->selectUni('ParticipantsDialog', $conditions, 'id_participant');
  //   return $idParticipant;
  // }
  // protected function checkStateInDialog($idUser, $state)
  // {
  //   $conditions = $this->dbUtilities->prepareEqualAndEqual($state, $idUser, 'state', 'id_paticipiant');
  //   $result = $this->dbWorker->selectUni('ParticipantsDialog', $conditions);
  //   return $result;
  // }
  // private function getDialogParticipantOLD()
  // {
  //   $idParticipants = $this->wpdb->get_results("SELECT id_participant FROM gi_new_participants_dialog WHERE id_dialog = '$this->idDialog' AND `state` = 1");
  //   $participiantsDialog = [];
  //   foreach ($idParticipants as $participiant) {
  //     $participiantsDialog[] = $participiant->id_participant;
  //   }
  //   // $participiants = $this->getInfoAboutUsers($participiantsDialog);
  //   return $participiantsDialog;
  // }
  private function checkStatusDialog()
  {
    $sqlCheckUnreaded = $this->wpdb->get_results("SELECT * FROM gi_new_log_unreaded_messages WHERE id_dialog='$this->idDialog'");
    if (count($sqlCheckUnreaded) !== 0) {
      return 'yes';
    } else {
      return 'no';
    }
  }
  // addContractorEmptyDialog - Если dialog был покинут всеми контрагентами и в dialog было отправлено новое сообщение юзером фабрики, то мы разыскиваем idCreator и добавляем его в диалог, если idCreator “no active” или “blocked” мы ищем юзера среди контрагентов с рангом на 1 выше ранга данного юзера на idPoint и добавляем его в dialog, если он так же “no active”, то ищем пользователя с рангом выше на 1 чем у данного юзера на idPoint, если и такового нет отдаём на клиент сообщение о том, что система не смогла найти подходящего юзера.
  private function addContractorEmptyDialog($idContractor)
  {
    $statusActivity = $this->dbWorker->selectVarSimple('Users', 'id_user', $idContractor, 'status_activity');
    if ($statusActivity === 'active') {
      $this->wpdb->query("INSERT INTO gi_new_participants_dialog 
      (id_dialog, id_participant) 
      VALUES ('$this->idDialog', '$idContractor')");
      return true;
    } else {
      $currentUserContractor = $this->factory->createUser($idContractor, $this->container);
      $idContractorHigherRank = $currentUserContractor->outsideContractorHigherRank();
      unset($currentUserContractor);
      if ($idContractorHigherRank) {
        $this->addContractorEmptyDialog($idContractorHigherRank);
      } else {
        return false;
      }
    }
  }
  protected function checkParticipantInDialog($party)
  {
    $idCurrentParticipiant = $this->checkActivePart($this->idParticipant, $party, 'active');
    // $this->catcherBugs->convPrintLog($idCurrentParticipiant, 'checkParticipantInDialog', 'idCurrentParticipiant', 1);
    if (!$idCurrentParticipiant) {
      $idPrevParticipantActive = $this->checkPrevParticipant($this->idDialog, $party, 'active');
      // $this->catcherBugs->convPrintLog($idPrevParticipantActive, 'checkParticipantInDialog', 'idPrevParticipantActive', 2);
      if (!empty($idPrevParticipantActive)) {
        $this->dialogServices->changeUserStateInDialogs($idPrevParticipantActive, 1);
        return true;
      } else {
        $idPrevParticipantNoactive = $this->checkPrevParticipant($this->idDialog, $party, 'noactive');
        if ($idPrevParticipantNoactive) {
          $idReplacementForNoactive = $this->userUtilities->getUserReplacement($idPrevParticipantNoactive);
          if ($idReplacementForNoactive) {
            return $this->dialogServices->addParticipant($idReplacementForNoactive, $this->idDialog);
          }
        } else {
          $idPrevParticipantBlocked = $this->checkPrevParticipant($this->idDialog, $party, 'blocked');
          if ($idPrevParticipantBlocked) {
            $idReplacementForBlocked = $this->userUtilities->getUserReplacement($idPrevParticipantNoactive);
            if ($idReplacementForBlocked) {
              return $this->dialogServices->addParticipant($idReplacementForBlocked, $this->idDialog);
            }
          }
        }
      }
    } else {
      return true;
    }
  }

  protected function checkPrevParticipant($idDialog, $party, $activity)
  {
    $idParticipiant = $this->dialogServices->getDialogParticipant($idDialog, 0);
    $checkState = $this->checkActivePart($idParticipiant, $party, $activity);
    return $checkState;
  }
  protected function checkActivePart($idParticipants, $party, $activity)
  {
    $idParticipants = $this->simpleUtilities->toArray($idParticipants);
    foreach ($idParticipants as $idUser) {
      $userRole = $this->userUtilities->getUserRole($idUser);
      $whose = $this->userUtilities->partyVerification($userRole);
      $statusActivity = $this->dbWorker->selectVarSimple('Users', 'id_user', $idUser, 'status_activity');
      if ($whose === $party && $statusActivity === $activity) {
        return $idUser;
      }
    }
    return null;
  }
  protected function checkContractor()
  {
    $checkContractor = $this->checkParticipantInDialog('contractor');
    if (!$checkContractor) {
      $this->addContractorEmptyDialog($this->idCreator);
    }
  }
  protected function tableCreate()
  {
    $sql_create = "CREATE TABLE {$this->pathTableDB} (
      id INT NOT NULL AUTO_INCREMENT,
      id_dialog INT NOT NULL DEFAULT 0,
      id_message INT NOT NULL DEFAULT 0,
      id_author INT NOT NULL DEFAULT 0,
      message_body TEXT NOT NULL DEFAULT '',
      files TEXT NOT NULL DEFAULT '',
      date_send DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      date_readed DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_create);
  }
  // protected function checkContractorOLD_2()
  // {
  //   foreach ($this->idParticipant as $idUser) {
  //     $userRole = serviceUser::getUserRole($idUser);
  //     $userRoleContractors = ["designer_dealer", "dealer", "free_dealer", "distributor"];
  //     if (in_array($userRole, $userRoleContractors) && $this->getStatusActivityDB($idUser) === 'active') {
  //       if ($this->checkStateInDialog($idUser, 1)) {
  //         return true;
  //       } else {
  //         $this->changeUserStateInDialogs($idUser, 1);
  //         return true;
  //       }
  //     }
  //   }
  //   $checkAdd = $this->addContractorEmptyDialog($this->idCreator);
  //   if ($checkAdd) {
  //     return true;
  //   }
  //   return false;
  // }
  // protected function checkContractorOLD_3()
  // {

  //   foreach ($this->idParticipant as $idUser) {
  //     $userRole = serviceUser::getUserRole($idUser);
  //     $userRoleContractors = ["designer_dealer", "dealer", "free_dealer", "distributor"];
  //     if (in_array($userRole, $userRoleContractors)) {
  //       if ($this->getStatusActivityDB($idUser) === 'active') {
  //         if ($this->checkStateInDialog($idUser, 1)) {
  //           return true;
  //         } else {
  //           $this->changeUserStateInDialogs($idUser, 1);
  //           return true;
  //         }
  //       } else {
  //         $table = $this->dbWorker->getPathTable('Users');
  //         $this->dbWorker->selectResultsFromDBU_2($table, 'id_user', '');
  //       }
  //     }
  //   }

  //   $checkAdd = $this->addContractorEmptyDialog($this->idCreator);
  //   if ($checkAdd) {
  //     return true;
  //   }
  //   return false;
  // }
  // checkContractorDialog - Если участник диалога, который отвечал со стороны контрагента не активен и в диалоге нет других участников со стороны контрагента все сообщения в диалоге направляются на ранг выше автоматически.
  protected function checkContractorDialog()
  {
    foreach ($this->idParticipant as $idUser) {
      $userRole = $this->userUtilities->getUserRole($idUser);
      $userRoleContractors = ["designer_dealer", "dealer", "free_dealer", "distributor"];
      $statusActivity = $this->dbWorker->selectVarSimple('Users', 'id_user', $idUser, 'status_activity');
      if (in_array($userRole, $userRoleContractors) && $statusActivity === 'active') {
        return true;
      }
    }
    $checkAdd = $this->addContractorEmptyDialog($this->idCreator);
    if ($checkAdd) {
      return true;
    }
    return false;
  }
  // changeStatusDialog - Если все сообщения прочитаны в диалоге, его статус меняется на “no active”
  protected function changeStatusDialog()
  {
    if ($this->checkStatusDialog() !== $this->statusActivity) {
      $this->statusActivity = $this->checkStatusDialog();
      $this->wpdb->query("UPDATE gi_new_dialogues SET status_activity = '$this->statusActivity' WHERE id_dialog = '$this->idDialog'");
    }
  }
  protected function detectionYear()
  {
    $dateCreation = $this->objectRelationship->getDateCreation();
    $date = new DateTime($dateCreation);
    $year = $date->format('Y');
    return $year;
  }
  protected function createPathTableDB($type)
  {
    $pathTableDB = 'x_gi_new_messages_' . $type . '_' . $this->year;
    return $pathTableDB;
  }
  protected function deleteUnreadedMessages($idUser)
  {
    $tableLogUnreaded = $this->dbWorker->getPathTable('LogUnreadedMessages');
    $whereSqlDelete = $this->dbUtilities->prepareEqualAndEqual($this->idDialog, $idUser, 'id_dialog', 'id_user');
    $this->dbWorker->deleteComplexQueryDB($tableLogUnreaded, $whereSqlDelete['sql'], $whereSqlDelete['values']);
  }

  private function packageMessages()
  {
    if (!empty($this->messages)) {
      $packageMessages = [];
      /** @var Message $message */
      foreach ($this->messages as $message) {
        $packageMessages[] = $message->getDataMessageOutside();
      }
      return $packageMessages;
    }
  }

  public function getDataDialoguesForUser()
  {
    $dataDialogForUsers = [
      'idDialog' => $this->idDialog,
      'serialNumber' => $this->serialNumber,
      'idCreator' => $this->idCreator,
      'typeDialog' => $this->typeDialog,
      'dateLastActivity' => $this->dateLastActivity,
      'dateLastActivityTimestamp' => $this->dateLastActivityTimestamp,
      'statusActivity' => $this->statusActivity,
      'idParticipant' => $this->userUtilities->getInfoAboutUsers($this->idParticipant),
      'messages' => $this->packageMessages(),
      'pathTableDB' => $this->pathTableDB,
      // 'usersHaveAccess' => $this->usersHaveAccess,
    ];
    return $dataDialogForUsers;
  }
  public function shutDownAllMessages()
  {
      $this->messages = [];
  }
  protected function findParticipant($idUser)
  {
    $conditions = $this->dbUtilities->prepareEqualAndEqual($idUser, $this->idDialog, 'id_participant', 'id_dialog');
    $result = $this->dbWorker->selectUni('ParticipantsDialog', $conditions);
    return !empty($result) ? true : false;
  }
  public function addParticipiantToDialog($message)
  {
    $checkAttendance = $this->findParticipant($message->idUser);

    if ($checkAttendance) {
      $this->dialogServices->changeStateUserDialog($this->idDialog, $message->idUser, 1);
    } else {
      //Rewrite this part
      $this->wpdb->query("INSERT INTO `gi_new_participants_dialog`
      (id_dialog, id_participant) 
      VALUES 
      ('$this->idDialog', '$message->idUser')");

    }
    $this->idParticipant = $this->dialogServices->getDialogParticipant($this->idDialog, 1);
    // $this->catcherBugs->convPrintLog($this->idParticipant, 'addParticipiantToDialog', '$this->idParticipant');
    $resultIdMessages = $this->wpdb->get_results("SELECT id_message FROM `$this->pathTableDB` WHERE id_dialog = '$this->idDialog' AND id_author != '$message->idUser'");
    foreach ($resultIdMessages as $id) {
      $this->wpdb->query("INSERT INTO `gi_new_log_unreaded_messages`
        (id_dialog, id_message, id_user) 
        VALUES 
        ('$this->idDialog', '$id->id_message', '$message->idUser')");
    }
    $this->updateMessagesDidintRead();
  }

  public function participantLeftDialog($message)
  {
    $this->dialogServices->changeStateUserDialog($this->idDialog, $message->idUser, 0);
    $this->idParticipant = $this->dialogServices->getDialogParticipant($this->idDialog, 1);
    $this->deleteUnreadedMessages($message->idUser);
    $this->updateMessagesDidintRead();
  }
  public function addReplacementDialog()
  {
    $this->idParticipant = $this->dialogServices->getDialogParticipant($this->idDialog, 1);
    $this->updateMessagesDidintRead();
  }
  private function updateMessagesDidintRead()
  {
    foreach ($this->messages as $message) {
      /** @var Message $message */
      $message->updateDidintRead();
    }
  }
}