<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\interfaces\DealerInterface;
use PersonalAccount\Users\traits\DealerTrait;
// Дилер
class Dealer extends Contractor implements DealerInterface
{
  use DealerTrait;
  // use capsuleDealerParam;
  // Методы и свойства для дилера
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->inicializationDealer();
    $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
  }
  public function updateAfterBindUser()
  {
    $this->idUP = $this->DBWorker->selectSimple('Users', 'id_user', $this->userId, 'id_dealer');
    $this->updateMyManager();
    $this->updateWorkers();
    $this->idContractorHigherRank = $this->getContractorHigherRankDB($this);
    $this->updatePoints();
    $this->rank = $this->DBWorker->selectVarSimple('Users', 'id_user', $this->userId, 'user_rank'); //Простое число.
    $this->connected = !empty($this->rank) ? true : false;
  }
}
