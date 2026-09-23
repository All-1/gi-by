<?php
namespace PersonalAccount\ObjectRelationship\traits;
trait InvoiceAndShipment
{
  private $idCreator;
  private $nameCreator;
  private function getNameCreatorDB()
  {
    $sqlNamePoint = $this->wpdb->get_var("SELECT user_name FROM gi_new_users WHERE id_user='$this->idCreator'");
    return $sqlNamePoint;
  }
  private function assigmentParams($objectRelationshipFromUser, $type)
  {
    $name = 'name' . $type;
    $this->type = $type;
    $this->$name = $objectRelationshipFromUser['name'];
    $this->idCreator = $objectRelationshipFromUser['idCreator'];
    $this->nameCreator = $this->getNameCreatorDB();
  }
}