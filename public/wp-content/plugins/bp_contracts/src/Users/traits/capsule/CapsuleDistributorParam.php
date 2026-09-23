<?php
namespace PersonalAccount\Users\traits\capsule;
trait CapsuleDistributorParam
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  private function getIdDealersUP()
  {
    return $this->getProperty('idDealersUP');
  }
  //Сеттеры
  private function setIdDealersUP($idDealersUP)
  {
    $this->setProperty('idDealersUP', $idDealersUP);
  }
}