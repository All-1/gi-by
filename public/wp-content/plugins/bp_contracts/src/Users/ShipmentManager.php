<?php
namespace PersonalAccount\Users;
use PersonalAccount\Users\traits\WorkWithShipments;

class ShipmentManager extends FactoryWorker
{
  use WorkWithShipments;
  public function __construct($idUser, $closures)
  {
    parent::__construct($idUser, $closures);
    $this->initialisationShipments();
  }
  // getAllShipments – видит все диалоги по отгрузкам.
  private function getMyShipments()
  {
    $sqlShipments = $this->wpdb->get_results("SELECT * FROM gi_new_shipment 
      ORDER BY date_last_activity DESC");
    return $sqlShipments;
  }
}