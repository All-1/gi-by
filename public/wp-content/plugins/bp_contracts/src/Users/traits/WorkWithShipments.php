<?php
namespace PersonalAccount\Users\traits;
use PersonalAccount\Utilities\ObjectRelatashionshipService;

trait workWithShipments
{
  private $unreadedDialoguesShipments; // Получаем Информацию о всех непрочитанных Shipments
  private $shipments;
  private $markShipments;
  abstract protected function getMyShipments();
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  private function initialisationShipments()
  {
    $this->shipments = $this->getMyShipments();
    $this->unreadedDialoguesShipments = objectRelatashionshipService::sortDialogues($this->unreadedDialogues, 'Shipments');
    $this->markedShipments = objectRelatashionshipService::markUnreaded($this->shipments, $this->unreadedDialoguesShipments);
    $this->shipmentsOnPage = objectRelatashionshipService::prepareListObjectsRelatashionShip($this->markedShipments, 20, 1);
  }
  public function showShipmentsOnPage($perPage, $currentPage)
  {
    $invoices = objectRelatashionshipService::prepareListObjectsRelatashionShip($this->markShipments, $perPage, $currentPage);
    $this->setShipments($invoices);
    return $this->getContractsOnPage();
  }
  protected function setShipmentsOnPage($shipmentsOnPage)
  {
    $this->setProperty('shipmentsOnPage', $shipmentsOnPage);
  }
  protected function getShipmentsOnPage()
  {
    return $this->getProperty('shipmentsOnPage');
  }
  public function getUnreadedDialoguesShipments()
  {
    return $this->getProperty('unreadedDialoguesShipments');
  }
  public function getShipments()
  {
    return $this->getProperty('shipments');
  }
  public function getMarkedShipmentss()
  {
    return $this->getProperty('markedShipments');
  }
  //SetParam
  private function setUnreadedDialoguesShipments($unreadedDialoguesShipments)
  {
    $this->setProperty('unreadedDialoguesShipments', $unreadedDialoguesShipments);
  }
  private function setShipments($shipments)
  {
    $this->setProperty('shipments', $shipments);
  }
  private function setMarkedShipmentss($markedShipments)
  {
    $this->setProperty('markedShipments', $markedShipments);
  }
}