<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
global $lang_adm;
global $wpdb;
/*

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
*/
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
//$user_name = $_POST['user_name'];
$model_name = isset($_POST['model_name']) ? sanitize_text_field($_POST['model_name']) : '';

$date_booking = isset($_POST['date_booking']) ? sanitize_text_field($_POST['date_booking']) : '';
$max_number = "SELECT MAX(order_number) FROM gi_booking";
$max_number_result = $wpdb->get_var($max_number);
$order_number = $max_number_result + 1;
$sql = "INSERT INTO gi_booking (user_id, order_number, model_name, date_booking) VALUES ( '$user_id', '$order_number', '$model_name', '$date_booking')";

$result = $wpdb->get_results($sql);


echo 'Данные успешно добавлены!';
exit();

?>