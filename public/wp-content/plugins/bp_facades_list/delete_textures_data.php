<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$thisid = $_POST['thisid'];
$thisfile = $_POST['thisfile'];

$texture = "../wp-content/uploads/facades_list/textures/" . $thisfile;
$thumb = "../wp-content/uploads/facades_list/thimbnails/" . $thisfile;

$sql = "DELETE FROM gi_facades_list WHERE newid = '$thisid'";

unlink($texture);
unlink($thumb);

$result=$wpdb->get_results($sql);

?>