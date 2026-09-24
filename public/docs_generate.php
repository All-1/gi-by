<?php

require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$array_checkboxes = $_POST['otmetka'];
$userid = $_POST['sqltable'];
$usertable = "temporary_$userid";
if (!empty($_POST['otmetka'])){
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
if($_POST['generate_doc'] == 'vedom'){
	include('vedomost.php');
}
if($_POST['generate_doc'] == 'print'){
	include('print.php');
}

?>