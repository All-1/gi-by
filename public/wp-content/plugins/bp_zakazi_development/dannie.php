<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
global $lang_adm;
global $wpdb;
$numbers = array_unique($_POST['numbers']);
$user = $_POST['user'];
$str_num = implode(',', $numbers);
$netto = 0;
$brutto = 0;
$volume = 0;

//echo $str_num;
$sql = "SELECT * FROM temporary_$user WHERE id IN ($str_num)";
$result = $wpdb->get_results($sql);
//var_dump($result);
foreach($result as $row){
	
	$my_brutto = $row->brutto;
	$my_netto = $row->netto;
	$my_volume = $row->volume;
	if(is_numeric($my_brutto)){$brutto += $my_brutto;}
	if(is_numeric($my_netto)){$netto += $my_netto;}
	if(is_numeric($my_volume)){$volume += $my_volume;}
}
echo "
	<div style='width:max-content; display:inline-block; padding:5px 15px; background:#e78c68; color:#fff; margin:8px 16px 8px 0;'>$lang_adm->mz_netto: $netto</div>
	<div style='width:max-content; display:inline-block; padding:5px 15px; background:#e78c68; color:#fff; margin:8px 16px 8px 0;'>$lang_adm->mz_brutto: $brutto</div>
	<div style='width:max-content; display:inline-block; padding:5px 15px; background:#e78c68; color:#fff; margin:8px 16px 8px 0;'>$lang_adm->mz_value: $volume</div><br><br>
";

?>