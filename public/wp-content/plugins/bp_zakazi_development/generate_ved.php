<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
global $lang_adm;

$array_checkboxes = $_POST['checkboxes'];
$userid = $_POST['sqltable'];
$usertable = "temporary_" . $userid;
if (!empty($_POST['checkboxes'])){
	foreach ($array_checkboxes as $number => $name){
		$sql = "SELECT order_number FROM $usertable WHERE id='$name'";
		$result = $wpdb->get_results($sql);
		foreach($result as $row){
			$order_number = $row-> order_number;
			$orders_number_string.="$order_number, ";
		}
	}
	$orders_number_string = substr("$orders_number_string", 0, -2);
	$orders_string = implode(", ", $array_checkboxes); 
	
	
}
else echo "<script>alert('$lang_adm->mz_viberite');</script>";

//require_once '/includes/phpexcel/PHPExcel.php';


?>