<?php
namespace PersonalAccount\ObjectRelationship;
use PersonalAccount\ObjectRelationship\traits\InvoiceAndShipment;
use PersonalAccount\ObjectRelationship\traits\capsule\CapsuleInvoiceShipment;
class Shipment extends ObjectRelationship
{
  // use CatcherBugsTemporary;
  use invoiceAndShipment;
  use capsuleInvoiceShipment;
  private $nameShipment;
  public function __construct($paramCurrentObject, $container, $parent)
  {
    parent::__construct($paramCurrentObject, $container, $parent);
    $this->assigmentParams($paramCurrentObject, 'Shipment');
    $this->dataDialogues = $this->getDialogues('Shipment');
    //$this->dialogues = $this->callDialogues($this->dataDialogues);
  }
  private function getNameShipment()
  {
    return $this->getProperty('nameShipment');
  }
  private function setNameShipment($nameShipment)
  {
    $this->setProperty('nameShipment', $nameShipment);
  }
  public function getOutsieParamInvoice()
  {
    $shipment[$this->serialNumber] = [
      'linkInvoice' => $this,
      'serialNumber' => $this->serialNumber,
      'nameShipment' => $this->nameShipment,
      'dateLastActivity' => $this->dateLastActivity,
      'dateLastActivityTimestamp' => $this->dateLastActivityTimestamp,
      'dateCreation' => $this->dateCreation,
      'dateCreationTimestamp' => $this->dateCreationTimestamp,
      'idCreator' => $this->idCreator,
      'nameCreator' => $this->nameCreator,
    ];
    return $shipment;
  }
}