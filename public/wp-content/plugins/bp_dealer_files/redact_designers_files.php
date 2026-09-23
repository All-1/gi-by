<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
$thisid = $_POST['thisid'];
$thisfile = $_POST['thisfile'];

$sql = "DELETE FROM gi_designer_architect WHERE newid = $thisid";
$result=$wpdb->get_results($sql);

$filedir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/data_dir/" . $thisfile;
//unlink($filedir); //Удаляет если дублируется название в других моделях (косяк с 3д моделями с прямым фасадом и гола-профилем) 
?>