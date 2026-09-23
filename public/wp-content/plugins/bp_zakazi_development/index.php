<?php
/*Plugin Name: bp_zakazi_development
Description: Раздел "Мои Заказы" в кабинете дилера
Version: 1.0
Author: Business Park*/

add_shortcode('moi_zakazi_development', 'moi_zakazi_development');

function moi_zakazi_development(){
	$perpage = 20;
	global $wpdb;
	global $user_role;
	global $lang_adm;
	if($user_role !== 'designer_architect'){
		$pech_btns = "
		<div class='pechat_buttons_new'>
			<button name='generate_doc' id='vedom_new' value='vedom' type='submit'>$lang_adm->vedomost</button>
			<button name='generate_doc' id='print_new' value='print'>$lang_adm->pechat</button>
		</div>
		";
		$neotgruj = "
		<div class='neotruz_checkbox_new'><input type='checkbox' name='otgruz' id='otgruz_new'> $lang_adm->neotgruz </div>
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
	
	$a.="
	<h1 class='my_order_h1'>$lang_adm->m_zakazi</h1>
	<div class='product_time'>Срок изготовления заказа с момента передачи в прозводство: <span class='amount_days'>60 дней</span></div>
	";
	$dannie_str = "
		<div></div>
	";
	$a.= do_shortcode('[modal_window_contracts]');
	$a.="
	<form method='POST' action='/docs_generate.php'>
		<div class='header_orders'>
			<div class='header_orders_left'>
				$pech_btns
				<div class='search_orders'>
					<input type='text' name='search' id='search_new' placeholder='$lang_adm->mz_pl_1'/>
				</div>
			</div>
			<div class='header_orders_center'>
				<div class='header_orders_center_row'></div>
				<div class='header_orders_center_row'>
					<div class='date_seatch_new'>
						$lang_adm->po_date_otgruzki: <br> <input type='date' id='date_from_new' onchange='do_date();'> <input type='date' id='date_to_new' onchange='do_date();' style='margin-right:16px;'>
					</div>
					$neotgruj
				</div>
			</div>
			<div class='header_orders_right'>
				<div class='radio_buttons'>	
					<input type='radio' name='order_stage' id='confirm' value='confirm' checked>
					<label for='confirm'>Подтверждён</label>
				</div>
				<div class='radio_buttons'>
					<input type='radio' name='order_stage' id='paid' value='paid'>
					<label for='paid'>Оплачен</label>
				</div>
				<div class='radio_buttons'>
					<input type='radio' name='order_stage' id='production' value='production'>
					<label for='production'>В производстве</label>
				</div>
			</div>
		</div>
				
		
		
		<div id='dannie'>
			$dannie_str
		</div>
		<span style='margin-bottom:16px; display:block;'>*$lang_adm->mz_p_1</span>
		<div class='scrolling_div_order'>
			<div class='table_div_order'>
				<div class='tr_div_header_order'>
					<div class='td_div_header_order'><input type='checkbox' id='all_check' onclick='allcheck(this);'></div>
					<div class='td_div_header_order'>Меню</div>
					<div class='td_div_header_order'>№<br> дог.</div>
					<div class='td_div_header_order'>Имя<br> дог.</div>
					<div class='td_div_header_order'>№<br> заказа</div>
					<div class='td_div_header_order'>Дата<br> создания</div>
					<div class='td_div_header_order'>Дата<br> проформы</div>
					<div class='td_div_header_order'>Дата<br> подтв-ия</div>
					<div class='td_div_header_order'>Дата<br> готов-ти</div>
					<div class='td_div_header_order'>Готовность</div>
					<div class='td_div_header_order'>Дата<br> отгрузки</div>
					<div class='td_div_header_order'>Активность</div>
					<div class='td_div_header_order'>Состояние заказа</div>
				</div>
				<div class='tr_div_table_order'>
					<div class='td_div_table_order'><input type='checkbox' id='all_check' onclick='allcheck(this);'></div>
					<div class='td_div_table_order'><a class='links_menu' href='#'>Подтвердить проформу</a><a class='links_menu' href='#'>Скачать проформу</a></div>
					<div class='td_div_table_order'>000001</div>
					<div class='td_div_table_order'><a href='#' onclick='openModalWindow();'>Сазонов-Петроград</a></div>
					<div class='td_div_table_order'>ФИ-948/16</div>
					<div class='td_div_table_order'>23-12-2023</div>
					<div class='td_div_table_order'>23-12-2023</div>
					<div class='td_div_table_order'>23-12-2023</div>
					<div class='td_div_table_order'>23-12-2023</div>
					<div class='td_div_table_order'>не готов</div>
					<div class='td_div_table_order'>23-12-2023</div>
					<div class='td_div_table_order'>23-12-2023</div>
					<div class='td_div_table_order'>Производство</div>
				</div>

				
	";


	
	$a.="
				
				
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
			url: '../wp-content/plugins/bp_zakazi_development/dannie.php',
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
				url: '../wp-content/plugins/bp_zakazi_development/dannie.php',
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
		url: '../wp-content/plugins/bp_zakazi_development/ajaxtable.php',
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
		url: '../wp-content/plugins/bp_zakazi_development/ajaxtable.php',
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
				url: '../wp-content/plugins/bp_zakazi_development/generate_ved.php',
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

$a .= $b;
return $a;

}
?>