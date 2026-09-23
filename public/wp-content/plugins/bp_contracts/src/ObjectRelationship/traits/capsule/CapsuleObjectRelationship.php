<?php
namespace PersonalAccount\ObjectRelationship\traits\capsule;
trait CapsuleObjectRelationship
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  // Геттеры и сеттеры для всех свойств
  protected function getParent()
  {
    return $this->getProperty('parent');
  }
  protected function setParent($parent)
  {
    $this->setProperty('parent', $parent);
  }
  public function getSerialNumber()
  {
    return $this->serialNumber;
  }
  protected function setSerialNumber($serialNumber)
  {
    $this->setProperty('serialNumber', $serialNumber);
  }
  protected function getSerialNumberForUser()
  {
    return $this->getProperty('serialNumberForUser');
  }
  protected function setSerialNumberForUser($serialNumberForUser)
  {
    $this->setProperty('serialNumberForUser', $serialNumberForUser);
  }
  protected function getDateLastActivity()
  {
    return $this->getProperty('dateLastActivity');
  }
  protected function setDateLastActivity($dateLastActivity)
  {
    $this->setProperty('dateLastActivity', $dateLastActivity);
  }
  protected function getDateLastActivityTimestamp()
  {
    return $this->getProperty('dateLastActivityTimestamp');
  }
  protected function setDateLastActivityTimestamp($dateLastActivityTimestamp)
  {
    $this->setProperty('dateLastActivityTimestamp', $dateLastActivityTimestamp);
  }

  protected function setDateCreation($dateCreation)
  {
    $this->setProperty('dateCreation', $dateCreation);
  }
  protected function getDateCreationTimestamp()
  {
    return $this->getProperty('dateCreationTimestamp');
  }
  protected function setDateCreationTimestamp($dateCreationTimestamp)
  {
    $this->setProperty('dateCreationTimestamp', $dateCreationTimestamp);
  }
  protected function getType()
  {
    return $this->getProperty('type');
  }
  protected function setType($type)
  {
    $this->setProperty('type', $type);
  }
  protected function getIdOrders()
  {
    return $this->getProperty('idOrders');
  }
  protected function setIdOrders($idOrders)
  {
    $this->setProperty('idOrders', $idOrders);
  }
  protected function getDataDialogues()
  {
    return $this->getProperty('dataDialogues');
  }
  protected function setDataDialogues($dataDialogues)
  {
    $this->setProperty('dataDialogues', $dataDialogues);
  }
  protected function getDialogues()
  {
    return $this->getProperty('dialogues');
  }
  protected function setDialogues($dialogues)
  {
    $this->setProperty('dialogues', $dialogues);
  }
}