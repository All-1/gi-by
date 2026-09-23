<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$thisid = $_POST['thisid'];
$thisname = $_POST['thisname'];
$thistype = $_POST['thistype'];
$thisrank = $_POST['thisrank'];
$thisdescr = $_POST['thisdescr'];
$kitchensstring = $_POST['kitchensstring'];
$new = $_POST['newfacade'];
$silver = $_POST['silver'];
$antibac = $_POST['antibac'];

$sql = "UPDATE gi_facades_list SET `name` = '$thisname', `type`='$thistype', `kitchens`='$kitchensstring', `facade_rank`='$thisrank', `description`='$thisdescr', `newfacade`='$new', `antibac`='$antibac', `silver`='$silver' WHERE `newid` = '$thisid'";
$result = $wpdb->get_results($sql);

error_log($_POST['thisid']);
?>