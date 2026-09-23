<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Utilities\Utilit;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\ErrorHandler;
class CatcherBugs
{
  use Utilit;
  private $pathLogsError;
  private $Logger;
  public function __construct()
  {
    $this->pathLogsError = ABSPATH . 'wp-content/plugins/bp_contracts/logs/';
    error_log($this->pathLogsError);

    $this->Logger = new Logger('websocket');
    $this->Logger->pushHandler(new StreamHandler($this->pathLogsError . 'websocket_stream_log.log', Logger::DEBUG));
    $this->Logger->info('Logger initialized, log file should now exist');

    // $this->Logger->pushHandler(new StreamHandler($this->pathLogsError . 'websocket_warning_and_error.log',Logger::WARNING));
    // $this->Logger->error('Warnings mode enabled, logging warning and error');

    // $this->Logger->pushHandler( new StreamHandler($this->pathLogsError . 'websocket_error_log.log',Logger::ERROR));
    // $this->Logger->error('Error mode enabled, logging errors only');

    // ErrorHandler::register($this->Logger);
  }
  private function writeLog($chain, $entry)
  {
    if (!is_dir($this->pathLogsError)) {
      if (!@mkdir($this->pathLogsError, 0755, true) && !is_dir($this->pathLogsError)) {
        error_log('Failed to create directory: ' . $this->pathLogsError);
      }
    }
    $pathFile = $this->pathLogsError . $chain . '.txt';
    error_log(print_r($entry, true));
    if (!file_exists($pathFile)) {
      file_put_contents($pathFile, $entry);
    } else {
      file_put_contents($pathFile, $entry, FILE_APPEND);
    }
    // $this->Logger->info($entry);
  }
  private function createEntryInLog($caller, $what, $value, $flag = null)
  {
    $file = $caller['file'] ?? 'Unknown file';
    $line = $caller['line'] ?? 'Unknown line';
    $function = $caller['function'] ?? 'Unknown function';
    $class = $caller['class'] ?? null; // Класс, вызвавший метод
    $type = $caller['type'] ?? '';     // Тип вызова: -> или ::

    $callerInfo = $class ? "$class{$type}$function" : $function;
    $entryFlag = $flag ? " but flag was $flag" : "";
    ob_start();
    var_dump($value);
    $valueDump = trim(ob_get_clean());
    $date = date('Y-m-d H:i:s');
    $entry = "[$date] The line: $line ---> in file $file in Object->method: $callerInfo variable $what === $valueDump $entryFlag\n";
    return $entry;
  }
  public function catchBug($chain, $what, $value, $flag = null)
  {
    $backtrace = debug_backtrace();
    $caller = $backtrace[1];
    $entry = $this->createEntryInLog($caller, $what, $value, $flag);
    if ($flag !== null) {
      if ($flag === true && empty($value)) {
        $this->writeLog($chain, $entry);
      }
    } else {
      if (empty($value)) {
        $this->writeLog($chain, $entry);
      }
    }
  }
  public function analizeChain($chain, $what, $value)
  {
    $backtrace = debug_backtrace();
    $caller = $backtrace[1];
    $entry = $this->createEntryInLog($caller, $what, $value);
    $this->writeLog($chain, $entry);

  }
  public function checkReturn($value, $chain, $what)
  {
    $backtrace = debug_backtrace();
    $caller = $backtrace[1];
    // $entry = $this->createEntryInLog($caller, $what, $value);
    if (!empty($value)) {
      return $value;
    } else {
      // $this->writeLog($chain, $entry);
      return null;
    }
  }
  public function ArrayStructures($array)
  {
    $structure = [];
    foreach ($array as $key => $value) {
      $structure[$key] = is_array($value) ? $this->ArrayStructures($value) : null;
    }
    return $structure;
  }
  public function convPrintLog($value, $where, $what, $marker = 1)
  {
    switch ($marker) {
      case 1:
        $markerStr = '//////////////////';
        break;
      case 2:
        $markerStr = '----------!!!!!!!----------';
        break;
      case 3:
        $markerStr = '#######-----????????----#######';
        break;
    }
    $backtrace = debug_backtrace();
    $caller = $backtrace[1];
    // $entry = $this->createEntryInLog($caller, $what, $value);
    $string = $markerStr . ' ' . $where . ' - ' . $what . ' ' . $markerStr;
    error_log(print_r($string . ' START', true));
    error_log(print_r($value, true));
    error_log(print_r($string . ' FINISH', true));
    $this->Logger->info(print_r($string . ' START', true));
    $this->Logger->info(print_r($value, true));
    $this->Logger->info(print_r($string . ' FINISH', true));
  }
}
