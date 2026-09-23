<?php
namespace PersonalAccount\Workers;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PersonalAccount\Workers\CatcherBugs;
use GuzzleHttp\Client;
class Mailer
{
  private $Mailer;
  private $sender;
  public function __construct()
  {
    $this->Mailer = new PHPMailer(true);

    try {
      // Настройки сервера
      $this->Mailer->isSMTP();                                      // Использование SMTP
      $this->Mailer->Host = 'smtp.yandex.ru';                       // SMTP-сервер Yandex
      $this->Mailer->SMTPAuth = true;                               // Включить аутентификацию
      $this->Mailer->Username = 'gi@gi.by';                         // Ваш логин
      $this->Mailer->Password = 'kUtv#5fk*7g';                      // Пароль приложения
      $this->Mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // Защищённое соединение (SSL/TLS)
      $this->Mailer->Port = 465;                                    // Порт для SSL
      $this->Mailer->CharSet = 'UTF-8';                             // Установка кодировки
      
      // Set the sender URL directly
      $this->sender = "https://dev.gi.by/wp-content/plugins/bp_contracts/sender/newSender.php";
      // $this->sender = "https://geosideal.ru/wp-content/plugins/bp_contracts/sender/newSender.php";
      // Log the sender URL for debugging
      error_log("Sender URL: " . $this->sender);
    } catch (Exception $e) {
      error_log("Ошибка настройки PHPMailer: {$e->getMessage()}");
      throw new Exception("Ошибка инициализации почтового клиента.");
    }
  }

  public function sendToEmail($data)
  {

    try {
      // echo "Данные после декодирования в sendToEmail: " . print_r($data, true);
      foreach ($data as $item) {
        if (!empty($item)) {
          // echo "Данные после декодирования в sendToEmail: " . print_r($item, true);
          $this->Mailer->setFrom('gi@gi.by', 'Личный Кабинет GI.BY');
          $this->Mailer->addAddress($item['email'], trim("{$item['lastName']} {$item['firstName']}"));
          $this->Mailer->isHTML(true);
          $this->Mailer->Subject = $item['subject'];
          $this->Mailer->Body = $item['body'];
          $this->Mailer->AltBody = strip_tags($item['body']);
          $this->Mailer->send();
          // error_log(print_r($this->Mailer->Body, true));
        }
      }
    } catch (Exception $e) {
      error_log("Ошибка отправки письма: {$e->getMessage()}");
      throw new Exception("Письмо не было отправлено. Проверьте настройки.");
    }

  }
  public function sendCurlOLD($data)
  {
    // Prepare JSON data
    $jsonData = json_encode($data);

    // Log the data being sent
    error_log("Data being sent in sendCurl: " . print_r($data, true));

    // Указываем URL, на который будет отправлен запрос
    $url = $this->sender;

    // Формируем команду для Windows
    $command = sprintf(
        'curl -X POST "%s" -H "Content-Type: application/json" -d "%s" > NUL 2>&1 &',
        $url,
        str_replace('"', '\\"', $jsonData)
    );

    // Запускаем процесс в фоновом режиме
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        pclose(popen('start /B ' . $command, 'r'));
    } else {
        // Linux/Unix
        exec($command . ' > /dev/null 2>&1 &');
    }

    return true;
  }
  public function sendCurl_1($data)
  {
    // Prepare JSON data
    $jsonData = json_encode($data);

    // Log the data being sent
    error_log("Data being sent in sendCurl_1: " . print_r($data, true));
    error_log("JSON data in sendCurl_1: " . $jsonData);

    // Указываем URL, на который будет отправлен запрос
    $url = $this->sender;

    // Инициализируем cURL
    $ch = curl_init();

    // Настраиваем cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: */*'
    ]);

    // Включаем режим отладки для подробной информации
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    // Выполняем запрос и получаем ответ
    $response = curl_exec($ch);

    // Получаем подробную информацию из режима отладки
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    $CatcherBugs = new CatcherBugs();
    // $CatcherBugs->convPrintLog($verboseLog, 'Verbose info', '$verboseLog');
    // error_log("Verbose info: " . $verboseLog);

    // Проверяем ошибки cURL
    if (curl_errno($ch)) {
      $error = curl_error($ch);
      error_log("cURL error: $error");
      curl_close($ch);
      return ['success' => false, 'error' => $error];
    }

    // Закрываем cURL
    curl_close($ch);

    // Логируем ответ
    error_log("Response from newSender.php: " . $response);

    return ['success' => true, 'response' => $response];
  }
  public function sendCurl_2($data)
  {
    // Fire-and-forget using external curl with a temp JSON file; returns immediately and avoids quoting issues
    $jsonData = json_encode($data);


    $url = $this->sender;

    // Write payload to a temp file to avoid shell quoting problems
    $tempFile = tempnam(sys_get_temp_dir(), 'async_post_');
    if ($tempFile === false) {
      // $CatcherBugs->convPrintLog('$tempFile === false', 'sendCurl_2', '$tempFile');
      return false;
    }
    // Ensure .json extension for clarity
    $jsonFile = $tempFile . '.json';
    rename($tempFile, $jsonFile);
    file_put_contents($jsonFile, $jsonData);

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // Windows: start a background cmd that posts then deletes the temp file
      // Use ^& so the parent shell passes & into the child cmd /c
      $inner = 'curl -X POST "' . $url . '" -H "Content-Type: application/json" --data-binary @"' . $jsonFile . '" > NUL 2>&1 ^& del /f /q "' . $jsonFile . '"';
      $full = 'start /B "" cmd /c ' . $inner;
      pclose(popen($full, 'r'));
    } else {
      // Linux/Unix: run curl in background and delete temp file afterwards
      $cmd = 'sh -c ' . escapeshellarg(
        'curl -X POST ' . escapeshellarg($url)
        . ' -H "Content-Type: application/json" --data-binary @' . escapeshellarg($jsonFile)
        . ' >/dev/null 2>&1; rm -f ' . escapeshellarg($jsonFile)
      ) . ' &';
      exec($cmd);
    }

    return true;
  }

}
