<?php
/*Plugin Name: bp_booking
Description: Раздел "Бронирование" в кабинете дилера
Version: 1.0
Author: Business Park*/

add_shortcode('booking', 'booking');

function booking(){
	require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
	$perpage = 20;
	global $wpdb;
	global $user_role;
	global $lang_adm;

	$Id = get_current_user_id();
	//$user_name = wp_get_current_user();
	$now_time = time();

	$max_number = "SELECT MAX(order_number) FROM gi_booking";
	$max_number_result = $wpdb->get_var($max_number);
	$order_number = $max_number_result + 1;
	if($user_role !== 'designer_architect'){
		$pech_btns = "
		<h3 style='color:red'>!!!На сайте производятся технические работы. Раздел в разработке</h3>
		<div class='control_panel'>
			<div class='generate_id'>
				<input type='text' name='model_name' id='model_name' placeholder='Название модели'>
				<input type='text' name='user_id' style='display:none;' value='$Id'>
				<input type='text' name='user_name' style='display:none;' value='$user_name'>
				<input type='text' name='date_booking' style='display:none;' value='$now_time'>
				<input type='submit' name='generate_id_zakaz' id='id_zakaz' value='Забронировать'>
				<div class='place_id'> </div>
			</div>
			<div></div>
		</div>
		";
		
	}
	else {
		$pech_btns = "";
		$neotgruj = "";
	}

	$last_time = get_user_meta( $Id, 'last_login', true );
	$sql_length = "SELECT * FROM gi_booking WHERE user_id = '$ID'";
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

	
	echo "<h1>$lang_adm->m_booking</h1>";
	$dannie_str = "
		<div style=\"height:62px;\"></div>
	";
	$a.="
	<form method='POST'>
		$pech_btns
		
		<div class='scrolling_div_booking'>
			<div class='table_div'>
				<div class='tr_div_header'>
					<div class='td_div_header booking_checkbox'><input type='checkbox' id='all_check' onclick='allcheck(this);'></div>
					<div class='td_div_header booking_number'>Номер заказа</div>
					<div class='td_div_header booking_name'>Название модели</div>
					<div class='td_div_header booking_date'>Дата бронирования</div>
					<div class='clear-fix'></div>
				</div>";
			$Id = get_current_user_id();
			$sqlcount = "SELECT * FROM gi_booking WHERE user_id = '$Id'";
			$resultcount = $wpdb->get_results($sqlcount);
			
			foreach ($resultcount as $row){
					$order_number = $row->order_number;
					$model_name = $row->model_name;
					$date_booking = $row->date_booking;
					$order_confirm = $row->order_confirm;
					$date_booking = (integer)$date_booking;
					$date_booking = date('d/m/y', $date_booking);
					
					if ($order_confirm !== 'yes'){
						$a.="
						<div class='tr_div_table_booking'>
							<div class='td_div_table booking_checkbox'>
								<input type='checkbox' name='otmetka[]' value='' class='ordersChecboxes ' onclick='mychange();'/>
							</div>
							<div class='td_div_table booking_number'>$order_number</div>
							<div class='td_div_table booking_name'>$model_name</div>
							<div class='td_div_table booking_date'>$date_booking</div>
							<div class='clear-fix'></div>
						</div>";
					}
			}
			
					$a.="
				
			</div>
		</div>
	</form>";
	
	/*
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
*/

    $a .= "
    <script>
    jQuery(document).ready(function($) {
        $('#id_zakaz').on('click', function(e) {
            e.preventDefault();
            
            var formData = {
                'model_name': $('#model_name').val(),
                'user_id': $('input[name=\"user_id\"]').val(),
                'user_name': $('input[name=\"user_name\"]').val(),
                'date_booking': $('input[name=\"date_booking\"]').val()
            };
            
            $.ajax({
                type: 'POST',
                url: '../wp-content/plugins/bp_booking/dannie.php',
                data: formData,
                success: function(response) {
                    alert(response);
                    window.location.reload();
                },
                error: function() {
                    alert('Ошибка при отправке данных.');
                }
            });
        });
    });
</script>
    ";
  
	/*
$a.="


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
		url: '../wp-content/plugins/bp_booking/ajaxtable.php',
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
		url: '../wp-content/plugins/bp_booking/ajaxtable.php',
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



</script>
";

*/
echo $a;

}
?>