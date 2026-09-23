<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
global $wpdb;
$rus=rusSymbols();
$lat=latSymbols();
	
$name = $_POST['commode_name'];
$alias = "living-" . str_replace($rus, $lat, $name);
$alias = str_replace('---', '-', $alias);
$alias = str_replace('-', '-', $alias);
$sql = "SELECT alias FROM gi_commode WHERE alias = '$alias'";

$result = $wpdb->get_results($sql);
$a = '';
if($result){
	$a.="<span style='color:red'>АААААААААА ТАКОЕ УЖЕ ЕСТЬ!!!!</span>";
	$a.= "<script>jQuery('#check_alias').css('background','red');</script>";
}
else {
	$a.= "<script>jQuery('#check_alias').css('background','#fff');</script>";
}
echo $a;


?>