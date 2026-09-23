<?php
namespace PersonalAccount\Users\traits\capsule;
trait CapsuleDealerParam
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);

  // Геттеры
  protected function getIdUP()
  {
    return $this->getProperty('idUP');
  }
  protected function getMyWorkersId()
  {
    return $this->getProperty('myWorkersId');
  }
  // Сеттеры
  protected function setIdUP($idUP)
  {
    $this->setProperty('idUP', $idUP);
  }
  protected function setMyWorkersId($myWorkersId)
  {
    $this->setProperty('myWorkersId', $myWorkersId);
  }
}