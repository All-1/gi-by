<?php
/*Plugin Name: bp_zakazi
Description: Раздел "Мои Заказы" в кабинете дилера
Version: 1.0
Author: Business Park*/

add_shortcode('moi_zakazi', 'moi_zakazi');

function moi_zakazi(){
	$perpage = 20;
	global $wpdb;
	global $user_role;
	global $lang_adm;
	if($user_role !== 'designer_architect'){
		$pech_btns = "
		<div class='pechat_buttons'>
			<button name='generate_doc' id='vedom' value='vedom' type='submit'>$lang_adm->vedomost</button>
			<button name='generate_doc' id='print' value='print'>$lang_adm->pechat</button>
		</div>
		";
		$neotgruj = "
		<div class='neotruz_checkbox'><input type='checkbox' name='otgruz' id='otgruz' style='transform:scale(1.5); margin-right:10px; '> $lang_adm->neotgruz </div>
		";
	}
	else {
		$pech_btns = "";
		$neotgruj = "";
	}
	
	$now_time = time();
	$Id = get_current_user_id();
	$last_time = get_user_meta( $Id, 'last_login', true );
	$sql_length = "SELECT DISTINCT point FROM temporary_$Id";
	$result_length = $wpdb->get_results($sql_length);
	$count_tables = count($result_length);
	//$Id = 11;
	if($now_time > $last_time + 1000){
		//echo "Обновляем таблицу<br>";
		update_user_meta( $Id, 'last_login', $now_time);
		$it_is_time = 1;
	}
	elseif($count_tables = 0){
		update_user_meta( $Id, 'last_login', $now_time);
		$it_is_time = 1;
	}
	else {
		//echo "Последняя версия таблицы<br>";
		$it_is_time = 0;
	}
	$url = $_SERVER['DOCUMENT_ROOT']."/db_connect.php";
	//echo "$url";
	require_once("$url");
	
	echo "<h1>$lang_adm->m_zakazi</h1>";
	$dannie_str = "
		<div style=\"height:62px;\"></div>
	";
	$a.="
	<form method='POST' action='/docs_generate.php'>
		$pech_btns
		<input type='text' name='search' id='search' placeholder='$lang_adm->mz_pl_1' style='margin-right:32px; width:250px; height:30px;'/>
		<div class='date_seatch'>
			$lang_adm->po_date_otgruzki: <br> <input type='date' id='date_from' onchange='do_date();'> <input type='date' id='date_to' onchange='do_date();' style='margin-right:16px;'>
		</div>
		$neotgruj
		
		<div id='dannie'>
			$dannie_str
		</div>
		<span style='margin-bottom:16px; display:block;'>*$lang_adm->mz_p_1</span>
		<div class='scrolling_div'>
			<div class='table_div'>
				<div class='tr_div_header'>
					<div class='td_div_header order_checkbox'><input type='checkbox' id='all_check' onclick='allcheck(this);'></div>
					<div class='td_div_header order_nubmer'>№</div>
					<div class='td_div_header order_name'>$lang_adm->name</div>
					<div class='td_div_header order_sost'>$lang_adm->sostoyanie</div>
					<div class='td_div_header order_priem'>$lang_adm->prinyat</div>
					<div class='td_div_header order_cofirm'>$lang_adm->confirm</div>
					<div class='td_div_header order_invoice'>$lang_adm->invoice</div>
					<div class='td_div_header order_payment'>$lang_adm->payment</div>
					<div class='td_div_header order_vipusk'>$lang_adm->vipusk</div>
					<div class='td_div_header order_otgruz'>$lang_adm->otgruzka</div>
					<div class='td_div_header order_tochka'>$lang_adm->tochka</div>
				</div>

				<!--<table class='orders_table'>
					<tr class='header_tr'>

						<td class='order_checkbox'><input type='checkbox' id='all_check' onclick='allcheck(this);'></td>
						<td class='order_nubmer'>№</td>
						<td class='order_name'>$lang_adm->name</td>
						<td class='order_sost'>$lang_adm->sostoyanie</td>
						<td class='order_priem'>$lang_adm->prinyat</td>
						<td class='order_cofirm'>$lang_adm->confirm</td>
						<td class='order_invoice'>$lang_adm->invoice</td>
						<td class='order_payment'>$lang_adm->payment</td>
						<td class='order_vipusk'>$lang_adm->vipusk</td>
						<td class='order_otgruz'>$lang_adm->otgruzka</td>
						<td class='order_tochka'>$lang_adm->tochka</td>
					</tr>	-->
	";


	
	$a.="
				<!--</table>-->
				
				<table id='ajaxtable' class='orders_table'>
				</table>
			</div>
		</div>
	</form>";
	
	$sqlcount = "SELECT * FROM temporary_$Id LIMIT 300";
	$resultcount = $wpdb->get_results($sqlcount);
	$count_data = count($resultcount);
	$count_pages = ceil($count_data/$perpage);
	$i=1;
	$b.="<div style='float:right; margin:20px 2%;' class='pagination'>";
	for($i; $i<=$count_pages; $i++){
		$b.="<button class='pageid' id='pagebtn_$i' value='$i' onclick='get_page(this);'>$i</button>";
	}
	$b.="<div style='clear:both'></div>";
	$b.="</div>";
	$b.="<div id='checkboxesarray'></div>";



$a.="
<script>
function allcheck(obj){
	var checkval = obj.value;
	if (jQuery('#all_check').is(':checked')){
		jQuery('.ordersChecboxes').attr('checked',true);
		
		var searchIDs = jQuery('.ordersChecboxes').map(function(){
		  return jQuery(this).val();
		}).get();
		console.log(searchIDs);
		jQuery.ajax({
			url: '../wp-content/plugins/bp_zakazi/dannie.php',
			type: 'POST',
			data: {numbers:searchIDs, user:$Id},
			cache: false,
			success: function(html){ 
				jQuery('#dannie').html(html);
			} 
		});
	}	
	else{
		jQuery('.ordersChecboxes').attr('checked',false);
		jQuery('#dannie').html('<div style=\"height:62px;\"></div');
		
	}
}

function mychange(){
	
	var check_arr = new Array();
	//alert (check_arr.length);
	jQuery('input:checked').each(function() {
        //check_arr.push(jQuery(this).val());

		var searchIDs = jQuery('.ordersChecboxes:checked').map(function(){
		  return jQuery(this).val();
		}).get();
		console.log(searchIDs);

		function do_dannie(){
			jQuery.ajax({
				url: '../wp-content/plugins/bp_zakazi/dannie.php',
				type: 'POST',
				data: {numbers:searchIDs, user:$Id},
				cache: false,
				success: function(html){ 
					jQuery('#dannie').html(html);
				} 
			});
		}
		setTimeout(do_dannie, 500);
    });
	
	if(check_arr.length == 0){
		//alert('null');
		jQuery('#dannie').html('<div style=\"height:62px;\"></div>');
	}
	
}


var otgruz = jQuery('#otgruz');
var search_input = jQuery('#search');

function get_page(obj){
	
	var search = jQuery('#search').val();
	
	if(jQuery(otgruz).is(':checked')== true) 
	{ 
		var where_otgruz = 'yes';  
	}
	else{
		var where_otgruz = '';
	}
	var pagenumber = obj.value;
	var pageid = document.getElementById(obj.id);
	var firstpage = document.getElementById('pagebtn_1');
	firstpage.style.background='#fff';
	jQuery('.pageid').removeClass('selected');
	jQuery(obj).addClass('selected');
	jQuery.ajax({
		url: '../wp-content/plugins/bp_zakazi/ajaxtable.php',
		type: 'POST',
		data: {page:pagenumber, sqltable:$Id, otgruz:where_otgruz, search:search},
		cache: false,
		success: function(html){ 
			jQuery('#ajaxtable').html(html);
			
		} 
	});
}



function show(){
		
	var date_from = jQuery('#date_from').val();
	var date_to = jQuery('#date_to').val();
	
	var search = jQuery('#search').val();

	if(jQuery(otgruz).is(':checked')== true) 
	{ 
		var where_otgruz = 'yes'; 
		jQuery('.pagination').css('display','none');
	}
	else{
		if(date_from && date_to){
			jQuery('.pagination').css('display','none');
		}
		else {
			jQuery('.pagination').css('display','block');
		}
		var where_otgruz = '';
		
	}
	if(search){
		jQuery('.pagination').css('display','none');
	}
	jQuery.ajax({
		url: '../wp-content/plugins/bp_zakazi/ajaxtable.php',
		type: 'POST',
		data: {page:1, sqltable:$Id, otgruz:where_otgruz, search:search, date_from:date_from, date_to:date_to},
		cache: false,
		success: function(html){ 
			jQuery('#ajaxtable').html(html);
		} 
	});
}
jQuery(document).ready(function(){ 
	show();  
});
jQuery(otgruz).change(function(){ 
	show();
});
jQuery(search).keyup(function(){ 
	show(); 
});
function do_date(){
	var date_from = jQuery('#date_from').val();
	console.log(date_from);
	var date_to = jQuery('#date_to').val();
	if(date_from && date_to){
		show();
		jQuery('.pagination').css('display','none');
	}
	else{
		jQuery('.pagination').css('display','block');
	}
}





/*

function onClickHandler(){
	var button_ved = document.getElementById('vedom');
	var button_print = document.getElementById('print');
	button_ved.style.display='none';
	button_print.style.display='none';	
	var myCheckboxes = new Array();
	jQuery('input:checked').each(function() {
		myCheckboxes.push(jQuery(this).val());
		//alert (myCheckboxes.length);
		if(myCheckboxes.length >= 1){
			button_ved.style.display='block';
			button_print.style.display='block';	
		}

	}	
}



function vedomost(obj){
	var myCheckboxes = new Array();
        jQuery('input:checked').each(function() {
           myCheckboxes.push(jQuery(this).val());
			jQuery.ajax({
				url: '../wp-content/plugins/bp_zakazi/generate_ved.php',
				type: 'POST',
				data: {checkboxes:myCheckboxes, sqltable:$Id},
				cache: false,
				success: function(data){ 
					alert(data);
				} 
			});	
        });
}
*/



</script>
";


echo $a . $b;

}
?>