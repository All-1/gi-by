<?php
namespace PersonalAccount\Users\traits;
use PersonalAccount\Utilities\ObjectRelatashionshipService;
// Трейт для дистрибьютора
trait DistributorTrait
{
  
  private function setDefaultName($orders)
  {
    $nameInvoice = '';
    for ($i = 0; $i < count($orders); $i++) {
      $nameInvoice .= $orders[$i]->name;
      if ($i < count($orders) - 1) {
        $nameInvoice .= '-';
      }
    }
    return $nameInvoice;
  }
  // private function getMyInvoices()
  // {
  //   $sqlInvoices = $this->wpdb->get_results("SELECT * FROM gi_new_invoice WHERE id_creator = '$this->userId'");
  //   $myInvoices = objectRelatashionshipService::packagData($sqlInvoices, 'Invoice', 'invoice');
  //   return $myInvoices;
  // }
  // showMyShipments – Видит все свои отгрузки.
  private function getMyShipments()
  {
    $sqlShipments = $this->wpdb->get_results("SELECT * FROM gi_new_shipment WHERE id_creator = '$this->userId'");
    $myShipments = objectRelatashionshipService::packagData($sqlShipments, 'Shipments', 'shipments');
    return $myShipments;
  }
  // createInvoice - Может запросить сформировать счёт по нескольким заказам на тех точках, где он ответственный. 
  public function createInvoice($orders)
  {
    $dateContract = $this->SimpleUtilities->currentTime();
    $nameInvoice = $this->setDefaultName($orders);
    $this->wpdb->query("INSERT INTO gi_new_invoice 
    (name_invoice, id_creator, date_last_activity, date_creation) 
    VALUES ('$nameInvoice', '$this->idPoint', '$dateContract', '$dateContract')");
    $snInvoice = $this->wpdb->insert_id;
    foreach ($orders as $order) {
      $this->wpdb->query("UPDATE gi_new_orders_$order->point SET sn_invoice = '$snInvoice' WHERE order_number = $order->order_number");
    }
    $this->createDialog($snInvoice, 'Invoice');
  }
  // createShipments - Может запросить переговоры по отгрузкам нескольким заказам на тех точках, где он ответственный.
  public function createShipment($orders)
  {
    $dateContract = $this->SimpleUtilities->currentTime();
    $nameShipment = $this->setDefaultName($orders);
    $this->wpdb->query("INSERT INTO gi_new_shipment 
    (name_shipment, id_creator, date_last_activity, date_creation) 
    VALUES ('$nameShipment', '$this->idPoint', '$dateContract', '$dateContract')");
    $snShipment = $this->wpdb->insert_id;
    $this->createDialog($snShipment, 'Shipment');
  }
  // getMyInvoices - Видит выставленный счёт.
  // putProduction - Отправить в производство.
  public function putProduction()
  {

  }
}