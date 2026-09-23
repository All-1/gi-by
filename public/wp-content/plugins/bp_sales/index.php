<?php
/*Plugin Name: bp_sales
Description: Кухни на скидке.
Version: 1.0
Author: Business Park*/

add_shortcode('sales_print', 'print_sales');
add_action('admin_menu', 'add_sales_page');
function add_sales_page() {
    add_menu_page('Кухни на скидке', 'Кухни на скидке', 8, __FILE__, 'my_sales', 'dashicons-cart');
}
function my_sales (){
	echo "
		<h1>Кухни на скидке</h1>
		Установите галочки на те кухни, которые в этом месяце будут на акции. Все остальные кухни автоматически уберутся из списка моделей на скидке.<br><br>
	";
	$a ="
	<form method='POST'>
	";
	global $wpdb;
	$a.="
	<div style='width:100%;'>
	";
	$sql = "SELECT * FROM gi_kitchen";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$name = $row->name;
		$name_eng = $row->name_eng;
		$description = $row->description;
		$avatar = $row->avatar;
		$directory = $row-> folder;
		$type = $row->type;
		$cost = $row->basic_cost;
		$sales = $row->sales;
		$newid = $row->newid;
		if ($sales !=='no'){
			$salesDiv = "
			<div style='position:absolute; top:0; right:0; background:darkred; padding:5px 10px; color:white;'>Скидка: $sales\n%</div>
			";
		}
		else $salesDiv = "";
		$placeholder = "Введите скидку на текущий месяц, %";
		$a.="
		<div style='display:inline-block; margin:10px; position:relative;'>
			<input type='checkbox' name='$newid' checked style='position:absolute; top:10px; left:3px;'>
			$salesDiv
			<div style='width:250px; height:180px; background:url(..$avatar) no-repeat; background-position:center; background-size:cover;'></div>
			<span style='font-size:16px; font-weight:600;'>$name</span><br>
			<input name='$name_eng' value='$sales' placeholder='$placeholder' style='width:100%;'>
		</div>
		";
		if(!empty($_POST[$name_eng]) and !empty($_POST[$newid]) and isset($_POST['go'])){
			$discount = $_POST[$name_eng];
			echo "Установлены новые скидки";
			echo "<script>window.location.reload();</script>";
			$sqlNewSale = "UPDATE gi_kitchen SET sales = '$discount' WHERE newid='$newid'";
			$resultNewsSale = $wpdb->get_results($sqlNewSale);
			continue;
		}
		elseif(empty($_POST[$name_eng]) and empty($_POST[$newid]) and isset($_POST['go'])) {
			$no="no";
			echo "Установлены новые скидки";
			echo "<script>window.location.reload();</script>";
			$sqlDeleteSale = "UPDATE gi_kitchen SET sales = '$no' WHERE newid='$newid'";
			$resultDeleteSale = $wpdb->get_results($sqlDeleteSale);
		}
	}

	$a.="
	</div>
	<input type='submit' name='go' style='margin:10px'>
	</form>";
	echo $a;
}


function print_sales(){
	global $wpdb;
	$sql = "SELECT * FROM gi_kitchen WHERE sales!='no' ORDER BY sales DESC, name ASC";
	$result = $wpdb->get_results($sql);
	$a ="
	<div class='akcii_fw_div'>
	";
	foreach ($result as $row) {
		$name = $row->name;
		$name_eng = $row->name_eng;
		$description = $row->description;
		$avatar = $row->avatar;
		$directory = $row-> folder;
		$type = $row->type;
		$cost = $row->basic_cost;
		$sales = $row->sales;
		$index = home_url();
		$a.="		
		<a href='$index/catalog/$name_eng/' target='_blank'>
		<div class='akciiDiv'>
			<div class='salesImage' style='background:url(..$avatar) no-repeat; background-position:center; background-size:cover;'></div>
			<div class='salesDiv' style=''>-$sales%</div>
			<div style='font-size:22px; font-weight:600; color:#4c4c4c; margin:10px 0 0 0; font-weight:600; padding:16px 32px 32px 32px; line-height:1.2;'>$name <span style='font-size:12px; font-weight:600;'><br>$type</span> </div>
			
			<div style='clear:both'></div>
		</div>
		</a>
		";
	}
	$a.="</div>";
	return $a;
}
?>