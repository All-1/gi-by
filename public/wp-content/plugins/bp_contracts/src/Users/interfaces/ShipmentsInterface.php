<?php
namespace PersonalAccount\Users\interfaces;
interface ShipmentsInterface
{
  public function showShipments();
  public function showShipment($serialNumber);
} 