<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$id = $_POST['id'];
$q = $_POST['q'];
$a = $_POST['a'];
$r = $_POST['r'];

$sql = "UPDATE gi_designer_faq SET question = '$q', answer = '$a', rank = '$r' WHERE newid = '$id'";
$result = $wpdb->get_results($sql);
?>