<?php
namespace PersonalAccount\Users\traits\capsule;
trait CapsuleContractorParam
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  protected function getRank()
  {
    return $this->getProperty('rank');
  }
  protected function getIdMyManager()
  {
    return $this->getProperty('idMyManager');
  }
  protected function getIdContractorHigherRank()
  {
    return $this->getProperty('idContractorHigherRank');
  }
  // Сеттеры
  protected function setRank($rank)
  {
    $this->setProperty('rank', $rank);
  }
  protected function setIdMyManager($idMyManager)
  {
    $this->setProperty('idMyManager', $idMyManager);
  }
  protected function setIdContractorHigherRank($idContractorHigherRank)
  {
    $this->setProperty('idContractorHigherRank', $idContractorHigherRank);
  }
}