<?php
namespace PersonalAccount\Workers;

use PersonalAccount\Core\Container;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Workers\UPWorker;

use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
use PersonalAccount\Utilities\DataUtilities;

class InvoiceWorker
{
  /** @var Container */
  private $Container;
  /** @var DBWorker */
  private $DBWorker;
  /** @var CatcherBugs */
  private $CatcherBugs;
  /** @var SimpleUtilities */
  private $SimpleUtilities;
  /** @var DBUtilities */
  private $DBUtilities;
  /** @var UPWorker */
  private $UPWorker;
  /** @var DataUtilities */
  private $DataUtilities;
  /** @var UserCollector */
  private $UserCollector;
  public function __construct(Container $Container)
  {
    $this->Container = $Container;
    $this->DBWorker = $this->Container->get('DBWorker');
    $this->CatcherBugs = $this->Container->get('CatcherBugs');
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
    $this->DBUtilities = $this->Container->get('DBUtilities');
    $this->UPWorker = $this->Container->get('UPWorker');
    $this->DataUtilities = $this->Container->get('DataUtilities');
  }
  private function chooseCurrency($currId)
  {
    $currency = '';
    switch ($currId) {
      case '':
        $currency = 'Фирмы нет в таблице Firms';
        break;
      case 0:
        $currency = 'EUR';
        break;
      case 1:
        $currency = 'BYN';
        break;
      case 2:
        $currency = 'RUB';
        break;
    }
    return $currency;
  }

  public function getInvoicesByDates($dateFrom, $dateTo)
  {
    $dateFrom = is_int($dateFrom) ? $dateFrom : strtotime($dateFrom);
    $dateTo = is_int($dateTo) ? $dateTo : strtotime($dateTo);
    $dateFrom = date('Y-m-d H:i:s', $dateFrom);
    $dateTo = date('Y-m-d H:i:s', $dateTo);
    $conditions = $this->DBUtilities->prepareBetween('invoice_date', $dateFrom, $dateTo);
    $invoices = $this->DBWorker->selectUni('Invoices', $conditions, 'invoice_sum');
    return $invoices;
  }
  public function getInvoicesSum($arraySum)
  {
    $sum = 0;
    $count = count($arraySum);
    foreach ($arraySum as $item) {
      $sum += $item;
    }
    return [$sum, $count];
  }
  public function getInvoicesOnPage_2($currentPage, $perPage, $searchQuery, $idFirm = null)
  {
    $idFirm = !empty($idFirm) ? $this->SimpleUtilities->toArray($idFirm) : null;
    $idFirm = !empty($idFirm) ? implode(',', $idFirm) : null;
    $filters = [
      'currentPage' => $currentPage,
      'perPage' => $perPage,
      'searchQuery' => $searchQuery,
      'idFirm' => $idFirm
    ];
    $invoices = $this->DBWorker->selectInvoices($filters);
    $countInvoices = $this->DBWorker->selectCountInvoices($filters);
    $countInvoices = intval($countInvoices) / $perPage;
    $countInvoices = ceil($countInvoices);
    $invoices[] = ['countPage' => $countInvoices];
    // $this->CatcherBugs->convPrintLog($invoices, 'getInvoicesOnPage_2', '$invoices');
    return $invoices;
  }
  public function printInvoice($idInvoice)
  {
    // $this->CatcherBugs->convPrintLog($idInvoice, 'printInvoice', '$idInvoice');
    $orders = $this->UPWorker->getInvoiceOrdersFromUP($idInvoice);
    $invoice = $this->DBWorker->selectSimple('Invoices', 'id_invoice', $idInvoice);
    // $this->CatcherBugs->convPrintLog($invoice, 'printInvoice', 'invice');
    $firmid = $invoice['firmId'];
    

    $firmDetails = $this->DBWorker->selectSimple('Firms', 'id_firm', $firmid);
    $geosDetails = $this->DBWorker->selectSimple('Firms', 'id_firm', 1);

    $invoice['currency'] = $this->chooseCurrency($firmDetails['currId']);

    $data = [
      'firmDetails' => $firmDetails,
      'geosDetails' => $geosDetails,
      'orders' => $orders,
      'invoice' => $invoice
    ];

    // $this->CatcherBugs->convPrintLog($data, 'printInvoice', 'data');
    // $this->CatcherBugs->convPrintLog($geosDetails, 'printInvoice', 'geosDetails');
    return $data;
  }

}