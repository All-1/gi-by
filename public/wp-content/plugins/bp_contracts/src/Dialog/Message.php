<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Utilities\Utilit;


class Message
{
  // use DependencyInjections;
  use Utilit; //Содержаться все функции которые имеют к базе данных, но нужны в разных объектах, а так же общая функция для создания капсулы
  /** @var Container */
  protected $container;
  /** @var SimpleUtilities */
  protected $simpleUtilities;
  /** @var UserUtilities */
  protected $userUtilities;
  private $idDialog; // idDialog – устанавливается автоматически в соответствии с диалогом которому оно принадлежит
  private $idMessage; // idMessage - устанавливается автоматически в соответствии с очерёдностью в диалоге которому оно принадлежит
  private $idAuthor; // idAuthor - устанавливается автоматически в соответствии с параметром отправившего юзера 
  private $messageBody; // messageBody – вводится юзер
  private $files; // files – заполняется юзером
  private $dateSend; // dateSend - автоматически
  private $dateSendTimestamp;
  private $dateReaded; // dateReaded – устанавливается по первому юзеру нажавшему на прочитано.
  private $dateReadedTimestamp;
  private $didntRead;
  private $dialog;
  private $firstNameAuthor;
  private $lastNameAuthor;
  private $userRoleAuthor;
  // Методы.
  public function __construct($message, $container, $parent)
  {
    date_default_timezone_set('Europe/Moscow');
    $this->container = &$container;
    $this->userUtilities = $this->container->get('UserUtilities');
    $this->simpleUtilities = $this->container->get('SimpleUtilities');
    $this->initializationWPDB();
    $this->idDialog = intval($message->id_dialog);
    $this->idMessage = intval($message->id_message);
    $this->idAuthor = intval($message->id_author);
    $this->firstNameAuthor = get_user_meta($this->idAuthor, 'first_name', true);
    $this->lastNameAuthor = get_user_meta($this->idAuthor, 'last_name', true);
    $this->userRoleAuthor = $this->userUtilities->getUserRole($this->idAuthor);
    $this->messageBody = $message->message_body;
    $this->files = $message->files;
    $this->dateSend = $message->date_send;
    $this->dateReaded = $message->date_readed;
    $this->dateSendTimestamp = convert_timestamp_index($message->date_send);
    $this->dateReadedTimestamp = convert_timestamp_index($message->date_readed);
    $this->didntRead = $this->getDidntRead();
  }
  // writeLogLastMessage - при отправке сообщения вызывается метод диалога по записи в таблицу log_last_message 

  // addMessage - Дополнить сообщение.
  private function addMessage($idUser, $idLastMessages, $idDialog)
  {

  }
  // replyMessage - Возможность ответить на сообщение. 
  private function deletePrevMessageLog($idUser)
  {
    $this->wpdb->query("DELETE FROM `gi_new_log_unreaded_messages` WHERE id_dialog = '$this->idDialog' AND id_message <= '$this->idMessage' AND id_user = '$idUser'");
  }
  private function getDidntRead()
  {
    $sqlDidntRead = $this->wpdb->get_results("SELECT id_user FROM gi_new_log_unreaded_messages WHERE id_dialog = '$this->idDialog' AND id_message = '$this->idMessage'");
    $didntRead = [];
    foreach ($sqlDidntRead as $idUser) {
      $didntRead[] = $idUser->id_user;
    }
    return $didntRead;
  }
  public function getDataMessageOutside()
  {
    $dataMessage = [
      'idDialog' => $this->idDialog,
      'idMessage' => $this->idMessage,
      'idAuthor' => $this->idAuthor,
      'messageBody' => $this->messageBody,
      'files' => $this->files,
      'dateSend' => $this->dateSend,
      'dateSendTimestamp' => $this->dateSendTimestamp,
      'dateReaded' => $this->dateReaded,
      'didntRead' => $this->userUtilities->getInfoAboutUsers($this->didntRead),
      'firstNameAuthor' => $this->firstNameAuthor,
      'lastNameAuthor' => $this->lastNameAuthor,
    ];
    return $dataMessage;
  }
  public function writeLogLastMessage($idParcipiants)
  {
    foreach ($idParcipiants as $parcipiantId) {
      if (intval($parcipiantId) !== intval($this->idAuthor)) {
        $this->wpdb->query("INSERT INTO gi_new_log_unreaded_messages 
          (id_dialog, id_message, id_user) 
          VALUES ('$this->idDialog', '$this->idMessage', '$parcipiantId')");
        $this->deletePrevMessageLog($this->idAuthor);
      }
    }
    $didntRead = $this->getDidntRead();
    $this->didntRead = $didntRead;
    return $didntRead;
  }
  public function readedThisMessage($message)
  {
    $this->deletePrevMessageLog($message->whoRead);
    $didntRead = $this->getDidntRead();
    $this->didntRead = $didntRead;
    if ($this->dateReadedTimestamp < 0) {
      $this->dateReaded = $this->simpleUtilities->currentTime();
      $this->dateReadedTimestamp = convert_timestamp_index($this->dateReaded);
      return $this->dateReaded;
    }
    return null;
  }
  public function getIdMessage()
  {
    return $this->idMessage;
  }
  public function getDateReadedTimestamp()
  {
    return $this->dateReadedTimestamp;
  }
  public function getDateSend()
  {
    return $this->dateSend;
  }
  public function getDateSendTimestamp()
  {
    return $this->dateSendTimestamp;
  }
  public function getIdAuthor()
  {
    return $this->idAuthor;
  }
  public function updateDateReadedMessage($dateFirstReaded)
  {
    if ($dateFirstReaded && convert_timestamp_index($this->dateReaded) < 0) {
      $this->dateReaded = $dateFirstReaded;
      $this->dateReadedTimestamp = convert_timestamp_index($this->dateReaded);
    }
  }
  public function updateWhoReadedMessage($idWhoRead)
  {
    $didntRead = [];
    foreach ($this->didntRead as $idUser) {
      if (intval($idWhoRead) === intval($idUser)) {
        continue;
      }
      $didntRead[] = $idUser;
    }
    $this->didntRead = $didntRead;
  }
  public function updateDidintRead()
  {
    $this->didntRead = $this->getDidntRead();
  }
  // saveFilesOnServer - метод загрузки файлов на сервер.
}
