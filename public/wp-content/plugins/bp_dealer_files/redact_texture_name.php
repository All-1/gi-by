<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$thisid = $_POST['thisid'];
$thisname = $_POST['thisname'];

$sql = "UPDATE gi_designer_textures SET name = '$thisname' WHERE newid = '$thisid'";
$result = $wpdb->get_results($sql);

?>