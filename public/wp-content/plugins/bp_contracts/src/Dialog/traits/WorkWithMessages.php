<?php
namespace PersonalAccount\Dialog\traits;
use PersonalAccount\Dialog\Message;
trait WorkWithMessages
{
  protected $messages;
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  abstract protected function tableCreate();
  abstract protected function checkContractor();
  protected function initializationMessages()
  {
    $tableExists = $this->dbWorker->checkExistanceTable($this->pathTableDB);
    if (!$tableExists) {
      $this->tableCreate();
    }
    $dataMessages = $this->getDataMessages($this->idDialog, $this->pathTableDB);
    if (!empty($dataMessages)) {
      $this->messages = $this->factory->createDependentObjects('Message', $this->container, $dataMessages);
    }
  }
  protected function getDataMessages($idDialog, $pathDB)
  {
    $sqlDataMessages = $this->wpdb->get_results("SELECT * FROM `$pathDB` WHERE id_dialog = '$idDialog'");
    return $sqlDataMessages;
  }
  protected function getIdNewMessage()
  {
    $idLastMessage = $this->wpdb->get_var("SELECT MAX(id_message) FROM `$this->pathTableDB` WHERE id_dialog = '$this->idDialog'");
    $idMessage = ($idLastMessage !== null) ? (intval($idLastMessage) + 1) : 1;
    return $idMessage;
  }
  protected function createDirectoryUpload()
  {
    $uploadDirectoryYear = ABSPATH . 'wp-content/uploads/contracts/' . $this->year;
    $uploadDirectory = $uploadDirectoryYear . '/serial_number_' . $this->serialNumber;
    if (!is_dir($uploadDirectoryYear)) {
      mkdir($uploadDirectoryYear, 0755, true);
    }
    if (!is_dir($uploadDirectory)) {
      mkdir($uploadDirectory, 0755, true);
    }
    return $uploadDirectory;
  }
  protected function checkExistanceFile($uploadDirectory, $fileName)
  {
    $fileUrl = $uploadDirectory . '/' . $fileName;
    $prefix = 1;
    if (file_exists($fileUrl)) {
      while (file_exists($fileUrl)) {
        $filenameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameNew = $filenameWithoutExtension . "($prefix)." . $fileExtension;
        $fileUrl = $uploadDirectory . '/' . $fileNameNew;
        ++$prefix;
      }
      return $fileNameNew;
    }
    return $fileName;
  }
  protected function createPathForDB($fileName)
  {
    $pathForDB = '/wp-content/uploads/contracts/' . $this->year . '/serial_number_' . $this->serialNumber;
    $fileUrlForDB = $pathForDB . '/' . $fileName;
    return $fileUrlForDB;
  }
  protected function saveFilesOnServer($files)
  {
    $pathFilesForDB = [];
    if (!empty($files)) {
      //Подгрузка всех файлов на сервер.
      foreach ($files as $file) {
        $fileName = $file->fileName;
        $fileData = base64_decode($file->content);
        $uploadDirectory = $this->createDirectoryUpload();
        // $this->CatcherBugs->catchBug('NewMessage', 'uploadDirectory', $uploadDirectory);
        $fileNameNew = $this->checkExistanceFile($uploadDirectory, $fileName);
        // $this->CatcherBugs->catchBug('NewMessage', 'fileNameNew', $fileNameNew);
        $fileUrl = $uploadDirectory . '/' . $fileNameNew;
        // $this->CatcherBugs->catchBug('NewMessage', 'fileUrl', $fileUrl);
        $fileUrlForDB = $this->createPathForDB($fileNameNew);
        // $this->CatcherBugs->catchBug('NewMessage', 'fileUrlForDB', $fileUrlForDB);
        //Проверка существования файла. Если файл уже есть сохраняем его с prefix
        if (!file_exists($uploadDirectory) && !is_dir($uploadDirectory)) {
          mkdir($uploadDirectory, 0755, true);
        }
        if (file_put_contents($fileUrl, $fileData)) {
          $pathFilesForDB[] = $fileUrlForDB;
        } else {
          // $this->CatcherBugs->catchBug('NewMessage', 'else in (file_put_contents($fileUrl, $fileData))', '');
        }
      }
    } else {
      // $this->CatcherBugs->catchBug('NewMessage', 'else (!empty($files))', $files);
    }
    return json_encode($pathFilesForDB);
  }
  private function writeNewMessage($newMessage)
  {
    $dateSend = $this->simpleUtilities->currentTime();
    $idNewMessage = $this->getIdNewMessage();
    // $this->CatcherBugs->catchBug('NewMessage', 'newMessage->files', $newMessage->files, $newMessage->filesExist);
    $files = $this->saveFilesOnServer($newMessage->files);
    // $this->CatcherBugs->catchBug('NewMessage', 'files', $files, $newMessage->filesExist);
    $value = [$newMessage->idDialog, $idNewMessage, $newMessage->idAuthor, $newMessage->messageBody, $files, $dateSend, ''];
    // $this->CatcherBugs->convPrintLog($this->pathTableDB, 'writeNewMessage', '$this->pathTableDB', 3);
    $this->dbWorker->insertDBU($this->pathTableDB, $value);
    // $this->wpdb->query("INSERT INTO `$this->pathTableDB` 
    //     (id_dialog, id_message, id_author, message_body, files, date_send, date_readed) 
    //     VALUES ('$newMessage->idDialog', '$idNewMessage', '$newMessage->idAuthor', '$newMessage->messageBody', '$files', '$dateSend', '')");
    $idLastMessage = $this->wpdb->insert_id;
    return intval($idLastMessage);
  }
  private function getDataOneMessage($idMessage)
  {
    // date_default_timezone_set('Europe/Moscow');
    $sqlDataMessage = $this->wpdb->get_results("SELECT * FROM `$this->pathTableDB` WHERE id = '$idMessage'");
    // error_log(print_r($sqlDataMessage, true));
    return $sqlDataMessage;
  }
  private function addNewMessage($dataMessage)
  {
    // $this->CatcherBugs->convPrintLog($dataMessage, 'addNewMessage', '$dataMessage');
    if (!empty($dataMessage)) {
      $message = $this->factory->createDependentObjects('Message', $this->container, $dataMessage);

      if (!isset($this->messages)) {
        $this->messages = [];
      }
      if (!empty($message) && isset($message[0])) {
        array_push($this->messages, $message[0]);
        // $this->leafs = &$this->messages;
        // $this->messages[] = $message[0];
        return $message[0];
      }
    }
  }
  public function getIdDialog()
  {
    return $this->getProperty('idDialog');
  }


  // protected function checkfactoryWorkerInDialog()
  // {
  //   $flag = false;
  //   $this->idParticipant = $this->simpleUtilities->toArray($this->idParticipant);
  //   // $this->CatcherBugs->convPrintLog($this->idParticipant,'checkfactoryWorker', '$this->idParticipant');
  //   foreach ($this->idParticipant as $idUser) {
  //     $whose = $this->UserUtilities->checkUserAccess($idUser);
  //     if ($whose === 'factory_worker') {
  //       $flag = true;
  //       break;
  //     }
  //   }
  //   if (!$flag) {
  //     $idManagerInPoint = $this->UserUtilities->getManagerForPoint($this->idPoint);
  //     $idfactoryWorker = $this->DialogServices->choiseManagerCurrentDialog($this->typeDialog, $idManagerInPoint);
  //     // $this->CatcherBugs->convPrintLog($idfactoryWorker, 'checkfactoryWorker', '$idfactoryWorker');
  //     $this->DialogServices->addParticipant($idfactoryWorker, $this->idDialog);
  //     $this->idParticipant[] = $idfactoryWorker;
  //   }
  // }
  // protected function checkContractorOLD()
  // {
  //   $flag = false;
  //   foreach ($this->idParticipant as $idUser) {
  //     $whose = $this->checkUserAccess($idUser);
  //     if ($whose === 'contractor') {
  //       $flag = true;
  //       break;
  //     }
  //   }
  //   if (!$flag) {
  //     $this->addParticipant($this->idCreator, $this->idDialog);
  //     $this->idParticipant[] = $this->idCreator;
  //   }
  // }

  public function workWithNewMessage($newMessage)
  {
    if (!empty($newMessage)) {
      // $this->checkfactoryWorkerInDialog();
      // $this->checkContractor();

      // $this->idParticipant = $this->DialogServices->getDialogParticipant($this->idDialog, 1);
      // $idNewMessage = $this->writeNewMessage($newMessage);
      $dataNewMessage = $this->getDataOneMessage($newMessage->idMessageTable);
      // $this->CatcherBugs->convPrintLog($dataNewMessage, 'workWithNewMessage', '$dataNewMessage');
      /** @var Message $message */
      $message = $this->addNewMessage($dataNewMessage);
      if (!empty($message)) {
        // $dateTimestampNewMessage = $message->getDateSendTimestamp();
        $dateNewMessage = $message->getDateSend();
        // $this->updateDateReadedMessagesDB($idNewMessage, $dateNewMessage, $newMessage->idAuthor);
        $this->updateReadedMessages($newMessage->idAuthor, $dateNewMessage);
        // $this->updateDateLastActivityDialog($dateNewMessage);

        // $didntRead = $message->writeLogLastMessage($this->idParticipant);
        // return $didntRead;
      }
    }
  }
  public function workWithReadedMessage($message)
  {
    /** @var Message $currentMessage */
    $currentMessage = $this->findMessage($message->idMessage);
    $dateFirstReaded = $currentMessage->readedThisMessage($message);
    if ($dateFirstReaded) {
      $this->updateDateReadedMessagesDB($message->idMessage, $dateFirstReaded, $message->whoRead);
    }
    $this->updateReadedMessages($message->whoRead, $dateFirstReaded);
  }
  protected function updateDateReadedMessagesDB($idMessage, $date, $idUser)
  {

    $this->wpdb->query("UPDATE `$this->pathTableDB` SET date_readed='$date' WHERE id_dialog = '$this->idDialog' AND id_message <= '$idMessage' AND date_readed <= 0 AND id_author != '$idUser'");

  }
  protected function updateReadedMessages($idUser, $dateFirstReaded)
  {
    /** @var Message $message */
    foreach ($this->messages as &$message) {
      $idAuthor = $message->getIdAuthor();
      if (intval($idUser) !== intval($idAuthor)) {
        $message->updateWhoReadedMessage($idUser);
        $message->updateDateReadedMessage($dateFirstReaded);
      }
    }
  }
  protected function findMessage($idMessage)
  {
    foreach ($this->messages as &$message) {
      $idMessageInDialog = $message->getIdMessage();
      if (intval($idMessageInDialog) === intval($idMessage)) {
        $linkMessage = &$message;
        return $linkMessage;
      }
    }
  }
  protected function updateDateLastActivityDialog($newDate)
  {
    $this->dateLastActivity = $newDate;
    $this->wpdb->query("UPDATE gi_new_dialogues SET date_last_activity = '$newDate' WHERE id_dialog = $this->idDialog");
  }
}