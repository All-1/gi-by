<?php
namespace PersonalAccount\Users\traits;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
use PersonalAccount\Workers\CatcherBugs;
trait ManageContractors
{
  protected $perPageContractors;
  protected $currentPageContractors;
  protected $filterRoleContractors;
  protected $filterStatusContractors;
  protected $searchContractors;
  protected $contractorsOnPage;
  protected $allContractorsWP;
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  protected function inicializationManageContractors()
  {
    $this->perPageContractors = 20;
    $this->currentPageContractors = 1;
    $this->filterRoleContractors = ['distributors'];
    $this->filterStatusContractors = [];
    $this->searchContractors = '';
    $this->filters = [
      'filterRole' => $this->filterRoleContractors,
      'filterStatus' => $this->filterStatusContractors,
      'searchQuery' => $this->searchContractors,
      'perPage' => $this->perPageContractors,
      'currentPage' => $this->currentPageContractors,
    ];
  }
  public function showContractorsMC()
  {
    $this->filters = [
      'filterRole' => $this->filterRoleContractors,
      'filterStatus' => $this->filterStatusContractors,
      'searchQuery' => $this->searchContractors,
      'perPage' => $this->perPageContractors,
      'currentPage' => $this->currentPageContractors,
    ];
    if (!empty($this->manageContractorsWorker)) {
      $this->contractorsOnPage = $this->manageContractorsWorker->sortedContractors($this->filters);
      // $this->CatcherBugs->convPrintLog($this->contractorsOnPage, 'ManageContractors', 'contractorsOnPage');
    } else {
      $this->contractorsOnPage = [];
    }
    // $this->CatcherBugs->convPrintLog($this->contractorsOnPage, 'ManageContractors', 'contractorsOnPage');
    return $this->contractorsOnPage;
  }
  public function setSearchQueryContractors($searchQuery)
  {
    $this->setProperty('searchContractors', $searchQuery);
  }
  public function setPerPageContractorsMC($perPage)
  {
    $this->setProperty('perPageContractors', $perPage);
  }
  public function setCurrentPageContractorsMC($currentPage)
  {
    $this->setProperty('currentPageContractors', $currentPage);
  }
  public function setFilterStatusContractorsMC($filter)
  {
    $this->filterStatusContractors = $filter !== "null" ? [$filter] : [];
  }
  public function setFilterRoleContractorsMC($filter)
  {
    $this->filterRoleContractors = [$filter];
  }
  public function searchForBindContractorsMC($data)
  {
    $paramData = $this->manageContractorsWorker->searchForBindContractors($data);
    return !empty($paramData) ? $paramData : null;
  }
  public function bindContractorsMC($data)
  {
    $idPoint = $this->manageContractorsWorker->bindContractors($data);
    return $idPoint;
  }
  
  public function changeStatusContractorsMC($data)
  {
    $idPoint = $this->manageContractorsWorker->changeStatusContractors($data);
    return $idPoint;
  }
  public function changeUserNameContractorsMC($data)
  {
    $idPoint = $this->manageContractorsWorker->changeUserNameContractors($data);
    return $idPoint;
  }
  public function unbindContractorsMC($data)
  {
    $idPoint = $this->manageContractorsWorker->unbindContractors($data);
    return $idPoint;
  }
}