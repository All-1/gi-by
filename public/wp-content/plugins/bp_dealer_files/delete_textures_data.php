<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$thisid = $_POST['thisid'];
$thisfile = $_POST['thisfile'];

$texture = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/textures/" . $thisfile;
$thumb = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/thimbnails/" . $thisfile;

$sql = "DELETE FROM gi_designer_textures WHERE newid = '$thisid'";

unlink($texture);
unlink($thumb);

$result=$wpdb->get_results($sql);

?>