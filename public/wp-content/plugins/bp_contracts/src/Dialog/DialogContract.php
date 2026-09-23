<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Dialog\Dialog;
class DialogContract extends Dialog
{
  protected $idPoint;
  public function __construct($dialog, $closures, $parent)
  {
    parent::__construct($dialog, $closures, $parent);
    $this->idPoint = $this->objectRelationship->getIdPoint();
    $this->pathTableDB = $this->createPathTableDB('contract') . '_' . $this->idPoint;
    $this->initializationMessages();
    
  }
  // saveContractMessageDB - Все сообщения пришедшие в данный диалог записываются в таблицу messages_cotracts_(idPoint)_(year(dateCreation))
  
  //Realise generic method in DBWorker

}