<?php
namespace PersonalAccount\Workers;

use PersonalAccount\Chat;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\Mailer;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\DialogServices;
use PersonalAccount\Utilities\Utilit;
use PersonalAccount\Controler\UserControler;
use \Exception;

class NotificationWorker
{
  use Utilit;
  // use DependencyInjections; //This is the main trait for all system.
  // use CatcherBugsTemporary;
  private $InteractionInterface;
  private $ControlerObjectsRelationship; //Cсылка на объект
  /** @var UserControler */
  private $UserControler;
  /** @var Chat */
  private $Chat;
  /** @var Container */
  private $Container;
  /** @var SimpleUtilities */
  protected $SimpleUtilities;
  /** @var UserUtilities */
  protected $UserUtilities;
  /** @var DialogServices */
  protected $DialogServices;
  /** @var DBWorker */
  protected $DBWorker;
  /** @var Mailer */
  protected $Mailer;
  /** @var DataUtilities */
  protected $DataUtilities;
  /** @var CatcherBugs */
  protected $CatcherBugs;
  public function __construct($Container)
  {
    $this->initializationWPDB();
    $this->Container = &$Container;
    $this->UserUtilities = $this->Container->get('UserUtilities');
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
    $this->DialogServices = $this->Container->get('DialogServices');
    $this->DBWorker = $this->Container->get('DBWorker');
    $this->Mailer = $this->Container->get('Mailer');
    $this->DataUtilities = $this->Container->get('DataUtilities');
    $this->UserControler = $this->Container->get('UserControler');
    $this->Chat = $this->Container->get('Chat');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
  }
  private function prepareEmail($data, $userId, $type)
  {
    $where = 'prepareEmail';
    $userInfo = get_userdata($userId);
    $email = $this->UserUtilities->extractUserParam($userInfo, 'user_email', $userId, $where);
    $lastName = $this->UserUtilities->extractUserParam($userInfo, 'last_name', $userId, $where);
    $firstName = $this->UserUtilities->extractUserParam($userInfo, 'first_name', $userId, $where);
    $emailNotification = boolval($this->UserUtilities->extractUserParam($userInfo, 'email_notification', $userId, $where));

    if (!empty($email) && $emailNotification === true) {
      if ($type === 'AddParticipiant') {
        $body = "Пользователь: " . $data['nameAuthor'] . $data['messageBody'];
        $subject = "Вас добавили в " . $data['typeDialog'];
      } else if ($type === 'Message') {
        $body = $data['nameAuthor'] . ': ' . $data['messageBody'];
        $subject = 'Новое сообщение в: ' . $data['nameOR'];
      }
      $newData = [
        'lastName' => $lastName,
        'firstName' => $firstName,
        'email' => $email,
        'subject' => $subject,
        'body' => $body,
      ];

      return $this->SimpleUtilities->checkReturn($newData);
    }
    // return $this->CatcherBugs->checkReturn($newData, 'prepareEmail', $type);
  }
  private function prepareDataNotification_2($didntRead, $dataOR, $message)
  {
    $msgInner = is_object($message) ? get_object_vars($message) : $message;
    $amount = isset($didntRead[0]) ? count($didntRead) : 1;
    // $lastDidntRead = end($userDidntRead);
    $typeOR = isset($msgInner['typeDialog']) ? $this->DialogServices->defineTypeOR($msgInner['typeDialog']) : '';
    $nameAuthor = isset($msgInner['idAuthor']) ? $this->UserUtilities->getNameUser($msgInner['idAuthor']) : '';

    $column = isset($typeOR) ? $this->SimpleUtilities->snakeToCamel('name_' . $typeOR) : '';

    $data = [
      'amount' => $amount,
      'typeOR' => $typeOR,
      'serialNumber' => $dataOR['sn'],
      'nameOR' => $dataOR[$column],
      'messageBody' => isset($msgInner['messageBody']) ? $msgInner['messageBody'] : '',
      'nameAuthor' => $nameAuthor,
    ];
    return $data;
  }

  public function setProperty($property, $value)
  {
    try {
      if (property_exists($this, $property)) {
        $this->$property = $value;
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'addParramForUser'];
    }
  }
  public function prepareDataToSend($message)
  {
    try {
      $dataOR = $this->DBWorker->selectResultsFromDBU_2('gi_new_contract', 'sn', $message->serialNumber);
      $prepareCondition = [
        'id_message' => intval($message->idMessage),
        'id_dialog' => $message->idDialog,
      ];
      $didntRead = $this->DBWorker->selectResultsFromDBU_2('gi_new_log_unreaded_messages', ['id_message', 'id_dialog'], $prepareCondition, 'id_user');
      $didntRead = is_array($didntRead) ? $didntRead : [$didntRead];

      if (!empty($didntRead)) {
        $message->nameContract = isset($dataOR['nameContract']) ? $dataOR['nameContract'] : '';
        $message->idPoint = isset($dataOR['idPoint']) ? $dataOR['idPoint'] : '';
        $emailsData = [];
        foreach ($didntRead as $userId) {
          $checkOnline = $this->UserControler->findUserOnline($userId);
          $didntReadAllMsg = $this->DBWorker->selectResultsFromDBU_2('gi_new_log_unreaded_messages', 'id_user', $userId);
          $data = $this->prepareDataNotification_2($didntReadAllMsg, $dataOR, $message);

          if ($checkOnline) {
            $idWebsocket = $this->UserControler->getIdWebsocketByUserId($userId);
            $this->Chat->sendExternalMessage($idWebsocket, 'Notification', $data);
          } else {
            $emailData = $this->prepareEmail($data, $userId, 'Message');
            if (!empty($emailData)) {
              $emailsData[] = $emailData;
            }
          }
        }
        if (!empty($emailsData)) {
          // $this->CatcherBugs->convPrintLog($emailsData, 'prepareDataToSend', '$emailsData');
          $this->Mailer->sendCurl_2($emailsData);
        }
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'prepareDataToSend'];
    }
  }
  public function addParticipiant($message, $idWebsocket, $idUserWhoAdd)
  {
    $dialog = $this->DBWorker->selectResultsFromDBU_2('gi_new_dialogues', 'id_dialog', $message->idDialog);
    $typeOR = $this->DialogServices->defineTypeOR($dialog['typeDialog']);
    $dataOR = $this->DBWorker->selectResultsFromDBU_2('gi_new_' . $typeOR, 'sn', $dialog['sn']);
    $userName = $this->UserUtilities->getNameUser($idUserWhoAdd);
    $typeDialogCyr = $this->DialogServices->typeDialogLatToCyr($dialog['typeDialog']);
    $serialNumberForUser = $this->SimpleUtilities->formatNumber($dataOR['sn']);
    $column = $this->SimpleUtilities->snakeToCamel('name_' . $typeOR);
    $nameOR = $dataOR[$column];
    $didntRead = $this->DBWorker->selectResultsFromDBU_2('gi_new_log_unreaded_messages', 'id_user', $message->idUser);
    $amount = isset($didntRead[0]) ? count($didntRead) : 1;

    $msgSend = "добавил Вас в " . $typeDialogCyr . " в договоре: №" . $serialNumberForUser . " " . $nameOR;

    $data = [
      'amount' => $amount,
      'typeOR' => $typeOR,
      'typeDialog' => $typeDialogCyr,
      'serialNumber' => $dataOR['sn'],
      'nameOR' => $nameOR,
      'messageBody' => $msgSend,
      'nameAuthor' => $userName,
    ];

    if ($idWebsocket) {
      error_log(print_r($idWebsocket, true));
      error_log('User is online');
      $this->Chat->sendExternalMessage($idWebsocket, 'Notification', $data);
    } else {
      $emailData = $this->prepareEmail($data, $message->idUser, 'AddParticipiant');
      // $this->sendEmailNotification_2($emailData);
      $this->Mailer->sendCurl_2([$emailData]);
      error_log('User isnt online');
    }
  }
  public function getUserNotification($idUser)
  {
    try {
      $didntRead = $this->DBWorker->selectResultsFromDBU_2('gi_new_log_unreaded_messages', 'id_user', $idUser);
      // $this->CatcherBugs->convPrintLog($didntRead, 'getUserNotification', '$didntRead');
      if (!empty($didntRead)) {
        $lastLog = $this->DataUtilities->getFromArrayMinMax($didntRead, 'id');
        $dialog = $this->DBWorker->selectResultsFromDBU_2('gi_new_dialogues', 'id_dialog', $lastLog['idDialog']);
        $typeOR = $this->DialogServices->defineTypeOR($dialog['typeDialog']);
        $dataOR = $this->DBWorker->selectResultsFromDBU_2('gi_new_' . $typeOR, 'sn', $dialog['sn']);
        $year = date("Y", strtotime($dataOR['dateCreation']));
        $endPath = $typeOR === 'contract' ? '_' . $dataOR['idPoint'] : '';
        $path = 'x_gi_new_messages_' . $typeOR . '_' . $year . $endPath;
        $prepareCondition = [
          'id_message' => $lastLog['idMessage'],
          'id_dialog' => $lastLog['idDialog'],
        ];
        $message = $this->DBWorker->selectResultsFromDBU_2($path, ['id_message', 'id_dialog'], $prepareCondition);
        $message['typeDialog'] = $dialog['typeDialog'];
        $data = $this->prepareDataNotification_2($didntRead, $dataOR, $message);
        return $data;
      }
    } catch (Exception $e) {
      // Логирование ошибки
      error_log($e->getMessage());
      return ['error' => 'getUserNotification'];
    }
  }
}
