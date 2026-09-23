<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$thisid = $_POST['thisid'];
$thisname = $_POST['thisname'];
$thistype = $_POST['thistype'];
$kitchensstring = $_POST['kitchensstring'];

$sql = "UPDATE gi_designer_textures SET name = '$thisname', type='$thistype', kitchens='$kitchensstring' WHERE newid = '$thisid'";
$result = $wpdb->get_results($sql);

?>