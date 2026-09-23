<?php
namespace PersonalAccount\Users;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Users\interfaces\FactoryWorkersInterface;
use PersonalAccount\Users\interfaces\OrdersInterface;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
use PersonalAccount\Users\traits\WorkWithInvoice;

class FactoryWorker extends UserMain implements FactoryWorkersInterface, OrdersInterface
{
  use WorkWithInvoice;
  // use capsuleFactoryWorkersParam;
  /** @var UserUtilities */
  protected $userUtilities;
  protected $factoryWorkers;
  protected $rolesFactory;
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->userUtilities = $this->Container->get('UserUtilities');
    $this->rolesFactory = $this->userUtilities->initializationRolesFactory();
    // Укажите роли, которые вам нужны
    // $this->potentialParticipant = $this->getAnotherFactoryWorkers();
    $this->initialisationInvoices();
    $this->updateCOWorkers();
    $this->allPontsID = $this->DBWorker->selectSimple('Points', null, null, 'id_point');

  }
  // getAnotherFactoryWorker - Могут вызвать в диалог любого сотрудника фабрики. А также дилера и дистрибьютора
  protected function getFactoryWorkersDB()
  {
    $sqlFactoryWorkers = $this->DBWorker->selectResultsFromDBU_2('gi_new_users', 'user_rank', [4, 5, 6, 7, 8, 9, 10], 'id_user');

    $factoryWorkers = $this->userUtilities->packageUser($sqlFactoryWorkers);
    return $factoryWorkers;
  }
  protected function getFactoryWorkersDB_2()
  {
    // $sqlFactoryWorkers = $this->DBWorker->selectResultsFromDBU_2('gi_new_users', 'user_rank', [4, 5, 6, 7, 8, 9, 10], 'id_user');
    $getUsers = $this->userUtilities->getFactoryUsers();
    // $this->CatcherBugs->convPrintLog($getUsers, 'getFactoryWorkersDB', '$getUsers');

    $factoryWorkers = $this->userUtilities->packageUser($getUsers);
    return $factoryWorkers;
  }
  protected function getCoWorkersFactory($factoryWorker)
  {
    $myCoWorkers = [];
    foreach ($factoryWorker as $idUser => $value) {
      if ($this->userRole === 'administrator' || $this->userRole === 'sales_manager' || $factoryWorker[$idUser]['area'] === $this->userArea) {
        $user = $this->userUtilities->packegeUserForClient($factoryWorker, $idUser);
        $user['potencialReplacement'] = $this->getPotencialReplacementServer($factoryWorker, $user['role'], $idUser);
        $myCoWorkers[$idUser] = $user;
      }
    }
    return $myCoWorkers;
  }
  // getPotencialReplacement. Назначить себе замену. (Массово добавить во все свои диалоги специалиста с такой же ролью.)


  // accountDisenable - Отключить на время учётную запись.

  protected function getContractors($idPoint)
  {
    $sqlIdUPDealer = $this->wpdb->get_var("SELECT id_dealer FROM gi_new_users WHERE id_point = '$idPoint'");
    $sqlIdUPDistributor = $this->wpdb->get_var("SELECT id_distributor FROM gi_new_dealers WHERE id_dealer = '$sqlIdUPDealer'");
    $idDealer = $this->wpdb->get_var("SELECT id_user FROM gi_new_users WHERE id_dealer = '$sqlIdUPDealer'");
    $idDistributor = $this->wpdb->get_var("SELECT id_user FROM gi_new_users WHERE id_distributor = '$sqlIdUPDistributor'");
    $idContractors = [$idDealer, $idDistributor];
    return $idContractors;
  }
  protected function updateCOWorkers()
  {
    $this->factoryWorkers = $this->getFactoryWorkersDB_2();
    $this->myCoWorkers = $this->getCoWorkersFactory($this->factoryWorkers);
    $this->potencialReplacement = $this->getPotencialReplacementServer($this->factoryWorkers, $this->userRole, $this->userId);
  }
  // showAllOrders – Видит все заказы фабрики. 
  public function updateForClientStatusWorkers()
  {
    $this->updateCOWorkers();
  }
  public function updateAfterBindUser()
  {
    $this->updateCOWorkers();
    $this->updateStatus();
    $this->updatePoints();
    $this->rank = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'user_rank'); //Простое число.
    $this->connected = !empty($this->rank) ? true : false;
  }
  public function bindOnMyOwn()
  {
    $nameInDB = $this->firstName . ' ' . $this->lastName;
    $values = [$this->userId, $nameInDB, NULL, '', '', '', 'active', 5];
    $this->DBWorker->insertDBU('gi_new_users', $values);
  }
  public function getAllPointsIdFW()
  {
    return $this->allPontsID;
  }
  public function updatePoints()
  {
    $this->allPontsID = $this->DBWorker->selectSimple('Points', null, null, 'id_point');
  }

  public function showOrders()
  {
    // Implementation for showing orders
    return [];
  }
  public function showInvoices()
  {
    $this->getAnalyseInvoices();
    $timeStart_1 = microtime(true);
    $invoicesOnPage = $this->InvoiceWorker->getInvoicesOnPage_2($this->currentPageInvoices, $this->perPageInvoices, $this->searchQueryInvoices);
    $timeEnd_1 = microtime(true);
    $timeStart_2 = microtime(true);
    // $invoicesOnPage = $this->InvoiceWorker->getInvoicesOnPage($this->currentPageInvoices, $this->perPageInvoices, $this->searchQueryInvoices);
    $timeEnd_2 = microtime(true);
    $time_1 = $timeEnd_1 - $timeStart_1;
    $time_2 = $timeEnd_2 - $timeStart_2;
    // $this->CatcherBugs->convPrintLog($time_1, 'showInvoices', '$time_1');
    // $this->CatcherBugs->convPrintLog($time_2, 'showInvoices', '$time_2');
    
    return $invoicesOnPage;
  }
  public function printInvoice($idInvoice)
  {
    // $this->CatcherBugs->convPrintLog($idInvoice, 'printInvoice', '$idInvoice');
    $data = $this->InvoiceWorker->printInvoice($idInvoice);
    return $data;
  }
  private function getAnalyseInvoices()
  {
    
    if ($this->userRole === 'administrator') {
      // $this->CatcherBugs->convPrintLog($this->userRole, 'getAnalyseInvoices', '$this->userRole');
      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-01-2020 00:00:00', '31-12-2020 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-01-2020 00:00:00 to 31-12-2020 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-01-2023 00:00:00', '31-12-2023 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-01-2023 00:00:00 to 31-12-2023 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-01-2024 00:00:00', '31-12-2024 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-01-2024 00:00:00 to 31-12-2024 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-01-2025 00:00:00', '30-06-2025 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-01-2025 00:00:00 to 30-05-2025 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-05-2025 00:00:00', '31-05-2025 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-05-2025 00:00:00 to 31-05-2025 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-06-2025 00:00:00', '30-06-2025 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-06-2025 00:00:00 to 30-06-2025 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-10-2025 00:00:00', '31-10-2025 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-10-2025 00:00:00 to 31-10-2025 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-11-2025 00:00:00', '30-11-2025 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-11-2025 00:00:00 to 30-11-2025 00:00:00');
     
      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-12-2025 00:00:00', '31-12-2025 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      // $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-12-2025 00:00:00 to 31-12-2025 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-01-2026 00:00:00', '31-01-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-01-2026 00:00:00 to 31-01-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-02-2026 00:00:00', '28-02-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-02-2026 00:00:00 to 28-02-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-03-2026 00:00:00', '31-03-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-03-2026 00:00:00 to 31-03-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-04-2026 00:00:00', '30-04-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-04-2026 00:00:00 to 30-04-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-05-2026 00:00:00', '31-05-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-05-2026 00:00:00 to 31-05-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-06-2026 00:00:00', '30-06-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-06-2026 00:00:00 to 30-06-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-07-2026 00:00:00', '31-07-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-07-2026 00:00:00 to 31-07-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-08-2026 00:00:00', '31-08-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-08-2026 00:00:00 to 31-08-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-09-2026 00:00:00', '30-09-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-09-2026 00:00:00 to 30-09-2026 00:00:00');

      $invoicesSums = $this->InvoiceWorker->getInvoicesByDates('01-01-2026 00:00:00', '30-09-2026 00:00:00');
      $sum = $this->InvoiceWorker->getInvoicesSum($invoicesSums);
      $this->CatcherBugs->convPrintLog($sum, 'showInvoices', '01-01-2026 00:00:00 to 30-09-2026 00:00:00');
    }
  }
  public function changePoints($userId, $hisReplacementId)
  {
    $points = $this->DBWorker->selectSimple('gi_new_points', 'id_manager', $userId, 'id_point');
    // $this->CatcherBugs->convPrintLog($points, 'changePoints', '$points');
    // $conditions = $this->DBUtilities->prepareInAndEqual($points, $userId, 'id_point', 'id_manager');
    // $this->CatcherBugs->convPrintLog($conditions, 'changePoints', '$conditions');

    $this->DBWorker->updateDBU('gi_new_points', 'id_manager', $userId, 'id_manager', $hisReplacementId);
    return $points;
  }
}