<?php
namespace PersonalAccount\Workers;
use PersonalAccount\Workers\CatcherBugs;
use PgSql\Lob;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\RSA;
class SSHRemote
{
  private $sshHost;
  private $sshPort;
  private $sshUser;
  private $sshPassword;
  /** @var CatcherBugs */
  private $CatcherBugs;
  public function __construct($ServicesContainer)
  {
// $local_port = 1433; // Локальный порт на вашей машине
// $remote_host = '192.168.3.12'; // Хост БД за SSH-сервером
// $remote_port = 1433; // Порт MSSQL на удаленном сервере

    $this->sshHost = '86.57.128.78';
    // $this->sshHost = '192.168.3.12';
    $this->sshPort = 2023;
    $this->sshUser = 'ssh_user';
    $this->sshPassword = "93;17aZq";
    $this->CatcherBugs = $ServicesContainer->get('CatcherBugs');
  }
  public function sshOpen($sshHost, $sshPort)
  {
    
    $ssh = new SSH2($this->sshHost, $this->sshPort);
    if (!$ssh->login($this->sshUser, $this->sshPassword)) {
      exit('Не удалось подключиться к SSH серверу');
    }
    $ssh->exec("ssh -L $this->sshPort:$this->sshHost:$this->sshPort -fN");

    if ($this->isHostAliveViaSSH($ssh,$sshHost)) {
      // $this->CatcherBugs->convPrintLog($sshHost, 'sshOpen - available', '$sshHost');
    } else {
      // $this->CatcherBugs->convPrintLog($sshHost, 'sshOpen - unavailable', '$sshHost');
    }

    if ($this->isHostAliveViaSSH($ssh,$this->sshHost)) {
      // $this->CatcherBugs->convPrintLog($this->sshHost, 'sshOpen - available', '$sshHost');
    } else {
      // $this->CatcherBugs->convPrintLog($this->sshHost, 'sshOpen - unavailable', '$sshHost');
    }
    // Проброс порта через SSH
    // $ssh->exec("ssh -L $sshPort:$sshHost:$sshPort -fN");

    if (!empty($ssh)) {
      error_log("SSH туннель успешно установлен.");
      return $ssh;
    }
  }
  public function isHostAliveViaSSH($ssh, $remoteHost) 
  {
      $output = $ssh->exec("ping -c 2 " . escapeshellarg($remoteHost));
      return strpos($output, '0% packet loss') !== false;
  }
}