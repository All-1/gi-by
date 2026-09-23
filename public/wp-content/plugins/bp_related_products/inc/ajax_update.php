<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

global $wpdb;

$data = $_POST;
$files = $_FILES;

$moderateid = isset($data['id']) ? intval($data['id']) : 0;
$newname = sanitize_text_field($data['name'] ?? '');
$newdescr = sanitize_textarea_field($data['descr'] ?? '');
$newcost = sanitize_text_field($data['cost'] ?? '');
$newsales = sanitize_text_field($data['sales'] ?? '');
$deleteGallery = $data['deleteGallery'] ?? [];
$newFiles = $_FILES['newFiles'] ?? [];
$commodeNameEng = $data['commodeNameEng'] ?? '';


$newFileName = addNewFiles($newFiles, $commodeNameEng, $moderateid);
if (!empty($newFileName)) {
  $newFileThumbName = str_replace('fullsize', 'thumb', $newFileName);
  $sqlUpdate = "UPDATE gi_commode SET avatar = '$newFileName', avatar_thumb = '$newFileThumbName' WHERE newid = '$moderateid'";
  $resultUpdate = $wpdb->query($sqlUpdate);
}

$sqlupdate = "UPDATE gi_commode SET name = '$newname', description='$newdescr', cost='$newcost', sales='$newsales' WHERE newid='$moderateid'";
$resultupdate = $wpdb->query($sqlupdate);
if ($resultupdate === false) {
  echo json_encode(['error' => 'Database update failed', 'sql' => $sqlupdate, 'db_error' => $wpdb->last_error]);
  exit;
}

if (!empty($deleteGallery) && is_array($deleteGallery)) {
  foreach ($deleteGallery as $idVisualisation) {
    $sqlSelect = "SELECT * FROM gi_commode_visualisation WHERE id = '$idVisualisation'";
    $resultSelect = $wpdb->get_results($sqlSelect);
    foreach ($resultSelect as $rowSelect) {
      $urlImage = $_SERVER['DOCUMENT_ROOT'] . $rowSelect->url_image;
      $urlThumb = $_SERVER['DOCUMENT_ROOT'] . $rowSelect->url_thumb;
      if (file_exists($urlImage)) {
        // echo $urlImage;
        unlink($urlImage);
      }
      if (file_exists($urlThumb)) {
        // echo $urlThumb;
        unlink($urlThumb);
      }
    }
    $sqlDelete = "DELETE FROM gi_commode_visualisation WHERE id = '$idVisualisation'";
    $resultDelete = $wpdb->query($sqlDelete);
  }
}

if (!empty($data['nameVisualisation']) && is_array($data['nameVisualisation'])) {
  foreach ($data['nameVisualisation'] as $idVisualisation => $nameVisualisation) {
    $safeName = esc_sql($nameVisualisation);
    $sqlUpdate = "UPDATE gi_commode_visualisation SET name_visual = '$safeName' WHERE id = '$idVisualisation'";
    $resultUpdate = $wpdb->query($sqlUpdate);
  }
}

if (!empty($data['rankVisualisation']) && is_array($data['rankVisualisation'])) {
  foreach ($data['rankVisualisation'] as $idVisualisation => $rankVisualisation) {
    $safeRank = esc_sql($rankVisualisation);
    $sqlUpdate = "UPDATE gi_commode_visualisation SET rank_visual = '$safeRank' WHERE id = '$idVisualisation'";
    $resultUpdate = $wpdb->query($sqlUpdate);
  }
}

// print_r($_POST['new_gallery']);
// print_r($_FILES);
if (!empty($_FILES['new_gallery']) && is_array($_FILES['new_gallery'])) {
  addGalleryCommode($_FILES['new_gallery'], $commodeNameEng, $moderateid);
}

$sql = "SELECT * FROM gi_commode WHERE newid = '$moderateid'";
$result = $wpdb->get_results($sql);
// $result = $result[0];
$sqlVisualisation = "SELECT * FROM gi_commode_visualisation WHERE id_commode = '$moderateid'";
$resultVisualisation = $wpdb->get_results($sqlVisualisation);

if (empty($result)) {
  echo json_encode($moderateid);
  exit;
}

$result['visualisation'] = $resultVisualisation;

echo json_encode($result);


// echo "<script>window.location.reload();</script>";

// if (isset($data['deleteCommode'])) {
//   $deleteId = $data['deleteCommode'];
//   $sqlSelect = "SELECT * FROM gi_commode WHERE newid = '$deleteId'";
//   $sqlUpdate = "DELETE FROM gi_commode WHERE newid = '$deleteId'";
//   $resultUpdate = $wpdb->get_results($sqlUpdate);
//   echo json_encode(['success' => 'Commode deleted successfully']);
// } else {
//   echo json_encode(['success' => 'Update successful']);
// }