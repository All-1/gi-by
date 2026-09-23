<?php
namespace PersonalAccount\Users\interfaces;
interface InvoicesInterface
{
  public function showInvoices();
  public function showInvoice($serialNumber);
} 