<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$id = $_POST['id'];

$sql = "DELETE FROM gi_designer_faq WHERE newid = '$id'";
$result = $wpdb->get_results($sql);
?>