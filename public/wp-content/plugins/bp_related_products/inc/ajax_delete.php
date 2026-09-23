<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
global $wpdb;

$data = !empty($_POST) ? $_POST : [];

$id = isset($data['id']) ? intval($data['id']) : 0;

// echo json_encode($id);
$sqlSelect = "SELECT * FROM gi_commode WHERE newid = '$id'";
$resultSelect = $wpdb->get_results($sqlSelect);
$resultSelect = $resultSelect[0];
$avatar = $_SERVER['DOCUMENT_ROOT'] . $resultSelect->avatar;
$avatarThumb = $_SERVER['DOCUMENT_ROOT'] . $resultSelect->avatar_thumb;

if (file_exists($avatar)) {
  unlink($avatar);
}
if (file_exists($avatarThumb)) {
  unlink($avatarThumb);
}
$sql = "DELETE FROM gi_commode WHERE newid = '$id'";
$result = $wpdb->query($sql);


$sqlSelectVisualisation = "SELECT * FROM gi_commode_visualisation WHERE id_commode = '$id'";
$resultSelectVisualisation = $wpdb->get_results($sqlSelectVisualisation);

foreach ($resultSelectVisualisation as $rowSelectVisualisation) {
  $urlImage = $_SERVER['DOCUMENT_ROOT'] . $rowSelectVisualisation->url_image;
  $urlThumb = $_SERVER['DOCUMENT_ROOT'] . $rowSelectVisualisation->url_thumb;
  if (file_exists($urlImage)) {
    unlink($urlImage);
  }
  if (file_exists($urlThumb)) {
    unlink($urlThumb);
  }
}

$sqlVisualisation = "DELETE FROM gi_commode_visualisation WHERE id_commode = '$id'";
$resultVisualisation = $wpdb->query($sqlVisualisation);

echo json_encode($result);