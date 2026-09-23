<?php
namespace PersonalAccount\ObjectRelationship\traits\capsule;
trait CapsuleInvoiceShipment
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  private function getIdCreator()
  {
    return $this->getProperty('idCreator');
  }
  private function setIdCreator($idCreator)
  {
    $this->setProperty('idCreator', $idCreator);
  }
  private function getNameCreator()
  {
    return $this->getProperty('nameCreator');
  }
  private function setNameCreator($nameCreator)
  {
    $this->setProperty('nameCreator', $nameCreator);
  }
}