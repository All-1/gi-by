<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\FactoryWorker;
use PersonalAccount\Users\traits\WorkWithInvoice;

class Bookkeeper extends FactoryWorker
{
  use WorkWithInvoice;
  private $unreadedDialoguesInvoices;
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->initialisationInvoices();
  }
  // getAllInvoices – видит все диалоги по счетам.
  
}