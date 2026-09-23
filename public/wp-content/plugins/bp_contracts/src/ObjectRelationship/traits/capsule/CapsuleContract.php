<?php
namespace PersonalAccount\ObjectRelationship\traits\capsule;
trait CapsuleContract
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  // Геттеры и сеттеры для всех свойств
  protected function setIdPoint($idPoint)
  {
    $this->setProperty('idPoint', $idPoint);
  }
  protected function getNamePoint()
  {
    return $this->getProperty('pointName');
  }
  protected function setNamePoint($pointName)
  {
    $this->setProperty('pointName', $pointName);
  }
  protected function getNameContract()
  {
    return $this->getProperty('nameContract');
  }
  protected function setNameContract($nameContract)
  {
    $this->setProperty('nameContract', $nameContract);
  }
}