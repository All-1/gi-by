<?php
namespace PersonalAccount\Users\traits;
trait workWithInvoice
{
  protected $perPageInvoices;
  protected $currentPageInvoices;
  protected $searchQueryInvoices;
  //SetParam
  private function initialisationInvoices()
  {
    $this->perPageInvoices = 20;
    $this->currentPageInvoices = 1;
    $this->searchQueryInvoices = '';
  }
  public function setPerPageInvoices($perPageInvoices)
  {
    $this->setProperty('perPageInvoices', $perPageInvoices);
  }
  public function setCurrentPageInvoices($currentPageInvoices)
  {
    $this->setProperty('currentPageInvoices', $currentPageInvoices);

  }
  public function setSearchQueryInvoices($searchQueryInvoices)
  {
    $this->setProperty('searchQueryInvoices', $searchQueryInvoices);
  }
}
