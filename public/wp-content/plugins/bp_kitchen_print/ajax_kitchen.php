<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
use WPFramework\Workers\KitchenWorker;

global $servicesContainer;
/** @var KitchenWorker */
$kitchenWorker = $servicesContainer->get('KitchenWorker');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if(isset($data['value']) && isset($data['where']) && isset($data['nameKitchen'])) {
  $value = $data['value'];
  $where = $data['where'];
  $nameKitchen = $data['nameKitchen'];
  $type = $data['type'] ?? 'kitchen';
  $result = $kitchenWorker->switchWhereLookFor($where, $value, $nameKitchen, $type);
  // $result = [$value, $where, $nameKitchen];
  // error_log(print_r($result, true));
  echo json_encode($result);
  error_log(print_r($result, true));
} else {
  // Return error response if data is missing
  $error = ['error' => 'Missing required parameters'];
  echo json_encode($error);
  error_log('Missing required parameters in ajax_kitchen.php');
}