<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
global $lang_adm;
global $wpdb;
$page = $_POST['page'];
if(empty($page)){
	$page = 1;
}
$sqltable = $_POST['sqltable'];
$otgruz = $_POST['otgruz'];
$search = $_POST['search'];
$date_to = $_POST['date_to'];
$date_from = $_POST['date_from'];
$date_to =  strtotime ($date_to);
$date_from =  strtotime ($date_from);
if($otgruz == 'yes'){$whereotgruz = "AND status != '$lang_adm->mz_shipped'";} else {$whereotgruz = ''; }
if(!empty($search)){$wheresearch = "AND (order_number LIKE '%$search%' OR client_numer LIKE '%$search%' OR point LIKE '%$search%' OR status LIKE '%$search%')";} else{$wheresearch = '';}
if(!empty($date_from) and !empty($date_to)){$wheredate = "AND (shipping_date >= $date_from AND shipping_date <= $date_to)";} else $wheredate = "";

if($otgruz == 'yes' or !empty($search) or (!empty($date_from) and !empty($date_to))){ $limit=400;} else {$limit=20;}

$from = ($page - 1) * 20;
$sql = "SELECT * FROM temporary_$sqltable WHERE 1 = 1 $whereotgruz $wheresearch $wheredate ORDER BY date_receipt DESC LIMIT $from, $limit";

$result = $wpdb->get_results($sql);
foreach ($result as $row){
	$number = $row->id;
	$order_number = $row->order_number;
	$serial_number = $row->serial_number;
	$client_numer = $row->client_numer;
	$date_receipt = $row->date_receipt;
	$confirm_date = $row->confirm_date;
	$invoice_date = $row->invoice_date;
	$payment_date = $row->payment_date;
	$required_date = $row->required_date;
	$status = $row->status;
	$release_date = $row->release_date;
	$shipping_date = $row->shipping_date;
	if(!empty($serial_number)){
		$serial_number = "($serial_number)";
		
	}
	if(!empty($shipping_date)){
		$shipping_date = (integer)$shipping_date;
		$shipping_date = date('d/m/y', $shipping_date);
	}
	if(!empty($release_date)){
		$release_date = (integer)$release_date;
		$release_date = date('d/m/y', $release_date);
	}
	
	if(!empty($date_receipt)){
		$date_receipt = (integer)$date_receipt;
		$date_receipt = date('d/m/y', $date_receipt);
	}
	if(!empty($confirm_date)){
		$confirm_date = (integer)$confirm_date;
		$confirm_date = date('d/m/y', $confirm_date);
	}
	if(!empty($invoice_date)){
		$invoice_date = (integer)$invoice_date;
		$invoice_date = date('d/m/y', $invoice_date);
	}
	if(!empty($required_date)){
			$required_date = (integer)$required_date;
			$required_date = date('d/m/y', $required_date);
	}
	if(!empty($payment_date)){
		$payment_date = (integer)$payment_date;
		$payment_date = date('d/m/y', $payment_date);
		
	} 

	
	$point = $row->point;
	$brutto = $row->brutto;
	$netto = $row->netto;
	$volume = $row->volume;
	$shipment_password = $row->shipment_password;
	/*идиотская дата*/
	
	
	
	
	$b.="
		<div class='tr_div_table'>
			<div class='td_div_table order_checkbox'>
				<input type='checkbox' name='otmetka[]' value='$number' class='ordersChecboxes' onclick='mychange();'/>
			</div>
			<div class='td_div_table order_nubmer'>$order_number <br> $serial_number</div>
			<div class='td_div_table order_name'>$client_numer</div>
			<div class='td_div_table order_sost'>$status</div>
			<div class='td_div_table order_priem'>$date_receipt</div>
			<div class='td_div_table order_cofirm'>$confirm_date</div>
			<div class='td_div_table order_invoice'>$invoice_date</div>
			<div class='td_div_table order_payment'>$payment_date</div>
			<div class='td_div_table order_vipusk'>$required_date</div>
			<div class='td_div_table order_otgruz'>$shipping_date</div>
			<div class='td_div_table order_tochka'>$point</div>
			<div class='clear-fix'></div>
		</div>


	<!--<tr class='order_tr'>
		<td class='order_checkbox'>
			<input type='checkbox' name='otmetka[]' value='$number' class='ordersChecboxes' onclick='mychange();'/>
		</td>
		<td class='order_nubmer'>$order_number</td>
		<td class='order_name'>$client_numer</td>
		<td class='order_sost'>$status</td>
		<td class='order_priem'>$date_receipt</td>
		<td class='order_cofirm'>$confirm_date</td>
		<td class='order_invoice'>$invoice_date</td>
		<td class='order_payment'>$payment_date</td>
		<td class='order_vipusk'>$required_date</td>
		<td class='order_otgruz'>$shipping_date</td>
		<td class='order_tochka'>$point</td>
	</tr>-->
	";
	
}

echo $b;

?>