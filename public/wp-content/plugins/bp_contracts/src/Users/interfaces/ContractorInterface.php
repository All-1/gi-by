<?php
namespace PersonalAccount\Users\interfaces;
interface ContractorInterface
{
  public function createContract($nameContract);
  public function createDialog($serialNumber, $typeDialog);
  public function renameContract($serialNumber, $newName);
} 