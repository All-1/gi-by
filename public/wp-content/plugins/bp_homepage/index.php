<?php
/*Plugin Name: bp_templates_redactor
Description: Редактирование контента на страницах с уникальными шаблонами.
Version: 1.0
Author: Business Park*/
add_action('admin_menu', 'add_redactor_page');
function add_redactor_page() {
    add_menu_page('Главная страница', 'Главная страница', 8, __FILE__, 'homepage_redactor', 'dashicons-admin-home', 3);
		add_submenu_page(__FILE__, 'Добавить слайд', 'Добавить слайд', 8, 'add_slide', 'add_slide');
		add_submenu_page(__FILE__, 'Правка слайдов', 'Правка слайдов', 8, 'redact_slide', 'redact_slide');
	add_menu_page('Контакты', 'Контакты', 8, 'contacts', 'contacts_redactor', 'dashicons-groups', 4);
		add_submenu_page('contacts', 'Отделы', 'Отделы', 8, 'otdeli', 'otdeli');
}

function homepage_redactor() {
	global $wpdb;
	$sql = "SELECT * FROM gi_homepage";
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$modern_image = $row->modern_image;
		$classic_image = $row->classic_image;
		$modern_descr = $row->modern_descr;
		$classic_descr = $row->classic_descr;
		$first_header = $row->first_header;
		$first_content = $row->first_content;
		$mater_image_1 = $row->mater_image_1;
		$mater_image_2 = $row->mater_image_2;
		$mater_image_3 = $row->mater_image_3;
		$mater_image_4 = $row->mater_image_4;
		$mater_header_1 = $row->mater_header_1;
		$mater_header_2 = $row->mater_header_2;
		$mater_header_3 = $row->mater_header_3;
		$mater_header_4 = $row->mater_header_4;
		$mater_descr_1 = $row->mater_descr_1;
		$mater_descr_2 = $row->mater_descr_2;
		$mater_descr_3 = $row->mater_descr_3;
		$mater_descr_4 = $row->mater_descr_4;
		$mater_link_1 = $row->mater_link_1;
		$mater_link_2 = $row->mater_link_2;
		$mater_link_3 = $row->mater_link_3;
		$mater_link_4 = $row->mater_link_4;
		$city_image_1 = $row->city_image_1;
		$city_image_2 = $row->city_image_2;
		$city_image_3 = $row->city_image_3;
		$city_image_4 = $row->city_image_4;
		$city_image_5 = $row->city_image_5;
		$city_image_6 = $row->city_image_6;
		$city_name_1 = $row->city_name_1;
		$city_name_2 = $row->city_name_2;
		$city_name_3 = $row->city_name_3;
		$city_name_4 = $row->city_name_4;
		$city_name_5 = $row->city_name_5;
		$city_name_6 = $row->city_name_6;
		$city_link_1 = $row->city_link_1;
		$city_link_2 = $row->city_link_2;
		$city_link_3 = $row->city_link_3;
		$city_link_4 = $row->city_link_4;
		$city_link_5 = $row->city_link_5;
		$city_link_6 = $row->city_link_6;
		$header_2 = $row->header_2;
		$content_2 = $row->content_2;
		$content_2_2 = $row->content_2_2;
		$button_text_content_2 = $row->button_text_content_2;
		$button_link_content_2 = $row->button_link_content_2;
		$utp_image_1 = $row->utp_image_1;
		$utp_image_2 = $row->utp_image_2;
		$utp_image_3 = $row->utp_image_3;
		$utp_header_1 = $row->utp_header_1;
		$utp_header_2 = $row->utp_header_2;
		$utp_header_3 = $row->utp_header_3;
		$utp_descr_1 = $row->utp_descr_1;
		$utp_descr_2 = $row->utp_descr_2;
		$utp_descr_3 = $row->utp_descr_3;
		$utp_link_1 = $row->utp_link_1;
		$utp_link_2 = $row->utp_link_2;
		$utp_link_3 = $row->utp_link_3;
		$header_3 = $row->header_3;
		$content_3 = $row->content_3;
		$button_text_content_3 = $row->button_text_content_3;
		$button_link_content_3 = $row->button_link_content_3;
		$image_content_3 = $row->image_content_3;

	}

	echo "<h1>Редактор главной страницы:</h1>";
	echo "СЛАЙДЕР НА ОТДЕЛЬНОЙ ВКЛАДКЕ <br><br>";
	
	echo "<h2>Стили классика и модерн (2 блока)</h2>";
	$a="
	<form method='POST'>";
	
	//СТИЛИ КУХНИ
	$a.="
	<div style='widtH:98%; background:white; min-height:200px;'>
		<div style='width:calc(50% - 10px); min-height:200px; min-width:400px; display:inline-block; margin-right:10px;'>
			<div style='width:100%; height:300px; background:url($modern_image) no-repeat; background-size:cover; background-position:center;'></div>
			Описание современной кухни:<br>
			<textarea name='modern_descr' required style='width:100%; min-height:70px;'>$modern_descr</textarea>
			Ссылка на картнку из медиафайлов:
			<input name='modern_image' style='width:100%; height:30px;' required value='$modern_image'/>
		</div>
		<div style='width:calc(50% - 15px); min-height:200px; min-width:400px; display:inline-block; margin-left:10px;'>
			<div style='width:100%; height:300px; background:url($classic_image) no-repeat; background-size:cover; background-position:center;'></div>
			Описание классической кухни:<br>
			<textarea name='classic_descr' required style='width:100%; min-height:70px;'>$classic_descr</textarea><br>
			Ссылка на картнку из медиафайлов:
			<input name='classic_image' style='width:100%; height:30px;' required value='$classic_image'/>
		</div>
	</div>
	";
	
	//ПЕРВЫЙ КОНТЕНТ И МАТЕРИАЛЫ
	$a.="
	<h2>Первый контент</h2>
	Заголовок h1:<br>
	<textarea name='first_header' required style='width:98%; min-height:50px;'>$first_header</textarea><br>
	Контент 1: <br>
	<textarea name='first_content' required style='width:98%; min-height:50px;'>$first_content</textarea>
	";
	
	//МАТЕРИАЛЫ
	$a.="
	<h2>Материалы</h2>
	<div style='width:98%; background:white;'>
		<div style='width:calc(25% - 10px); min-width:200px; display:inline-block; margin-right:7px; min-height:100px;'>
			<div style='width:100%; height:200px; background:url($mater_image_1) no-repeat; background-size:cover; background-position:center;'></div>
			Название материала<br>
			<input style='width:100%; height:30px;' name='mater_header_1' required value='$mater_header_1'/><br>
			Описание материала<br>
			<textarea style='width:100%; height:50px;' name='mater_descr_1' required>$mater_descr_1</textarea><br>
			Ссылка на информацию <br>
			<input style='width:100%; height:30px;' name='mater_link_1' required value='$mater_link_1'/><br>
			Ссылка на картнку из медиафайлов:
			<input name='mater_image_1' style='width:100%; height:30px;' required value='$mater_image_1'/>
		</div>
		<div style='width:calc(25% - 10px); min-width:200px; display:inline-block; margin-right:7px; min-height:100px;'>
			<div style='width:100%; height:200px; background:url($mater_image_2) no-repeat; background-size:cover; background-position:center;'></div>
			Название материала<br>
			<input style='width:100%; height:30px;' name='mater_header_2' required value='$mater_header_2'/><br>
			Описание материала<br>
			<textarea style='width:100%; height:50px;' name='mater_descr_2' required>$mater_descr_2</textarea><br>
			Ссылка на информацию <br>
			<input style='width:100%; height:30px;' name='mater_link_2' required value='$mater_link_2'/><br>
			Ссылка на картнку из медиафайлов:
			<input name='mater_image_2' style='width:100%; height:30px;' required value='$mater_image_2'/>
		</div>
		<div style='width:calc(25% - 10px); min-width:200px; display:inline-block; margin-right:7px; min-height:100px;'>
			<div style='width:100%; height:200px; background:url($mater_image_3) no-repeat; background-size:cover; background-position:center;'></div>
			Название материала<br>
			<input style='width:100%; height:30px;' name='mater_header_3' required value='$mater_header_3'/><br>
			Описание материала<br>
			<textarea style='width:100%; height:50px;' name='mater_descr_3' required>$mater_descr_3</textarea><br>
			Ссылка на информацию <br>
			<input style='width:100%; height:30px;' name='mater_link_3' required value='$mater_link_3'/><br>
			Ссылка на картнку из медиафайлов:
			<input name='mater_image_3' style='width:100%; height:30px;' required value='$mater_image_3'/>
		</div>
		<div style='width:calc(25% - 10px); min-width:200px; display:inline-block; margin-right:7px; min-height:100px;'>
			<div style='width:100%; height:200px; background:url($mater_image_4) no-repeat; background-size:cover; background-position:center;'></div>
			Название материала<br>
			<input style='width:100%; height:30px;' name='mater_header_4' required value='$mater_header_4'/><br>
			Описание материала<br>
			<textarea style='width:100%; height:50px;' name='mater_descr_4' required>$mater_descr_4</textarea><br>
			Ссылка на информацию <br>
			<input style='width:100%; height:30px;' name='mater_link_4' required value='$mater_link_4'/><br>
			Ссылка на картнку из медиафайлов:
			<input name='mater_image_4' style='width:100%; height:30px;' required value='$mater_image_4'/>
		</div>
	</div>
	";
	
	//ГОРОДА
	$a.="
	<h2>Города</h2>
	<div style='width:98%; background:white;'>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:100px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:200px; background:url($city_image_1) no-repeat; background-size:cover; background-position:center;'></div>
			Название города:<br>
			<input name='city_name_1' required style='width:100%; height:30px;' value='$city_name_1'/>
			Ссылка на город<br>
			<input name='city_link_1' required style='width:100%; height:30px;' value='$city_link_1'/><br>
			Ссылка на картнку из медиафайлов:
			<input name='city_image_1' style='width:100%; height:30px;' required value='$city_image_1'/>
		</div>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:100px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:200px; background:url($city_image_2) no-repeat; background-size:cover; background-position:center;'></div>
			Название города:<br>
			<input name='city_name_2' required style='width:100%; height:30px;' value='$city_name_2'/>
			Ссылка на город<br>
			<input name='city_link_2' required style='width:100%; height:30px;' value='$city_link_2'/><br>
			Ссылка на картнку из медиафайлов:
			<input name='city_image_2' style='width:100%; height:30px;' required  value='$city_image_2'/>
		</div>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:100px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:200px; background:url($city_image_3) no-repeat; background-size:cover; background-position:center;'></div>
			Название города:<br>
			<input name='city_name_3' required style='width:100%; height:30px;' value='$city_name_3'/>
			Ссылка на город<br>
			<input name='city_link_3' required style='width:100%; height:30px;' value='$city_link_3'/>
			Ссылка на картнку из медиафайлов:
			<input name='city_image_3' style='width:100%; height:30px;' required  value='$city_image_3'/>
		</div>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:100px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:200px; background:url($city_image_4) no-repeat; background-size:cover; background-position:center;'></div>
			Название города:<br>
			<input name='city_name_4' required style='width:100%; height:30px;' value='$city_name_4'/>
			Ссылка на город<br>
			<input name='city_link_4' required style='width:100%; height:30px;' value='$city_link_4'/>
			Ссылка на картнку из медиафайлов:
			<input name='city_image_4' style='width:100%; height:30px;' required value='$city_image_4'/>
		</div>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:100px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:200px; background:url($city_image_5) no-repeat; background-size:cover; background-position:center;'></div>
			Название города:<br>
			<input name='city_name_5' required style='width:100%; height:30px;' value='$city_name_5'/>
			Ссылка на город<br>
			<input name='city_link_5' required style='width:100%; height:30px;' value='$city_link_5'/>
			Ссылка на картнку из медиафайлов:
			<input name='city_image_5' style='width:100%; height:30px;' required value='$city_image_5'/>
		</div>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:100px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:200px; background:url($city_image_6) no-repeat; background-size:cover; background-position:center;'></div>
			Название города:<br>
			<input name='city_name_6' required style='width:100%; height:30px;' value='$city_name_6'/>
			Ссылка на город<br>
			<input name='city_link_6' required style='width:100%; height:30px;' value='$city_link_6'/>
			Ссылка на картнку из медиафайлов:
			<input name='city_image_6' style='width:100%; height:30px;' required value='$city_image_6'/>
		</div>
	</div>
	";
	
	//КОНТЕНТ 2, УТП
	$a.="
	<h2>Второй контент и УТП</h2>
	Заголовок h2:<br>
	<textarea name='header_2' required style='width:98%; min-height:50px;'>$header_2</textarea><br>
	Контент 2: <br>
	<textarea name='content_2' required style='width:98%; min-height:50px;'>$content_2</textarea>
	Контент 2-2 (после УТП): <br>
	<textarea name='content_2_2' required style='width:98%; min-height:50px;'>$content_2_2</textarea>
	Название кнопки:<br>
	<input name='button_text_content_2' style='width:98%; height:30px;' required value='$button_text_content_2'/>
	Ссылка кнопки:<br>
	<input name='button_link_content_2' style='width:98%; height:30px;' required value='$button_link_content_2'/>
	";
	$a.="
	<h2>УТП</h2>
	<div style='width:98%; background:white;'>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:150px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:250px; background:url($utp_image_1) no-repeat; background-size:cover; background-position:center;'></div>
			Заголовок УТП:<br>
			<input name='utp_header_1' required style='width:100%; height:30px;' value='$utp_header_1'/>
			Краткое описание:<br>
			<textarea name='utp_descr_1' required style='width:100%; height:50px;'>$utp_descr_1</textarea>
			Ссылка УТП<br>
			<input name='utp_link_1' required style='width:100%; height:30px;' value='$utp_link_1'/>
			Ссылка на картнку из медиафайлов:
			<input name='utp_image_1' style='width:100%; height:30px;' required value='$utp_image_1'/>
		</div>	
		<div style='width:calc(33% - 11px); min-width:200px; min-height:150px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:250px; background:url($utp_image_2) no-repeat; background-size:cover; background-position:center;'></div>
			Заголовок УТП:<br>
			<input name='utp_header_2' required style='width:100%; height:30px;' value='$utp_header_2'/>
			Краткое описание:<br>
			<textarea name='utp_descr_2' required style='width:100%; height:50px;'>$utp_descr_2</textarea>
			Ссылка УТП<br>
			<input name='utp_link_2' required style='width:100%; height:30px;' value='$utp_link_2'/>
			Ссылка на картнку из медиафайлов:
			<input name='utp_image_2' style='width:100%; height:30px;' required value='$utp_image_2'/>
		</div>
		<div style='width:calc(33% - 11px); min-width:200px; min-height:150px; display:inline-block; margin-right:10px; margin-bottom:15px; margin-top:15px;'>
			<div style='width:100%; height:250px; background:url($utp_image_3) no-repeat; background-size:cover; background-position:center;'></div>
			Заголовок УТП:<br>
			<input name='utp_header_3' required style='width:100%; height:30px;' value='$utp_header_3'/>
			Краткое описание:<br>
			<textarea name='utp_descr_3' required style='width:100%; height:50px;'>$utp_descr_3</textarea>
			Ссылка УТП<br>
			<input name='utp_link_3' required style='width:100%; height:30px;' value='$utp_link_3'/>
			Ссылка на картнку из медиафайлов:
			<input name='utp_image_3' style='width:100%; height:30px;' required value='$utp_image_3'/>
		</div>
	</div>
	";
	//О ФАБРИКЕ СНИЗУ ГЛАВНОЙ
	$a.="
	<h2>ПОСЛЕДНИЙ БЛОК</h2>
	<div style='width:98%; background:white;'>
		<div style='width:calc(50% - 12px); margin-right:10px; display:inline-block; min-height:300px; min-width:300px;'>
			<br><br>
			Заголовок:<br>
			<input name='header_3' required style='width:100%; height:30px;' value='$header_3'/>
			Контент:<br>
			<textarea name='content_3' required style='width:100%; height:50px;'>$content_3</textarea>
			Текст кнопки:<br>
			<input name='button_text_content_3' required style='width:100%; height:30px;' value='$button_text_content_3'/>
			Ссылка кнопки:<br>
			<input name='button_link_content_3' required style='width:100%; height:30px;' value='$button_link_content_3'/>		
			Ссылка на картнку из медиафайлов:
			<input name='image_content_3' style='width:100%; height:30px;' required value='$image_content_3'/>
			<br><br>
		</div>
		<div style='width:calc(50% - 12px); margin-left:10px; display:inline-block; height:300px; min-width:300px;'>
			<div style='background:url($image_content_3) no-repeat; background-size:cover; background-position:center; width:100%; height:100%;'></div>
		</div>
	</div>
	";
	
	$a.="<input type='submit' name='redact_homepage' style=' padding:10px 20px; background:green; border:none; color:white; margin-top:20px; cursor:pointer;' value='ВНЕСТИ ПРАВКИ'/>";
	
	$a.="
	</form>
	";
	
	echo $a;
	
	if(isset($_POST['redact_homepage'])){
		$modern_descr = $_POST['modern_descr'];
		$classic_descr = $_POST['classic_descr'];
		$first_header = $_POST['first_header'];
		$first_content = $_POST['first_content'];
		$mater_header_1 = $_POST['mater_header_1'];
		$mater_header_2 = $_POST['mater_header_2'];
		$mater_header_3 = $_POST['mater_header_3'];
		$mater_header_4 = $_POST['mater_header_4'];
		$mater_descr_1 = $_POST['mater_descr_1'];
		$mater_descr_2 = $_POST['mater_descr_2'];
		$mater_descr_3 = $_POST['mater_descr_3'];
		$mater_descr_4 = $_POST['mater_descr_4'];
		$mater_link_1 = $_POST['mater_link_1'];
		$mater_link_2 = $_POST['mater_link_2'];
		$mater_link_3 = $_POST['mater_link_3'];
		$mater_link_4 = $_POST['mater_link_4'];
		$city_name_1 = $_POST['city_name_1'];
		$city_name_2 = $_POST['city_name_2'];
		$city_name_3 = $_POST['city_name_3'];
		$city_name_4 = $_POST['city_name_4'];
		$city_name_5 = $_POST['city_name_5'];
		$city_name_6 = $_POST['city_name_6'];
		$city_link_1 = $_POST['city_link_1'];
		$city_link_2 = $_POST['city_link_2'];
		$city_link_3 = $_POST['city_link_3'];
		$city_link_4 = $_POST['city_link_4'];
		$city_link_5 = $_POST['city_link_5'];
		$city_link_6 = $_POST['city_link_6'];
		$header_2 = $_POST['header_2'];
		$content_2 = $_POST['content_2'];
		$content_2_2 = $_POST['content_2_2'];
		$button_text_content_2 = $_POST['button_text_content_2'];
		$button_link_content_2 = $_POST['button_link_content_2'];
		$utp_header_1 = $_POST['utp_header_1'];
		$utp_header_2 = $_POST['utp_header_2'];
		$utp_header_3 = $_POST['utp_header_3'];
		$utp_descr_1 = $_POST['utp_descr_1'];
		$utp_descr_2 = $_POST['utp_descr_2'];
		$utp_descr_3 = $_POST['utp_descr_3'];
		$utp_link_1 = $_POST['utp_link_1'];
		$utp_link_2 = $_POST['utp_link_2'];
		$utp_link_3 = $_POST['utp_link_3'];
		$header_3 = $_POST['header_3'];
		$content_3 = $_POST['content_3'];
		$button_text_content_3 = $_POST['button_text_content_3'];
		$button_link_content_3 = $_POST['button_link_content_3'];
		//IMAGES
		$modern_image = $_POST['modern_image'];
		$classic_image = $_POST['classic_image'];
		$mater_image_1 = $_POST['mater_image_1'];
		$mater_image_2 = $_POST['mater_image_2'];
		$mater_image_3 = $_POST['mater_image_3'];
		$mater_image_4 = $_POST['mater_image_4'];
		$city_image_1 = $_POST['city_image_1'];
		$city_image_2 = $_POST['city_image_2'];
		$city_image_3 = $_POST['city_image_3'];
		$city_image_4 = $_POST['city_image_4'];
		$city_image_5 = $_POST['city_image_5'];
		$city_image_6 = $_POST['city_image_6'];
		$utp_image_1 = $_POST['utp_image_1'];
		$utp_image_2 = $_POST['utp_image_2'];
		$utp_image_3 = $_POST['utp_image_3'];
		$image_content_3 = $_POST['image_content_3'];
		
		$update_sql = "UPDATE gi_homepage SET 
			modern_descr = '$modern_descr', classic_descr = '$classic_descr', first_header = '$first_header', first_content = '$first_content', mater_header_1 = '$mater_header_1', 
			mater_header_2 = '$mater_header_2', mater_header_3 = '$mater_header_3', mater_header_4 = '$mater_header_4', mater_descr_1 = '$mater_descr_1', mater_descr_2 = '$mater_descr_2', 
			mater_descr_3 = '$mater_descr_3', mater_descr_4 = '$mater_descr_4', mater_link_1 = '$mater_link_1', mater_link_2 = '$mater_link_2', mater_link_3 = '$mater_link_3', 
			mater_link_4 = '$mater_link_4', city_name_1 = '$city_name_1', city_name_2 = '$city_name_2', city_name_3 = '$city_name_3', city_name_4 = '$city_name_4', 
			city_name_5 = '$city_name_5', city_name_6 = '$city_name_6', city_link_1 = '$city_link_1', city_link_2 = '$city_link_2', city_link_3 = '$city_link_3', 
			city_link_4 = '$city_link_4', city_link_5 = '$city_link_5', city_link_6 = '$city_link_6', header_2 = '$header_2', content_2 = '$content_2', 
			content_2_2 = '$content_2_2', button_text_content_2 = '$button_text_content_2', button_link_content_2 = '$button_link_content_2', utp_header_1 = '$utp_header_1', 
			utp_header_2 = '$utp_header_2', utp_header_3 = '$utp_header_3', utp_descr_1 = '$utp_descr_1', utp_descr_2 = '$utp_descr_2', utp_descr_3 = '$utp_descr_3', 
			utp_link_1 = '$utp_link_1', utp_link_2 = '$utp_link_2', utp_link_3 = '$utp_link_3', header_3 = '$header_3', content_3 = '$content_3', 
			button_text_content_3 = '$button_text_content_3', button_link_content_3 = '$button_link_content_3', 
			modern_image = '$modern_image', classic_image = '$classic_image', mater_image_1 = '$mater_image_1', mater_image_2 = '$mater_image_2', mater_image_3 = '$mater_image_3',
			 mater_image_4 = '$mater_image_4', city_image_1 = '$city_image_1', city_image_2 = '$city_image_2', city_image_3 = '$city_image_3', city_image_4 = '$city_image_4',
			 city_image_5 = '$city_image_5', city_image_6 = '$city_image_6', utp_image_1 = '$utp_image_1', utp_image_2 = '$utp_image_2', utp_image_3 = '$utp_image_3',
			 image_content_3 = '$image_content_3'";
		$update_result = $wpdb->get_results($update_sql);
		echo "<script>window.location.reload();</script>";
	}
}

function add_slide(){
	global $wpdb;
	
	echo "<h1>Добавить новый слайд</h1>";

	$a="<form method='POST'>";
	$a.="
	Ссылка на картинку:<br>
	<input name='image' style='width:400px; height:30px;' required/> <br>
	Заголовок:<br>
	<input name='header' style='width:400px; height:30px;' required/> <br>
	Описание:<br>
	<textarea name='descr' style='width:400px; height:50px;' required></textarea> <br>
	Текст кнопки:<br>
	<input name='btn_text' style='width:400px; height:30px;' required/> <br>
	Ссылка кнопки: <br>
	<input name='link' style='width:400px; height:30px;' required/> <br><br>
	Позиция: <input name='rank' style='width:133px; height:30px;' required/>
	Цвет фона: <input name='color' style=' width:133px; height:30px;'/> <br>
	<input type='submit' name='add_slide' style='padding:10px 20px; background:green; border:none; cursor:pointer; color:white; margin:20px 0;'/>
	
	";
	$a.="</form>";
	
	if(isset($_POST['add_slide'])){
		$image = $_POST['image'];
		$header = $_POST['header'];
		$descr = $_POST['descr'];
		$btn_text = $_POST['btn_text'];
		$link = $_POST['link'];
		$rank = $_POST['rank'];
		if(empty($_POST['color'])){
			$color = "#6b6b6b";
		}
		else {
			$color = $_POST['color'];
		}
		$sql = "INSERT INTO `gi_homeslider` (`image`,`header`,`descr`,`btn_text`,`link`,`color`,`rank`)
				VALUES ('$image','$header','$descr','$btn_text','$link','$color','$rank')";
		$result = $wpdb->get_results($sql);
		
		echo "Слайд добавлен!";
		echo "<script>window.location.reload();</script>";
		//var_dump($_POST);
	}
	
	echo $a;
}

function redact_slide(){
	global $wpdb;
	echo "<h1>Редактировать слайды</h1>";
	$a ="<div style='width:calc(98% - 30px); padding:15px; background:white; text-align:center;'>";
	$sql = "SELECT * FROM gi_homeslider ORDER BY `rank`";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$newid = $row->newid;
		$image = $row->image;
		$header = $row->header;
		$descr = $row->descr;
		$btn_text = $row->btn_text;
		$link = $row->link;
		$rank = $row->rank;
		$color = $row->color;
		$a.="<div style='width:310px; display:inline-block; background:#f9f9f9; border:1px solid #dcdcdc; padding:15px; margin:10px;'>
			<form method='POST' style='text-align:left;'>
				<div style='width:100%; height:200px; background:url($image) no-repeat; background-size:cover; background-position:center;'></div>
				Ссылка на картинку:<br>
				<input name='image' style='width:100%; height:30px;' required value='$image'/> <br>
				Заголовок:<br>
				<input name='header' style='width:100%; height:30px;'  value='$header'/> <br>
				Описание:<br>
				<textarea name='descr' style='width:100%; height:50px;' >$descr</textarea> <br>
				Текст кнопки:<br>
				<input name='btn_text' style='width:100%; height:30px;'  value='$btn_text'/> <br>
				Ссылка кнопки: <br>
				<input name='link' style='width:100%; height:30px;' required value='$link'/> <br><br>
				Позиция: <input name='rank' style='width:40px; height:30px;' required value='$rank'/>
				Цвет фона: <input name='color' style=' width:80px; height:30px;' value='$color'/> <br>
				<button type='submit' name='redact_slide' value='$newid' style='padding:10px 20px; background:green; border:none; cursor:pointer; color:white; margin:20px 0;'/>Редактировать</button>
				<button type='submit' name='delete_slide' value='$newid' style='padding:10px 20px; background:darkred; border:none; cursor:pointer; color:white; margin:20px 0;'/>Удалить слайд</button>
			</form>
		</div>";		
	}
	$a.="</div>";

	if(isset($_POST['redact_slide'])){
		$slide_id = $_POST['redact_slide'];
		$image = $_POST['image'];
		$header = $_POST['header'];
		$descr = $_POST['descr'];
		$btn_text = $_POST['btn_text'];
		$link = $_POST['link'];
		$rank = $_POST['rank'];
		if(empty($_POST['color'])){
			$color = "#6b6b6b";
		}
		else {
			$color = $_POST['color'];
		}
		$sql = "UPDATE gi_homeslider SET image='$image', header='$header', descr='$descr', btn_text='$btn_text', link='$link', `rank`='$rank', color='$color' WHERE newid='$slide_id'";
		$result = $wpdb->get_results($sql);
		echo "Правки внесены!";
		echo "<script>window.location.reload();</script>";
	}
	if(isset($_POST['delete_slide'])){
		$slide_id = $_POST['delete_slide'];
		$sql = "DELETE FROM gi_homeslider WHERE newid = '$slide_id'";
		$result=$wpdb->get_results($sql);
		echo "Слайд удален!";
		echo "<script>window.location.reload();</script>";		
	}
	
	echo $a;
}

function contacts_redactor(){
	global $wpdb;
	$a = "<h1>Все контакты</h1>";
	$a.="<div style='width:calc(98% - 40px); padding:20px; background:white;'>";
	$sql = "SELECT * FROM gi_contacts ORDER BY department";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$newid = $row->newid;
		$department = $row->department;
		$subsection = $row->subsection;
		$contact_post = $row->contact_post;
		$employee = $row->employee;
		$phones = $row->phones;
		$mailbox = $row->mailbox;
		$rank = $row->rank;
		$a.="
		<div style='width:320px; padding:10px; margin:10px; display:inline-block; background:#f9f9f9; border:1px solid #dcdcdc;'>
			<form method='POST'>
				<i>($department)</i><br>
				<input name='red_subsection' value='$subsection' style='width:100%; height:30px;' required placeholder='Подразделение, Юр.лицо'/>
				<input name='red_contact_post' value='$contact_post' style='width:100%; height:30px;' placeholder='Должность'/>
				<input name='red_employee' value='$employee' style='width:100%; height:30px;' placeholder='ФИО'/>
				<textarea name='red_phones' style='width:100%; height:30px;' placeholder='Телефоны'>$phones</textarea>
				<input name='red_mailbox' value='$mailbox' style='width:100%; height:30px;' placeholder='Почтовый ящик'/>
				Очередь: <input name='red_rank' value='$rank' type='number' style='width:60px; height:30px;'/><br><br>
				<button type='submit' name='redact' value='$newid' style='background:green; border:none; padding:5px 10px; color:white; cursor:pointer;'>Редактировать</button>
				<button type='submit' name='delete' value='$newid' style='background:darkred; border:none; padding:5px 10px; color:white; cursor:pointer;'>Удалить</button>
			
			</form>
		</div>
		";
	}

	
	$a.="</div>";
	
	$a.="<h1>Добавить контакт</h1>";
	$a.="<div style='width:calc(98% - 40px); padding:20px; background:white;'>";
	$a.="
	<form method='POST'>
		<select name='department' required style='width:400px; height:30px; border-color:#dcdcdc;'>
			<option selected disabled value=''>Выберите главный отдел (гео)</option>
	";
	$sql_dep = "SELECT DISTINCT department FROM gi_departments";
	$result_dep = $wpdb->get_results($sql_dep);
	foreach($result_dep as $row_dep){
		$department = $row_dep->department;
		$a.="<option value='$department'>$department</option>";
	}
	$a.="
		</select><br>
		Введите подраздел* <br>
		<input name='subsection' required style='width:400px; height:30px;'/><br>
		Укажите должность<br>
		<input name='contact_post' style='width:400px; height:30px;' /><br>
		Укажите имя контакта<br>
		<input name='employee' style='width:400px; height:30px;'/><br>
		Укажите телефоны<br>
		<textarea name='phones' style='width:400px; height:50px;'></textarea><br>
		Почтовый ящик:<br>
		<input name='mailbox' style='width:400px; height:30px;'/><br><br>
		Очередность: <input name='rank' style='width:70px;' type='number'/><br><br>
		<button type='submit' name='add_contact' value='add_contact' style='padding:8px 15px; background:green; color:white; border:none; cursor:pointer;'>Добавить контакт</button>
	</form>
	";
	$a.="</div>";
	
	if(isset($_POST['redact'])){
		$redact_id = $_POST['redact'];
		$red_subsection = $_POST['red_subsection'];
		$red_contact_post = $_POST['red_contact_post'];
		$red_employee = $_POST['red_employee'];
		$red_phones = $_POST['red_phones'];
		$red_mailbox = $_POST['red_mailbox'];
		$red_rank = $_POST['red_rank'];
		$sql_red = "UPDATE `gi_contacts` SET subsection = '$red_subsection', contact_post = '$red_contact_post', employee = '$red_employee', phones = '$red_phones', mailbox = '$red_mailbox', rank = '$red_rank'  WHERE `newid` = '$redact_id';";
		$result_red = $wpdb->get_results($sql_red);
		echo "Контакт отредактирован";
		echo "<script>window.location.reload();</script>";
	}
	if(isset($_POST['delete'])){
		$delete_id = $_POST['delete'];

		$sql_del = "DELETE FROM gi_contacts WHERE `newid` = '$delete_id';";
		$result_del = $wpdb->get_results($sql_del);
		echo "Контакт удален";
		echo "<script>window.location.reload();</script>";
	}
	
	if(isset($_POST['add_contact'])){
		$department = $_POST['department'];
		$subsection = $_POST['subsection'];
		$contact_post = $_POST['contact_post'];
		$employee = $_POST['employee'];
		$phones = $_POST['phones'];
		$mailbox = $_POST['mailbox'];
		$rank = $_POST['rank'];
		$sql = "INSERT INTO `gi_contacts` (`department`,`subsection`,`contact_post`,`employee`,`phones`,`mailbox`,`rank`) VALUES ('$department','$subsection','$contact_post','$employee','$phones', '$mailbox', '$rank')";
		$result = $wpdb->get_results($sql);
		echo "Контакт добавлен";
		echo "<script>window.location.reload();</script>";
	}
	echo $a;
}

function otdeli(){
	global $wpdb;
	echo "<h1>Все отделы</h1>";
	$a ="<div style='width:98%; background:white; padding:10px 0;'>";
	$sql = "SELECT * FROM gi_departments";
	$result = $wpdb-> get_results($sql);
	foreach($result as $row){
		$newid = $row->newid;
		$department = $row->department;
		$address = $row->address;
		$coordinates = $row->coordinates;
		$image = $row->image;
		$type = $row->type;
		if($type=='factory'){
			$factory_checked = "checked";
			$distrib_checked = "";
		}
		else {
			$factory_checked = "";
			$distrib_checked = "checked";
		}
		$a.="<div style='width:calc(100% - 40px); margin:10px; padding:10px; min-height:30px; background:#f9f9f9; border:1px solid #dcdcdc; min-width:1000px; overflow-y:auto;'>
		<form method='POST'>	
			<input name='department' style='width:17%;' placeholder='Название отдела' value='$department' required/>
			<input name='address' style='width:17%;' placeholder='Адрес' value='$address' required/>
			<input name='coordinates' style='width:17%;' placeholder='Координаты' value='$coordinates' required/>
			<input name='image'  style='width:16%;' placeholder='Изображение' value='$image'/>
			<select name='type' style='width:16%;'>
				<option value='factory' $factory_checked>Фабрика</option>
				<option value='distibutor' $distrib_checked>Дистрибьютор</option>
			</select>
			<button type='submit' name='redact' value='$newid' style='padding:5px 10px; background:green; color:white; cursor:pointer; border:none; width:calc(16% - 58px);'>Редактировать</button>
			<button type='submit' name='delete' style='width:30px; height:30px; background:darkred; border:none; color:white; border-radius:50%;'>X</button>
		</form>
		</div>";
	}
	$a.="</div>";
	
	$a.="<h1>Добавить новый отдел</h1>";
	$a.="<div style='width:calc(98% - 40px); padding:20px; background:white;'>
		<form method='POST'>
			Название отедла <br>
			<input name='add_department' style='width:300px; height:30px;' required/><br>
			Адрес <br>
			<textarea name='add_address' style='width:300px; height:60px;' required></textarea><br>
			Координаты, через зпт. с пробелом, шир., долг. <br>
			<input name='add_coordinates' style='width:300px; height:30px;' required/><br>
			Ссылка на изображение <br>
			<input name='add_image' style='width:300px; height:30px;'/><br><br>
			<select name='add_type' style='width:300px;' required>
				<option selected disabled value=''>Тип отдела</option>
				<option value='factory'>Фабрика</option>
				<option value='distibutor'>Дистрибьютор</option>
			</select><br><br>
			<button type='submit' name='add_department_btn' value='add' style='padding:5px 10px; background:green; color:white; cursor:pointer; border:none; width:calc(16% - 58px);'>Добавить</button>			
		</form>
	</div>";
	if(isset($_POST['redact'])){
		$redact_id = $_POST['redact'];
		$department = $_POST['department'];
		$address = $_POST['address'];
		$coordinates = $_POST['coordinates'];
		$image = $_POST['image'];
		$type = $_POST['type'];
		$sql = "UPDATE gi_departments SET department = '$department', address = '$address', coordinates = '$coordinates', image = '$image', type = '$type' WHERE newid = $redact_id";
		$result = $wpdb->get_results($sql);
		echo "Правки внесены";
		echo "<script>window.location.reload();</script>";
	}
	if(isset($_POST['delete'])){
		$delete_id = $_POST['delete'];
		$sql = "DELETE FROM gi_departments WHERE newid = '$delete_id'";
		$result = $wpdb->get_results($sql);
		echo "Отдел удален";
		echo "<script>window.location.reload();</script>";
	}
	if(isset($_POST['add_department_btn'])){
		$department = $_POST['add_department'];
		$address = $_POST['add_address'];
		$coordinates = $_POST['add_coordinates'];
		$image = $_POST['add_image'];
		$type = $_POST['add_type'];
		
		$sql = "INSERT INTO `gi_departments` (`department`,`address`,`coordinates`,`image`,`type`) VALUES ('$department','$address','$coordinates','$image','$type')";
		$result = $wpdb->get_results($sql);
		echo "Отдел добавлен";
		echo "<script>window.location.reload();</script>";
	}
	echo $a;
}
?>