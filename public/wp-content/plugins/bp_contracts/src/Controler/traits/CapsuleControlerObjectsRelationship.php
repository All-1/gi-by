<?php
namespace PersonalAccount\Controler\traits;
trait CapsuleControlerObjectsRelationship
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  // Геттеры и сеттеры для свойств $allPoints
  private function getAllPoints()
  {
    return $this->getProperty('allPoints');
  }
  private function setAllPoints($allPoints)
  {
    $this->setProperty('allPoints', $allPoints);
  }
  // Геттеры и сеттеры для свойств $allShipments
  private function getAllShipments()
  {
    return $this->getProperty('allShipments');
  }
  private function setAllShipments($allShipments)
  {
    $this->setProperty('allShipments', $allShipments);
  }
  // Геттеры и сеттеры для свойств $allInvoices
  private function getAllInvoices()
  {
    return $this->getProperty('allInvoices');
  }
  private function setAllInvoices($allInvoices)
  {
    $this->setProperty('allInvoices', $allInvoices);
  }
  // Геттеры и сеттеры для свойств $contractsForFactory
  private function getContractsForFactory()
  {
    return $this->contractsForFactory;
  }

  // Геттеры и сеттеры для свойств $orderForFactory
  private function getOrderForFactory()
  {
    return $this->getProperty('orderForFactory');
  }
  private function setOrderForFactory($orderForFactory)
  {
    $this->setProperty('orderForFactory', $orderForFactory);
  }
}