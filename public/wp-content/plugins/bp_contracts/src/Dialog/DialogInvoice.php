<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Dialog\Dialog;
use PersonalAccount\Dialog\traits\ConsultationOrComplaint;
//Метод будет перенесён в методы диалога.
class DialogInvoice extends Dialog
{
  use ConsultationOrComplaint;
  public function __construct($dialog, $closures, $parent)
  {
    parent::__construct($dialog, $closures, $parent);
    $this->pathTableDB = $this->createPathTableDB('invoice');
    $this->initializationMessages();
  }
  // saveInvoiceMessageDB - Все сообщения пришедшие в данный диалог записываются в таблицу messages_invoice_(year(dateCreation))
  public function saveInvoiceMessageDB($dataMessage)
  {

  }
}