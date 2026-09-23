<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Dialog\Dialog;
use PersonalAccount\Dialog\traits\ConsultationOrComplaint;
class DialogShipment extends Dialog
{
  use ConsultationOrComplaint;
  public function __construct($dialog, $closures, $parent)
  {
    parent::__construct($dialog, $closures, $parent);
    $this->pathTableDB = $this->createPathTableDB('shipment');
    $this->initializationMessages();
  }
  // saveShipmentMessageDB - Все сообщения пришедшие в данный диалог записываются в таблицу messages_shipment_(year(dateCreation))
}