<?php
namespace PersonalAccount\Users\traits;
trait WorkWithOrders
{
  protected $orders;
  protected $perPageOrders;
  protected $currentPageOrders;
  protected $searchQueryOrders;
  protected $filterOrders;
  protected $startDateOrders;
  protected $endDateOrders;
  protected $whatSearch;
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  // abstract public function showOrders();
  protected function initialisationOrders()
  {
    $this->perPageOrders = 20;
    $this->currentPageOrders = 1;
    $this->searchQueryOrders = '';
    $this->filterOrders = [];
    $this->startDateOrders = '';
    $this->endDateOrders = '';
  }
  protected function updateOrders($idPoints) {
    $filters = [
      'perPage' => $this->perPageOrders,
      'currentPage' => $this->currentPageOrders,
      'whatSearch' => $this->whatSearch,
      'searchQueryOrders' => $this->searchQueryOrders,
      'startDateOrders' => $this->startDateOrders,
      'endDateOrders' => $this->endDateOrders,
      'filterOrders' => $this->filterOrders,
    ];
    // $this->CatcherBugs->convPrintLog($filters, 'updateOrders', '$filters');
    $this->orders = $this->ordersWorker->getOrders($idPoints, $filters);
  }
  public function showOrdersUser()
  {
    if ($this->connected) {
      $whose = $this->UserUtilities->partyVerification($this->userRole);
      if ($whose === 'factory_worker') {
        $this->updateOrders($this->allPontsID);
      } elseif ($whose === 'contractor') {
        $this->updateOrders($this->idPoint);
      }
      return $this->orders;
    }
  }
  public function printReportOrdersWWO($orders)
  {
    $sqlOrders = $this->ordersWorker->getOrdersReport($orders);
    $preparedOrders = $this->ordersWorker->prepareOrdersForPrint($sqlOrders);
    return $preparedOrders;
  }
  public function packageInfoOrdersWWO($orders)
  {
    $ordersPackage = $this->ordersWorker->getInfoOrdersUP($orders);
    return $ordersPackage;
  }
  public function setPerPageOrders($perPageOrders)
  {
    $this->setProperty('perPageOrders', $perPageOrders);
  }
  public function setCurrentPageOrders($currentPageOrders)
  {
    $this->setProperty('currentPageOrders', $currentPageOrders);
  }
  public function setFilterOrders($filter)
  {
    $this->setProperty('filterOrders', $filter);
  }
  public function setSearchQueryOrders($searchQuery)
  {
    $this->setProperty('searchQueryOrders', $searchQuery);
  }
  public function setStartDateOrders($date)
  {
    $this->setProperty('startDateOrders', strtotime($date));
  }
  public function setEndDateOrders($date)
  {
    $this->setProperty('endDateOrders', strtotime($date) +  86399);
  }
  public function setConditionForSearchOrders($whatSearch)
  {
    $this->setProperty('whatSearch', $whatSearch);
  }
}