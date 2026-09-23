<?php
namespace PersonalAccount\ObjectRelationship;
use PersonalAccount\ObjectRelationship\traits\InvoiceAndShipment;
use PersonalAccount\ObjectRelationship\traits\capsule\CapsuleInvoiceShipment;
class Invoice extends ObjectRelationship
{
  use invoiceAndShipment;
  use capsuleInvoiceShipment;
  // use CatcherBugsTemporary;
  private $nameInvoice;
  public function __construct($paramCurrentObject, $container, $parent)
  {
    parent::__construct($paramCurrentObject, $container, $parent);
    $this->assigmentParams($paramCurrentObject, 'Invoice');
    $this->dataDialogues = $this->getDialogues('Invoice');
  }
  private function getNameInvoice()
  {
    return $this->getProperty('nameInvoice');
  }
  private function setNameInvoice($nameInvoice)
  {
    $this->setProperty('nameInvoice', $nameInvoice);
  }
  // createDialogInvoice - при создании сразу создаёт dialogInvoice
  // createInvoiceFile - Создание текстового файла из номеров заказов и ID.
  public function getOutsideParamInvoice()
  {
    $invoice[$this->serialNumber] = [
      'linkInvoice' => $this,
      'serialNumber' => $this->serialNumber,
      'nameInvoice' => $this->nameInvoice,
      'dateLastActivity' => $this->dateLastActivity,
      'dateLastActivityTimestamp' => $this->dateLastActivityTimestamp,
      'dateCreation' => $this->dateCreation,
      'dateCreationTimestamp' => $this->dateCreationTimestamp,
      'idCreator' => $this->idCreator,
      'nameCreator' => $this->nameCreator,
    ];
    return $invoice;
  }

}