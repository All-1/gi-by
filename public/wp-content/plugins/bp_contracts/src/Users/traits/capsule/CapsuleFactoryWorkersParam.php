<?php
namespace PersonalAccount\Users\traits\capsule;
trait CapsuleFactoryWorkersParam
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  private function getPotencialReplacement()
  {
    return $this->getProperty('potencialReplacement');
  }
  private function getRolesFactory()
  {
    return $this->getProperty('rolesFactory');
  }
  //Сеттеры
  protected function setPotencialReplacement($potencialReplacement)
  {
    $this->setProperty('potencialReplacement', $potencialReplacement);
  }
}