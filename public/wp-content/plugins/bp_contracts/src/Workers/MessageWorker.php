<?php
namespace PersonalAccount\Workers;

use PersonalAccount\Core\Container;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;

use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DialogServices;
use PersonalAccount\Utilities\DBUtilities;
use DateTime;
class MessageWorker
{
  private $container;
  /** @var DBWorker */
  private $DBWorker;
  /** @var DBUtilities */
  private $DBUtilities;
  /** @var CatcherBugs */
  private $CatcherBugs;
  /** @var SimpleUtilities */
  private $SimpleUtilities;
  /** @var DialogServices */
  private $DialogServices;
  public function __construct(Container $container)
  {
    $this->container = &$container;
    $this->DBWorker = $this->container->get('DBWorker');
    $this->DBUtilities = $this->container->get('DBUtilities');
    $this->CatcherBugs = $this->container->get('CatcherBugs');
    $this->SimpleUtilities = $this->container->get('SimpleUtilities');
    $this->DialogServices = $this->container->get('DialogServices');
  }
  public function createDirectoryUpload($year, $serialNumber)
  {
    $uploadDirectoryYear = ABSPATH . 'wp-content/uploads/contracts/' . $year;
    $uploadDirectory = $uploadDirectoryYear . '/serial_number_' . $serialNumber;
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
  protected function createPathForDB($fileName, $year, $serialNumber)
  {
    $pathForDB = '/wp-content/uploads/contracts/' . $year . '/serial_number_' . $serialNumber;
    $fileUrlForDB = $pathForDB . '/' . $fileName;
    return $fileUrlForDB;
  }
  protected function saveFilesOnServer($files, $year, $serialNumber)
  {
    $pathFilesForDB = [];
    if (!empty($files)) {
      //Подгрузка всех файлов на сервер.
      foreach ($files as $file) {
        $fileName = $file->fileName;
        $fileData = base64_decode($file->content);
        $uploadDirectory = $this->createDirectoryUpload($year, $serialNumber);
        // $this->CatcherBugs->catchBug('NewMessage', 'uploadDirectory', $uploadDirectory);
        $fileNameNew = $this->checkExistanceFile($uploadDirectory, $fileName);
        // $this->CatcherBugs->catchBug('NewMessage', 'fileNameNew', $fileNameNew);
        $fileUrl = $uploadDirectory . '/' . $fileNameNew;
        // $this->CatcherBugs->catchBug('NewMessage', 'fileUrl', $fileUrl);
        $fileUrlForDB = $this->createPathForDB($fileNameNew, $year, $serialNumber);
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
  public function workWithNewMessage($newMessage, $contract)
  {
    $idParticipants = $this->DialogServices->getDialogParticipant($newMessage->idDialog, 1);

    $dateSend = $this->SimpleUtilities->currentTime();
    $year = $this->detectionYear($contract['dateCreation']);
    $pathTableDB = $this->createPathTableDB('contract', $year) . '_' . $contract['idPoint'];
    $idMessage = $this->DBWorker->getMax($pathTableDB, 'id_dialog', $newMessage->idDialog, 'id_message');
    
    $newMessage->idMessage = $idMessage;
    $newMessage->dateSend = $dateSend;
    $newMessage->idParticipants = $idParticipants;
    $newMessage->pathTableDB = $pathTableDB;

    $idMessageTable = $this->writeNewMessage($newMessage, $contract, $year, $dateSend);

    $newMessage->idMessageTable = $idMessageTable;

    $lastMessage = $this->getLastMessage($newMessage, $contract, $year, $dateSend);
    
    if (empty($lastMessage)) {
      $this->CatcherBugs->convPrintLog($lastMessage, 'MessageWorker workWithNewMessage $lastMessage EMPTY', '$lastMessage');
      $this->CatcherBugs->convPrintLog($newMessage, 'MessageWorker workWithNewMessage $newMessage', '$newMessage');
    }
    // $this->CatcherBugs->convPrintLog($newMessage, 'workWithNewMessage', '$newMessage');
    $this->DBWorker->updateDateReadedMessagesDB($newMessage);
    // $this->updateDateLastActivityDialog($dateNewMessage);
    $this->DBWorker->writeLogLastMessage($newMessage->idParticipants, $newMessage);
    return $newMessage;
  }
  private function getLastMessage($newMessage, $contract, $year, $dateSend)
  {
    $pathTableDB = $this->createPathTableDB('contract', $year) . '_' . $contract['idPoint'];
    $prepareCondition = $this->DBUtilities->prepareEqualAndEqual($newMessage->idDialog, $newMessage->idMessage, 'id_dialog', 'id_message');
    $lastMessage = $this->DBWorker->selectUni($pathTableDB, $prepareCondition);
    return $lastMessage;
  }
  public function writeNewMessage($newMessage, $contract, $year, $dateSend)
  {
    $pathTableDB = $this->createPathTableDB('contract', $year) . '_' . $contract['idPoint'];

    // $idNewMessage = $this->DBWorker->getMax($pathTableDB, 'id_dialog', $newMessage->idDialog, 'id_message');
    // $this->CatcherBugs->catchBug('NewMessage', 'newMessage->files', $newMessage->files, $newMessage->filesExist);
    $files = $this->saveFilesOnServer($newMessage->files, $year, $contract['sn']);
    // $this->CatcherBugs->catchBug('NewMessage', 'files', $files, $newMessage->filesExist);
    $value = [$newMessage->idDialog, $newMessage->idMessage, $newMessage->idAuthor, $newMessage->messageBody, $files, $dateSend, ''];
    // $this->CatcherBugs->convPrintLog($this->pathTableDB, 'writeNewMessage', '$this->pathTableDB', 3);
    $this->DBWorker->insertDBU($pathTableDB, $value);
    // $this->wpdb->query("INSERT INTO `$this->pathTableDB` 
    //     (id_dialog, id_message, id_author, message_body, files, date_send, date_readed) 
    //     VALUES ('$newMessage->idDialog', '$idNewMessage', '$newMessage->idAuthor', '$newMessage->messageBody', '$files', '$dateSend', '')");
    $idLastMessage = $this->DBWorker->lastId();
    // $this->CatcherBugs->convPrintLog($idLastMessage, 'writeNewMessage', '$idLastMessage');
    return intval($idLastMessage);
  }

  // protected function updateDateLastActivityDialog($newDate, $idDialog)
  // {
  //   $this->DBWorker->updateDBU('gi_new_dialogues', 'id_dialog', $idDialog, 'date_last_activity', $newDate);
  // }
  private function createPathTableDB($type, $year)
  {
    $pathTableDB = 'x_gi_new_messages_' . $type . '_' . $year;
    return $pathTableDB;
  }
  private function detectionYear($dateCreation)
  {
    $date = new DateTime($dateCreation);
    $year = $date->format('Y');
    return $year;
  }
}
