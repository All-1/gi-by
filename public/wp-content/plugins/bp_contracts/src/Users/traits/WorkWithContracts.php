<?php
namespace PersonalAccount\Users\traits;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
use PersonalAccount\Workers\DBWorker;
trait workWithContracts
{
  protected $contractsOnPage;
  protected $perPageContracts;
  protected $currentPageContracts;
  protected $searchQueryContracts;
  protected $filterContracts;
  protected $filterContractsMy;
  // Declare required protected methods
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);

  protected function inicializationContracts()
  {
    $this->perPageContracts = 20;
    $this->currentPageContracts = 1;
    $this->searchQueryContracts = '';
    $this->filterContracts = [];
    $this->filterContractsMy = true;
  }
  public function showContractsOnPage()
  {
    $filters = [
      'My' => $this->filterContractsMy,
      'Search' => $this->searchQueryContracts,
      'perPage' => $this->perPageContracts,
      'currentPage' => $this->currentPageContracts,
    ];
    $this->contractsOnPage = $this->contractsWorker->getContracts($this->userId, $this->allPontsID, $filters);
    return $this->contractsOnPage;
  }
  public function setFilterContracts($filters)
  {
    if (in_array('My', $filters)) {
      $this->filterContractsMy = true;
      // Удаляем 'My' из массива $filters, если он присутствует
      $filters = array_diff($filters, ['My']);
    } else {
      $this->filterContractsMy = false;
    }
    $this->filterContracts = $filters;
    // Обновляем фильтры после удаления 'My'
  }
  public function seeContract($serialNumber)
  {
    if ($this->filterContractsMy) {
      $dialogues = $this->DBWorker->selectSeeContract($this->userId, $serialNumber);
      if (!empty($dialogues)) {
        return true;
      }
      return false;
    }
    return true;
  }
  public function setPerPageContracts($perPageContracts)
  {
    $this->setProperty('perPageContracts', $perPageContracts);
  }
  public function setSearchQueryContracts($searchQuery)
  {
    $this->setProperty('searchQueryContracts', $searchQuery);
  }

  public function setCurrentPageContracts($currentPageContracts)
  {
    $this->setProperty('currentPageContracts', $currentPageContracts);
  }

  protected function setUnreadedDialoguesContracts($unreadedDialoguesContracts)
  {
    $this->setProperty('unreadedDialoguesContracts', $unreadedDialoguesContracts);
  }

  protected function setContracts($contracts)
  {
    $this->setProperty('contracts', $contracts);
  }
  protected function setMarkedContracts($markedContracts)
  {
    $this->setProperty('markedContracts', $markedContracts);
  }
  public function getUnreadedDialoguesContracts()
  {
    return $this->getProperty('unreadedDialoguesContracts');
  }
  public function getContracts()
  {
    return $this->getProperty('contracts');
  }
  public function getMarkedContracts()
  {
    return $this->getProperty('markedContracts');
  }
}