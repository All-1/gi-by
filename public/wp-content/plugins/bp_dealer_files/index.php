<?php
/*Plugin Name: bp_dealer_files
Description: Файлы дилерам
Version: 1.0
Author: Business Park*/

add_action('admin_menu', 'add_files_page');
function add_files_page()
{
	add_menu_page('Сотрудничество', 'Сотрудничество', 'read', __FILE__, 'my_files', 'dashicons-media-default', 12);
	add_submenu_page(__FILE__, 'Файлы дилеров', 'Файлы дилеров', 8, 'add_file', 'add_file');
	add_submenu_page(__FILE__, 'Файлы дизайнеров', 'Файлы дизайнеров', 8, 'add_designer_file', 'add_designer_file');
	add_submenu_page(__FILE__, 'Текстуры', 'Текстуры', 8, 'designers_textures', 'designers_textures');
	add_submenu_page(__FILE__, 'FAQ дизайнеров', 'FAQ дизайнеров', 8, 'faq_designers', 'faq_designers');
}
add_shortcode('user_files', 'user_files');
add_shortcode('print_designers_files', 'print_designers_files');


function faq_designers()
{
	global $wpdb;

	$a = "<h1>Добавить вопрос-ответ</h1>";
	$a .= "
	<form method='POST'>
		<div style='display:inline-grid; grid-template-columns:2fr 2fr 1fr 1fr; grid-gap:16px; width:98%; background:#fff; padding:16px;'>
			<div>
				<textarea name='question' placeholder='Введите вопрос' style='width:100%;'></textarea>
			</div>
			<div>
				<textarea name='answer' placeholder='Введите ответ' style='width:100%;'></textarea>
			</div>
			<div>
				<input name='rating' style='width:100%; height:100%; border:1px solid #dcdcdc;' placeholder='Порядковый номер'/>
			</div>
			<div>
				<button type='submit' style='width:100%; height:100%; cursor:pointer;'>Добавить</button>
			</div>
		</div>
	</form>
	";

	if (isset($_POST['question']) and isset($_POST['answer'])) {
		$q = $_POST['question'];
		$ans = $_POST['answer'];
		$r = $_POST['rating'];

		$sql = "INSERT INTO gi_designer_faq SET question = '$q', answer='$ans', `rank` = '$r'";
		$result = $wpdb->get_results($sql);

		echo "Вопрос добавлен";
		echo "<script>window.location.reload();</script>";
	}

	//Редактирование
	$b = "<h1>Редактирвоание вопросов</h1>";
	$b .= "<div style='width:98%; background:#fff; padding:16px; display:inline-grid; grid-template-columns: 3fr 3fr 1fr 1fr 1fr; grid-gap:16px;'>";
	$sql2 = "SELECT * FROM gi_designer_faq";
	$result2 = $wpdb->get_results($sql2);
	foreach ($result2 as $row2) {
		$question = $row2->question;
		$answer = $row2->answer;
		$rank = $row2->rank;
		$newid = $row2->newid;
		$b .= "
		<div>
			<textarea id='q_$newid' name='question' style='width:100%;'>$question</textarea>
		</div>
		<div>
			<textarea id='a_$newid' name='answer' style='width:100%;'>$answer</textarea>
		</div>
		<div>
			<input id='r_$newid' name='rank' value='$rank' style='width:100%; border:1px solid #dcdcdc;'/>
		</div>
		<div>
			<button id='redactbtn_$newid' value='$newid' style='width:100%; cursor:pointer;' onclick='redact_des_faq(this);'>Изменить</button>
		</div>
		<div>
			<button id='deletebtn_$newid' value='$newid' style='width:100%; cursor:pointer;' onclick='delete_des_faq(this);'>Удалить</button>
		</div>
		";
	}

	$b .= "</div>";
	$b .= "
	<script>
	function redact_des_faq(obj){
		
		var id = obj.value;
		var q = jQuery('#q_'+id).val();
		var a = jQuery('#a_'+id).val();
		var r = jQuery('#r_'+id).val();
		//console.log(q);
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/redact_designer_faq.php',
			type: 'POST',
			data: {id:id, q:q, a:a, r:r},
			success: function(data){ 
				alert('Правки внесены');
			} 
		});
		
	}
	
	function delete_des_faq(obj){
		var id = obj.value;
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/delete_designer_faq.php',
			type: 'POST',
			data: {id:id},
			success: function(data){ 
				alert('Вопрос удален');
				jQuery('#redactbtn_'+id).hide();
				jQuery('#deletebtn_'+id).hide();
			} 
		});
	}
	</script>
	";

	echo $a . $b;
}

function print_designers_files()
{

	global $wpdb;
	global $lang_adm;

	$a = "<h1>$lang_adm->df_h1</h1>";
	$kitchen_str = "";
	$sql = "SELECT DISTINCT kitchen FROM gi_designer_architect";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$kitchens = $row->kitchen;
		$kitchen_str .= $kitchens . ", ";
	}

	$kitchens_array = explode(', ', $kitchen_str);
	$kitchens_array = array_filter(array_unique($kitchens_array));
	$a .= "
	<div id='modal_window' style='width:100%; height:100%; position:fixed; left:0; top:0; z-index:999999; background:#fff; display:none; overflow-y:auto;'>
		<div id='close_modal_content' style='position:fixed; top:16px; right:16px; padding:16px; cursor:pointer;'>
			<img src='/wp-content/plugins/bp_dealer_files/images/close2.png' style='width:30px;'/>
		</div>
		<img id='loadingicon' src='/wp-content/themes/wp-diary/images/loading.gif' style='width:150px; height:150px; position:absolute; top:calc(50% - 75px); left:calc(50% - 75px);'>
		<div id='modal_content'></div>
	</div>";
	$a .= "<div class='des_kitchens_files' style=''>";
	foreach ($kitchens_array as $kitchen_name) {

		$sql2 = "SELECT * FROM gi_kitchen WHERE name = '$kitchen_name'";
		$result2 = $wpdb->get_results($sql2);
		foreach ($result2 as $row2) {
			$name = $row2->name;
			$type = $row2->type;
			$avatar = $row2->avatar;
			$material = $row2->material;

			$mat_arr = array('Массив + Шпон', 'Шпон', 'Акрил', 'Пластик', 'МДФ краска', 'ЛДСП', 'Массив дерева');
			$lang_arr = array($lang_adm->df_massiv, $lang_adm->df_shpon, $lang_adm->df_acril, $lang_adm->df_plastic, $lang_adm->df_mdf_kraska, $lang_adm->df_ldsp);
			$material_text = str_replace($mat_arr, $lang_arr, $material);
			$material_text_temp = '';
			$a .= "
			<div style='cursor:pointer;' data-type='kitchen' data-name='$name' onclick='loadinfo(this);'>
				<div class='kitchen_ava' style='width:100%; height:200px; background:url($avatar) no-repeat; background-size:cover; background-position:center;'></div>
				<div style='display:inline-block; width:calc(100% - 48px); margin-right:8px; margin-left:8px;'>
					<div style='font-size:24px; font-weight:600; margin-top:4px;'>$name</div>
					<div style='font-size:12px; display:block; margin-top:-4px; margin-bottom:8px; line-height:1;'>$material_text_temp</div>
				</div>
				<div style='display:inline-block; width:20px; vertical-align:top; margin-right:8px;'>
					<img src='/wp-content/plugins/bp_dealer_files/images/goto.png' style='width:100%; margin-top:17px;'/>
				</div>
			</div>
			";
		}
	}
	$a .= "</div>";

	$a .= "
	
	<style>
	#close_modal_content {z-index:999;}
	.des_kitchens_files{width:100%; display:inline-grid; grid-template-columns:repeat(3,1fr); grid-gap:32px;}
	#modal_content{margin:32px auto; padding:32px 32px 128px 32px; width:calc(100% - 40px - 64px); max-width:1100px;}
	.selected {background:transparent !important;}
	.texture_btns{margin:32px 0 16px 0;}
	.textures_btn{cursor:pointer; font-size:22px; display:inline-block; font-weight:200; margin:0 24px 16px 0; color:#A6A6A6;}
	.textures_btn.selected{border-bottom:4px solid #DEA993; color:#111; font-weight:600;}
	.textures_grid{width:100%; display:inline-grid; grid-template-columns:repeat(4,1fr); grid-column-gap:16px; grid-row-gap:32px; position:relative;}
	.des_texture_img{width:100%; height:300px; overflow:hidden;}
	
	.dis_obsch_files{display:inline-grid; grid-template-columns:1fr 1fr; grid-gap:32px; width:100%;}
	
	@media screen and (max-width:1000px){
		.des_kitchens_files{grid-gap:16px;}
		.textures_grid{grid-template-columns:repeat(3,1fr);}
	}
	@media screen and (max-width:800px){
		.des_kitchens_files{grid-template-columns:repeat(2,1fr);}
		.des_texture_img{height:250px;}
	}
	@media screen and (max-width:600px){
		.des_kitchens_files{grid-template-columns:repeat(1,1fr);}
		.textures_grid{grid-template-columns:repeat(2,1fr);}
		.dis_obsch_files{grid-template-columns:repeat(1,1fr);}
		.textures_btn{font-size:18px;}
		.textures_btn{margin:0 16px 8px 0;}
	}
	@media screen and (max-width:500px){
		#modal_content{width: calc(100% - 32px); margin-left: 32px;}
		.des_texture_img{height:200px;}
	}
	</style>
	
	
	<script>
	var avawidth = jQuery('.kitchen_ava').width();
	var avaheight = avawidth / 1.3;
	jQuery('.kitchen_ava').height(avaheight);
	function closemodalwindow(){
		jQuery('#modal_window').css('display','none');
		jQuery('html').css('overflow','auto');
	}
	jQuery('#close_modal_content').click(function(){
		closemodalwindow();
	});
	function loadinfo(obj){
		var type = jQuery(obj).data('type');
		var name = jQuery(obj).data('name');
		jQuery('#modal_window').css('display','block');
		jQuery('html').css('overflow','hidden');
		jQuery('#modal_content').html('');
		jQuery('#loadingicon').css('display','block');
		
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/write_designers_files_content.php',
			type: 'POST',
			data: {type:type, name:name},
			success: function(data){ 
				jQuery('#modal_content').html(data);
				jQuery('#loadingicon').css('display','none');
			} 
		});
	}
	</script>
	";
	return $a;
}


function designers_textures()
{

	global $wpdb;

	$x = "<div style='display:inline-grid; grid-template-columns:repeat(3,1fr); grid-gap:8px;'>";
	$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen ORDER BY name";
	$result_kitchens = $wpdb->get_results($sql_kitchens);
	foreach ($result_kitchens as $row_kitchens) {
		$kitchen = $row_kitchens->name;
		$x .= "<div><input type='checkbox' name='kitchens[]' value='$kitchen'/> $kitchen</div>";
	}
	$x .= "</div>";


	$a = "<h1>Добавить текстуры</h1>";
	$a .= "<form method='POST' enctype='multipart/form-data'>";
	$a .= "<div style='width:calc(100% - 64px); padding:32px; display:inline-grid; grid-template-columns:repeat(3,2fr) 1fr; grid-gap:32px; background:#fff;'>";
	$a .= "
		<div>
			<select name='texture_type' style='width:100%; height:45px;'>
				<option selected disabled value=''>Выберите тип текстур</option>
				<option value='Массив, Шпон'>Массив, Шпон</option>
				<option value='Мдф'>Мдф</option>
				<option value='Skin'>Skin</option>
				<option value='Syncron'>Syncron</option>
				<option value='Egger'>Egger</option>
				<option value='FENIX'>FENIX</option>
				<option value='Senosan матовый'>Senosan матовый</option>
				<option value='Senosan глянец'>Senosan глянец</option>
				<option value='Постформинг'>Постформинг</option>
			</select>
		</div>
		<div>
			$x
		</div>
		<div>
			<input type='file' name='gallery[]' multiple style='width:100%;'>
		</div>
		<div>
			<button type='submit' name='add_textures' value='load' style='width:100%; height:45px; background:green; border:none; color:#fff; cursor:pointer;'>Загрузить</button>
		</div>
	";
	$a .= "</div>";
	$a .= "</form>";

	if (isset($_POST['add_textures'])) {
		$type = $_POST['texture_type'];
		$kitchens = implode(', ', $_POST['kitchens']);

		//Загружаем фотографии текстур
		if (!empty($_FILES['gallery']) and $_FILES['gallery']['name'] == true) {
			foreach ($_FILES['gallery']['name'] as $numberAddFile => $nameAddFile) {
				$$numberAddFile = $nameAddFile; //надо 2 знака $
			}
			for ($i = 0; $i <= $numberAddFile; $i++) {
				$imagename = $_FILES['gallery']['name'][$i];
				$imagelink = "/wp-content/uploads/designers_files/textures/" . $_FILES['gallery']['name'][$i];
				$thumblink = "/wp-content/uploads/designers_files/thimbnails/" . $_FILES['gallery']['name'][$i];
				$tmp = $_FILES['gallery']['tmp_name'][$i];
				$path_parts = pathinfo($_FILES['gallery']['name'][$i]);
				$extension = $path_parts['extension'];
				//так надо :)
				$hrefimage = ".." . $imagelink;
				$hrefthumb = ".." . $thumblink;
				if ($extension == "png" or $extension == "jpg" or $extension == "jpeg" or $extension == "JPG") {
					move_uploaded_file($tmp, $hrefimage);
				} else {
					echo "Вы попытались загрузить неподходящее изображение<br>";
					continue;
				}
				//задаем размеры миниатюрам
				list($width, $height) = getimagesize($hrefimage);
				$otnosh = $width / $height;
				$newWidth = 700;
				$newHeight = $newWidth / $otnosh;
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				switch ($extension) {
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($hrefimage); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $hrefthumb, 100);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($hrefimage); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $hrefthumb, 100);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($hrefimage);
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagejpeg($image_p, $hrefthumb, 100);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($hrefimage);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $hrefthumb);
				}

				//Каждую картинку грузим в БД
				$sql = "INSERT INTO gi_designer_textures SET name='', type='$type', thisfile='$imagename', kitchens='$kitchens'";
				$result = $wpdb->get_results($sql);

			}
			//echo "Фотографии загружены";
		}

	}

	//Вносим названия всякие
	$sql_names = "SELECT * FROM gi_designer_textures WHERE name = ''";
	$result_names = $wpdb->get_results($sql_names);

	if ($result_names) {
		$b = "<h1>Впишите названия</h1>";
		$b .= "<div style='width:calc(100% - 64px); padding:32px; background:#fff; display:inline-grid; grid-template-columns:repeat(4, 1fr); grid-gap:32px;'>";
		foreach ($result_names as $row_names) {
			$file = $row_names->thisfile;
			$newid = $row_names->newid;
			$imgsrc = "/wp-content/uploads/designers_files/thimbnails/" . $file;
			$b .= "
			<div class='redacted_div_$newid'>
				<img src='$imgsrc' style='width:100%;'>
				<input type='text' id='$newid' name='texture_name' style='width:calc(100% - 59px); height:45px; margin-right:8px; display:inline-block;' placeholder='Название'/>
				<button name='add' value='$newid' style='width:45px; height:45px; display:inline-block; cursor:pointer; font-size:22px; line-height:35px;' onclick='redact_name(this);'>+</button>
			</div>";
		}
		$b .= "</div>";
	}

	$b .= "
	<script>
	function redact_name(obj){
		var thisid = jQuery(obj).val();
		var thisname = jQuery('#'+thisid).val();
		if(thisname !== ''){
			jQuery.ajax({
				url: '/wp-content/plugins/bp_dealer_files/redact_texture_name.php',
				type: 'POST',
				data: {thisid:thisid, thisname:thisname},
				success: function(data){ 
					jQuery('.redacted_div_'+thisid).css('display','none');
				} 
			});
		}
		else {
			alert('Пустое значение!');
		}
	}
	</script>
	";

	$c = "<h1 style='margin-top:128px;'>Редактируем файлы</h1>";
	$c .= "Ниже выводится 15 последних файлов. Используйте поиск, чтобы видеть ВСЕ соответствующее запросу фасады, не 15 :) (например, название цвета или материал)<br><br>";
	$c .= "<input type='text' style='width:100%; max-width:400px; height:45px;' id='search_redact' placeholder='ПОИСК...' onkeyup='search_filtres();'/> 
		<select name='texture_type' style='height:45px;' id='mat_filter' onchange='search_filtres();'>
			<option selected disabled value=''>Дополнительный фильтр по материалам</option>
			<option value=''>Ничего не выбирать</option>
			<option value='Массив, Шпон'>Массив, Шпон</option>
			<option value='Мдф'>Мдф</option>
			<option value='Skin'>Skin</option>
			<option value='Syncron'>Syncron</option>
			<option value='Egger'>Egger</option>
			<option value='FENIX'>FENIX</option>
			<option value='Senosan матовый'>Senosan матовый</option>
			<option value='Senosan глянец'>Senosan глянец</option>
			<option value='Постформинг'>Постформинг</option>
		</select>";

	$search_kitchens =
		"<select name='search_kitchens' id='search_kitchens' style='height:45px;' id='mat_filter' onchange='search_filtres();'>
			<option selected disabled value=''>Дополнительный фильтр по моделям</option>
			<option value=''>Ничего не выбирать</option>";
	$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen ORDER BY name";
	$result_kitchens = $wpdb->get_results($sql_kitchens);
	foreach ($result_kitchens as $row_kitchens) {
		$kitchen = $row_kitchens->name;
		$search_kitchens .= "<option value='$kitchen'>$kitchen</option>";
	}
	$search_kitchens .= "</select>
		<br><br>";
	$c .= $search_kitchens;


	$c .= "<div id='redact_content' style='width:98%; display:inline-grid; grid-template-columns:repeat(1,1fr); grid-row-gap:32px;'>";
	$sql2 = "SELECT * FROM gi_designer_textures WHERE name !='' ORDER BY newid DESC LIMIT 15";
	$result2 = $wpdb->get_results($sql2);

	foreach ($result2 as $row2) {
		$newid = $row2->newid;
		$type = $row2->type;
		$kitchens = $row2->kitchens;
		$thisfile = $row2->thisfile;
		$thisname = $row2->name;

		$kitchen_cheboxes = "<div id='div_content_$newid' style='width:100%; display:inline-grid; grid-template-columns:repeat(4,1fr); grid-gap:8px;'>";
		$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen";
		$result_kitchens = $wpdb->get_results($sql_kitchens);
		foreach ($result_kitchens as $row_kitchens) {
			$kitchen = $row_kitchens->name;
			if (stristr($kitchens, $kitchen)) {
				$checked = "checked";
			} else
				$checked = "";
			$kitchen_cheboxes .= "
			<div class='kitchen_checkboxes_$newid'><input type='checkbox' name='kitchens[]' value='$kitchen' $checked> $kitchen</div>
			";
		}
		$kitchen_cheboxes .= "</div>";

		$imgurl = "/wp-content/uploads/designers_files/textures/$thisfile";
		$thumburl = "/wp-content/uploads/designers_files/thimbnails/$thisfile";
		$c .= "
		<div id='redact_$newid' style='width:calc(100% - 32px); background:#fff; box-shadow:3px 6px 18px rgba(1,1,1,0.1); display:inline-grid; grid-template-columns:1fr 2fr 1fr 5fr 1fr; grid-gap:16px; padding:16px;'>
			<div>
				<a href='$imgurl' target='_blank'>
					<img src='$thumburl' style='width:100%; max-height:150px;'/>
					<input id='file_$newid' value='$thisfile' style='display:none;'>
				</a>
			</div>
			<div><input id='thisname_$newid' name='thisname' value='$thisname' style='width:100%; height:45px; border:1px solid #dcdcdc;'></div>
			<div>
				<select id='thistype_$newid' name='thistype' style='width:100%; height:45px;'>
					<option value='Массив, Шпон'>Массив, Шпон</option>
					<option value='Мдф'>Мдф</option>
					<option value='Skin'>Skin</option>
					<option value='Syncron'>Syncron</option>
					<option value='Egger'>Egger</option>
					<option value='FENIX'>FENIX</option>
					<option value='Senosan матовый'>Senosan матовый</option>
					<option value='Senosan глянец'>Senosan глянец</option>
					<option value='Постформинг'>Постформинг</option>
				</select>
			</div>
			
			$kitchen_cheboxes
			
			
			<div>
				<button id='$newid' value='redact' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:green; border:none; padding:8px 24px;' onclick=\"redact_texture($newid);\">Изменить</button>
				<button id='$newid' value='delete' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:darkred; border:none; padding:8px 24px' onclick=\"delete_texture($newid);\">Удалить</button>
			</div>				
		</div>";

		$c .= "<script>
		jQuery('#thistype_$newid option[value=$type]').attr('selected','selected');
		</script>";

	}
	$c .= "</div>";


	$c .= "
	<script>
	function redact_texture(thisid){
		var thisname = jQuery('#thisname_'+thisid).val();
		var thistype = jQuery('#thistype_'+thisid).val();
		var checkedkitchens = [];
		jQuery('.kitchen_checkboxes_'+thisid+ ' input:checkbox:checked').each(function() {
			checkedkitchens.push(this.value);
		});
		var kitchensstring = checkedkitchens.toString();
		kitchensstring = kitchensstring.replace(/,/g, \", \");
		
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/redact_textures_data.php',
			type: 'POST',
			data: {thisid:thisid, thisname:thisname, thistype:thistype, kitchensstring:kitchensstring},
			success: function(data){ 
				alert('Правки внесены');
			} 
		});
		
	}
	
	function delete_texture(thisid){
		var thisfile = jQuery('#file_'+thisid).val();
		
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/delete_textures_data.php',
			type: 'POST',
			data: {thisid:thisid, thisfile:thisfile},
			success: function(data){ 
				alert('Удалено');
				jQuery('#redact_'+thisid).css('display','none');
			} 
		});
	}
	</script>
	";


	$d .= "
	<script>
	/*
	jQuery('#search_redact').keyup(function(){
		thisval = this.value;
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/search_redact_files.php',
			type: 'POST',
			data: {thisval:thisval},
			success: function(data){ 
				jQuery('#redact_content').html(data);
			} 
		});
	});
	*/
	function search_filtres(){
		thisval = jQuery('#search_redact').val();
		matval = jQuery('#mat_filter').val();
		modelval = jQuery('#search_kitchens').val();
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/search_redact_files.php',
			type: 'POST',
			data: {thisval:thisval, matval:matval, modelval:modelval},
			success: function(data){ 
				jQuery('#redact_content').html(data);
			}
		});
	}
	</script>
	";
	echo $a . $b . $c . $d;
}

function add_designer_file()
{

	$a = "<h1>Добавить и редактировать файлы дизайнеров</h1>";
	global $wpdb;

	$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen ORDER BY name";
	$result_kitchens = $wpdb->get_results($sql_kitchens);
	$x = "";
	foreach ($result_kitchens as $row_kitchens) {
		$kitchen = $row_kitchens->name;
		$x .= "<option value='$kitchen'>$kitchen</option>";
	}
	$somekitchen = "<select required name='kitchen' style='width:100%; height:45px;'><option selected disabled value=''>Кухня</option>$x</select>";

	$a .= "
	<form method='POST' enctype='multipart/form-data'>
		<div style='background:#fff; width:calc(98% - 32px); display:inline-grid; grid-template-columns: repeat(4, 1fr); grid-column-gap:16px; grid-row-gap:8px; padding:16px;'>
					
			<div>
				<select name='type' required style='width:100%; height:45px;' id='typeselect'>
					<option selected disabled value=''>Тип файлов </option>
					<option value='techn_descr'>Техническое описание</option>
					<option value='3d_model'>3D модели</option>
				</select>
				
			</div>
			<div>
				$somekitchen
			</div>
			<div>
				<input name='descr' required placeholder='Описание, содержимое архива (3д модели)' style='width:100%; height:45px; border:1px solid #dcdcdc;'>
			</div>
			<div>
				Грузим файл: <br>
				<input type='file' name='loadfile' required>
			</div>
					
			<div>
				<button type='submit' style='background:#42f58d; color:#fff; border:none; outline:none; width:100%; height:45px; cursor:pointer;'>Загрузить</button>
			</div>
		</div>
	</form>
	
	";


	if (isset($_POST['type'])) {
		$kitchen = $_POST['kitchen'];
		$type = $_POST['type'];
		$descr = $_POST['descr'];
		$anyfile = $_FILES['loadfile'];
		$filename = $_FILES['loadfile']['name'];
		$tmpname = $_FILES['loadfile']['tmp_name'];
		$file_ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$dir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/";
		$data_dir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/data_dir/";
		if (!is_dir($dir))
			mkdir($dir);
		if (!is_dir($data_dir))
			mkdir($data_dir);

		$datadir_link = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/data_dir/$filename";

		//Грузим файл
		move_uploaded_file($tmpname, $datadir_link);

		$sql = "INSERT INTO gi_designer_architect SET kitchen='$kitchen', type = '$type', setfile='$filename', descr='$descr'";
		$result = $wpdb->get_results($sql);
		//echo $sql;
		echo "<script>window.location.reload();</script>";

	}


	//РЕДАКТОР
	$b = "<h1>Удаляем файлы</h1>";
	$sql2 = "SELECT * FROM gi_designer_architect";
	$result2 = $wpdb->get_results($sql2);
	$b .= "<div style='width:calc(98% - 64px); padding:32px; background:#fff; display:inline-grid; grid-template-columns:repeat(2,1fr); grid-gap:16px;'>";
	foreach ($result2 as $row2) {
		$newid = $row2->newid;
		$type = $row2->type;
		$kitchen = $row2->kitchen;
		$setfile = $row2->setfile;
		switch ($type) {
			case '3d_model':
				$str_type = '3D Model';
				break;
			case 'techn_descr':
				$str_type = 'Technical description';
				break;
			default:
				break;
		}
		$b .= "
		<div class='div_$newid' style='width:calc(100% - 16px); display:inline-grid; grid-template-columns:4fr 4fr 1fr; border:1px solid #dcdcdc; padding:8px;'>
			<div>$str_type</div>
			<div>$kitchen</div>
			<div>
				<button id='$newid' style='cursor:pointer;' onclick=\"delete_file($newid, '$setfile');\">Удалить</button>
			</div>
		</div>";

	}

	$b .= "
	<script>
	function delete_file(thisid, thisfile){
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/redact_designers_files.php',
			type: 'POST',
			data: {thisid: thisid, thisfile:thisfile},
			success: function(data){
				jQuery('.div_'+thisid).css('display','none');
				alert('Изменения внесены');
			} 
		});
	}
	</script>
	";
	$b .= "</div>";

	echo $a . $b;
}

function user_files()
{

	global $user_role;
	if ($user_role == 'designer_architect') {
		header("Location: /designer-conditions/");
	}

	global $wpdb;
	global $lang_adm;

	$a = "<div style='width:100%; background:white;'>";
	$a .= "<h1>$lang_adm->f_h_1</h1>";
	$a .= "
	<p>$lang_adm->f_p1</p>
	<p>$lang_adm->f_p2</p>
	<p>$lang_adm->f_p3</p>
	<p>$lang_adm->f_p4</p>
	";
	//Получаем категории
	$sql = "SELECT DISTINCT category FROM gi_dealers_files ORDER BY category";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$cat = $row->category;
		switch ($cat) {
			case "1 Программа просчёта":
				$cat_h2 = $lang_adm->f_h_2;
				break;
			case "2 Инструкции и требования":
				$cat_h2 = $lang_adm->f_h_3;
				break;
			case "3 Техническая информация":
				$cat_h2 = $lang_adm->f_h_4;
				break;
			case "4 Рекламная Информация":
				$cat_h2 = $lang_adm->f_h_5;
				break;
			case "":
				$cat_h2 = $lang_adm->f_h_6;
				break;
		}
		$a .= "<h2 class='filesCat'>$cat_h2</h2>";
		$a .= "<div class='allfiles'>";
		$sql_files = "SELECT * FROM gi_dealers_files WHERE category = '$cat' ORDER BY name ASC";
		$result_files = $wpdb->get_results($sql_files);
		foreach ($result_files as $row_files) {
			$name = $row_files->name;
			$url = str_contains($row_files->url, setWebsiteUrl()) || str_contains($row_files->url, 'https://geosideal.bitrix24.ru')? $row_files->url : setWebsiteUrl() . $row_files->url;
			// $extension = $row_files->extension;
			$icon = $row_files->icon;
			$category = $row_files->category;
			$rank = $row_files->rank;
			// switch($extension){
			// 	case 'jpg': $img = '/wp-content/uploads/2019/04/jpg.png'; break;
			// 	case 'jpeg': $img = '/wp-content/uploads/2019/04/jpg.png'; break;
			// 	case 'JPEG': $img = '/wp-content/uploads/2019/04/jpg.png'; break;
			// 	case 'JPG': $img = '/wp-content/uploads/2019/04/jpg.png'; break;
			// 	case 'png': $img = '/wp-content/uploads/2019/04/png.png'; break;
			// 	case 'doc': $img = '/wp-content/uploads/2019/04/doc.png'; break;
			// 	case 'docx': $img = '/wp-content/uploads/2019/04/doc.png'; break;
			// 	case 'txt': $img = '/wp-content/uploads/2019/04/doc.png'; break;
			// 	case 'pdf': $img = '/wp-content/uploads/2019/04/pdf.png'; break;
			// 	case 'zip': $img = '/wp-content/uploads/2019/04/zip.png'; break;
			// 	case 'rar': $img = '/wp-content/uploads/2019/04/rar.png'; break;
			// 	case 'cat': $img = '/wp-content/uploads/2019/04/kd.png'; break;
			// 	case 'xls': $img = '/wp-content/uploads/2020/08/xls.png'; break;
			// 	case 'xlsx': $img = '/wp-content/uploads/2020/08/xls.png'; break;
			// 	case 'csv': $img = '/wp-content/uploads/2020/08/xls.png'; break;
			// }

			switch ($icon) {
				case 'range':
					$icon_file = '/wp-content/uploads/for_dealer/range.svg';
					break;
				case 'info-for-dealer':
					$icon_file = '/wp-content/uploads/for_dealer/information_for_dealers.svg';
					break;
				case 'info-kd':
					$icon_file = '/wp-content/uploads/for_dealer/information_about_KD.svg';
					break;
				case 'catalog-KD':
					$icon_file = '/wp-content/uploads/for_dealer/catalog_KD.svg';
					break;
				case 'promotional-items':
					$icon_file = '/wp-content/uploads/for_dealer/promotional_items.svg';
					break;
				case 'datasheet':
					$icon_file = '/wp-content/uploads/for_dealer/datasheet.svg';
					break;
				case 'requirements':
					$icon_file = '/wp-content/uploads/for_dealer/requirements.svg';
					break;
				case 'files-for-KD':
					$icon_file = '/wp-content/uploads/for_dealer/files_for_KD.svg';
					break;
				case 'info-tech-character':
					$icon_file = '/wp-content/uploads/for_dealer/info_tech_character.svg';
					break;
				case 'info-tech-character':
					$icon_file = '/wp-content/uploads/for_dealer/info_tech_character.svg';
					break;
				case 'visualision-download':
					$icon_file = '/wp-content/uploads/for_dealer/visualision-download.svg';
					break;
				case 'video-download':
					$icon_file = '/wp-content/uploads/for_dealer/video-download.svg';
					break;
				case 'video-instruction':
					$icon_file = '/wp-content/uploads/for_dealer/video-instruction.svg';
					break;
			}
			$a .= "
			<div class='dealerfile'>
				<a href='$url' target='_blank'>
					<img src='$icon_file' class='fileimage'/>
					<span class='filename'>$name</span>
				</a>
			</div>
			";
		}

		$a .= "</div>";
	}


	$a .= "</div>";



	return $a;
}

function my_files()
{
	global $wpdb;
	$a = "<div style='width:100%; background:white; padding:20px 10px; margin-top:20px;'>";
	$a .= "<h1>Файлы</h1>";
	$a .= "

	<p>Каталоги коллекций ГеосИдеал и ГеосПлюс для KD обновляются на сайте практически ежемесячно (к первому числу месяца). Для того чтобы обновить каталог, необходимо кликнуть на соответствующую иконку и сохранить скачанный файл в папке <b>C:\KD\Catalogs</b>.</p>
	<p>Кроме того, периодически необходимо обновлять \"Документы отчётов WORD\" и \"Текстуры\"</p>
	<p>Новым дилерам до начала работы с клиентами в обязательном порядке следует ознакомиться с «Техническим описанием кухонь», а также «Руководством пользователя программой KitchenDraw». </p>
	
	";
	//Получаем категории
	$sql = "SELECT DISTINCT category FROM gi_dealers_files ORDER BY category";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$cat = $row->category;
		$a .= "<h2>$cat</h2>";
		$a .= "<div style='width:100%;'>";
		$sql_files = "SELECT * FROM gi_dealers_files WHERE category = '$cat' ORDER BY `name` ASC";
		$result_files = $wpdb->get_results($sql_files);
		foreach ($result_files as $row_files) {
			$name = $row_files->name;
			$url = $row_files->url;
			$extension = $row_files->extension;
			$category = $row_files->category;
			$rank = $row_files->rank;
			switch ($extension) {
				case 'jpg':
					$img = '/wp-content/uploads/2019/04/jpg.png';
					break;
				case 'jpeg':
					$img = '/wp-content/uploads/2019/04/jpg.png';
					break;
				case 'JPEG':
					$img = '/wp-content/uploads/2019/04/jpg.png';
					break;
				case 'JPG':
					$img = '/wp-content/uploads/2019/04/jpg.png';
					break;
				case 'png':
					$img = '/wp-content/uploads/2019/04/png.png';
					break;
				case 'doc':
					$img = '/wp-content/uploads/2019/04/doc.png';
					break;
				case 'docx':
					$img = '/wp-content/uploads/2019/04/doc.png';
					break;
				case 'txt':
					$img = '/wp-content/uploads/2019/04/doc.png';
					break;
				case 'pdf':
					$img = '/wp-content/uploads/2019/04/pdf.png';
					break;
				case 'zip':
					$img = '/wp-content/uploads/2019/04/zip.png';
					break;
				case 'rar':
					$img = '/wp-content/uploads/2019/04/rar.png';
					break;
				case 'cat':
					$img = '/wp-content/uploads/2019/04/kd.png';
					break;
			}
			$a .= "
			<a href='$url' download>
				<div style='width:120px; display:inline-block; vertical-align:top; margin:5px; text-align:center;'>
					<img src='$img' class='fileimage' style='max-width:75px;'/><br>
					$name<br>
				</div>
			</a>
			";
		}
		$a .= "</div>";
	}
	$a .= "</div>";
	echo $a;
}

function add_file()
{
	global $wpdb;


	echo "<h1>Добавить файл</h1>";
	echo "Сперва загрузите файл в разделе \"Медиафайлы\" <br><br>";

	$a = "
	<div style='width:93%; min-height:50px; background:white; text-align:center; padding: 3%'>
		<form method='POST' enctype='multipart/form-data'>
			<select name='category' required style='width:200px; height:50px; display:inline-block; margin-right: 15px;'>
				<option value='' selected disabled>Выберите категорию</option>
				<option value='1 Программа просчёта'>Программа просчёта</option>
				<option value='2 Инструкции и требования'>Инструкции и требования</option>
				<option value='3 Техническая информация'>Техническая информация</option>
				<option value='4 Рекламная Информация'>Рекламная Информация</option>
			</select>
			<input name='filename' placeholder='Введите название' style='width:220px; height:50px; display:inline-block; margin-right: 15px;' required/>
			<!----<input name='url' placeholder='Ссылка на файл' style='width:280px; height:50px; display:inline-block; margin-right: 15px;' required/>--->
			<input type='file' name='newfile' required/>
			<input name='rank' placeholder='№' style='width:70px; height:50px; display:inline-block; margin-right: 15px;' required/>
			<div style='display:inline-block; margin-right: 15px;'>
				<input type='radio' id='iconChoice1'
				name='icon' value='range'>
				<label for='iconChoice1'><img src='/wp-content/uploads/for_dealer/range.svg'></label>

				<input type='radio' id='iconChoice2'
				name='icon' value='info-for-dealer'>
				<label for='iconChoice2'><img src='/wp-content/uploads/for_dealer/information_for_dealers.svg'></label>

				<input type='radio' id='iconChoice3'
				name='icon' value='info-kd'>
				<label for='iconChoice3'><img src='/wp-content/uploads/for_dealer/information_about_KD.svg'></label>

				<input type='radio' id='iconChoice4'
				name='icon' value='catalog-KD'>
				<label for='iconChoice4'><img src='/wp-content/uploads/for_dealer/catalog_KD.svg'></label>

				<input type='radio' id='iconChoice5'
				name='icon' value='promotional-items'>
				<label for='iconChoice5'><img src='/wp-content/uploads/for_dealer/promotional_items.svg'></label>

				<input type='radio' id='iconChoice6'
				name='icon' value='datasheet'>
				<label for='iconChoice6'><img src='/wp-content/uploads/for_dealer/datasheet.svg'></label>

				<input type='radio' id='iconChoice7'
				name='icon' value='requirements'>
				<label for='iconChoice7'><img src='/wp-content/uploads/for_dealer/requirements.svg'></label>

				<input type='radio' id='iconChoice8'
				name='icon' value='files-for-KD'>
				<label for='iconChoice8'><img src='/wp-content/uploads/for_dealer/files_for_KD.svg'></label>

				<input type='radio' id='iconChoice9'
				name='icon' value='info-tech-character'>
				<label for='iconChoice9'><img src='/wp-content/uploads/for_dealer/info_tech_character.svg'></label>

				<input type='radio' id='iconChoice10'
				name='icon' value='visualision-download'>
				<label for='iconChoice10'><img src='/wp-content/uploads/for_dealer/visualision-download.svg'></label>

				<input type='radio' id='iconChoice11'
				name='icon' value='video-download'>
				<label for='iconChoice11'><img src='/wp-content/uploads/for_dealer/video-download.svg'></label>

				<input type='radio' id='iconChoice12'
				name='icon' value='video-instruction'>
				<label for='iconChoice12'><img src='/wp-content/uploads/for_dealer/video-instruction.svg'></label>
			</div>
			<input type='submit' name='addfile' value='Добавиь' style='width:220px; height:50px; border:1px solid #494949; background:#494949; color:white; cursor:pointer; display:inline-block; margin-right: 15px;'/>
		</form>	
	</div>
	";

	$a .= "
	<h1>Файлы в каталоге</h1>
	<div style='width:98%; min-height:50px; background:white; text-align:center;'>
	";
	$sqlfiles = "SELECT * FROM gi_dealers_files ORDER BY category, `rank`";
	$resultfiles = $wpdb->get_results($sqlfiles);
	foreach ($resultfiles as $rowfiles) {
		$newid = $rowfiles->newid;
		$name = $rowfiles->name;
		$category = $rowfiles->category;
		$icon = $rowfiles->icon;
		$url = $rowfiles->url;
		$rank = $rowfiles->rank;
		switch ($category) {
			case "1 Программа просчёта":
				$selected_program = "selected";
				$selected_instruction = "";
				$selected_info = "";
				$selected_marketing = "";
				break;
			case "2 Инструкции и требования":
				$selected_program = "";
				$selected_instruction = "selected";
				$selected_info = "";
				$selected_marketing = "";
				break;
			case "3 Техническая информация":
				$selected_program = "";
				$selected_instruction = "";
				$selected_info = "selected";
				$selected_marketing = "";
				break;
			case "4 Рекламная Информация":
				$selected_program = "";
				$selected_instruction = "";
				$selected_info = "";
				$selected_marketing = "selected";
				break;
		}
		$range = "";
		$info_for_dealer = "";
		$info_kd = "";
		$catalog_KD = "";
		$promotional_items = "";
		$datasheet = "";
		$requirements = "";
		$files_for_KD = "";
		$info_tech_character = "";
		$video_download = "";
		$visualision_download = "";
		$video_instruction = "";
		switch ($icon) {
			case "range":
				$range = "selected";
				break;
			case "info-for-dealer":
				$info_for_dealer = "selected";
				break;
			case "info-kd":
				$info_kd = "selected";
				break;
			case "catalog-KD":
				$catalog_KD = "selected";
				break;
			case "promotional-items":
				$promotional_items = "selected";
				break;
			case "datasheet":
				$datasheet = "selected";
				break;
			case "requirements":
				$requirements = "selected";
				break;
			case "files-for-KD":
				$files_for_KD = "selected";
				break;
			case "info-tech-character":
				$info_tech_character = "selected";
				break;
			case "info-tech-character":
				$info_tech_character = "selected";
				break;
			case "info-tech-character":
				$info_tech_character = "selected";
				break;
			case "info-tech-character":
				$info_tech_character = "selected";
				break;
			case "video-download":
				$video_download = "selected";
				break;
			case "visualision-download":
				$visualision_download = "selected";
				break;
			case "video-instruction":
				$video_instruction = "selected";
				break;
			case "":
				break;
		}
		$a .= "
		<form method='POST'>
			<select name='category' required style='width:260px; height:50px;'>
				<option value='1 Программа просчёта' $selected_program>Программа просчёта</option>
				<option value='2 Инструкции и требования' $selected_instruction>Инструкции и требования</option>
				<option value='3 Техническая информация' $selected_info>Техническая информация</option>
				<option value='4 Рекламная Информация' $selected_marketing>Рекламная Информация</option>
			</select>

			<select name='icon'>
				<option value='range' $range>1</option>
				<option value='info-for-dealer' $info_for_dealer>2</option>
				<option value='info-kd' $info_kd>3</option>
				<option value='catalog-KD' $catalog_KD>4</option>
				<option value='promotional-items' $promotional_items>5</option>
				<option value='datasheet' $datasheet>6</option>
				<option value='requirements' $requirements>7</option>
				<option value='files-for-KD' $files_for_KD>8</option>
				<option value='info-tech-character' $info_tech_character>9</option>
				<option value='video-download' $video_download>10</option>
				<option value='visualision-download' $visualision_download>11</option>
				<option value='video-instruction' $video_instruction>12</option>
			</select> 
			<textarea style='width:280px; height:50px; vertical-align: middle; border: 2px solid #111;' name='filename' maxlength='200' placeholder='Введите название' required >$name</textarea>
			<input name='url' placeholder='Ссылка на файл' value='$url' style='width:280px; height:50px;' required/>
			<input name='rank' placeholder='№' style='width:70px; height:50px;' value='$rank' required/>
			<button type='submit' name='redactfile' value='$newid' style='width:110px; height:50px; border:none; background:green; color:white; cursor:pointer;'>Править</button>
			<button type='submit' name='deletefile' value='$newid' style='width:110px; height:50px; border:none; background:darkred; color:white; cursor:pointer;'>Удалить</button>
		</form>	
		";
	}
	$a .= "			
	</div>
	";

	if (isset($_POST['addfile'])) {
		$category = $_POST['category'];
		//$url = $_POST['url'];
		$rank = $_POST['rank'];
		if ($_FILES['newfile']) {
			$file = $_FILES['newfile']['name'];
			$tmp_name = $_FILES['newfile']['tmp_name'];
			$url = "/kd/files/" . $file;
			move_uploaded_file($tmp_name, "..$url");
		}
		$filename = $_POST['filename'];
		$icon = $_POST['icon'];
		$fileinfo = new SplFileInfo($url);
		$ext = $fileinfo->getExtension();
		//var_dump($_FILES);
		$sql = "INSERT INTO `gi_dealers_files` (`name`, `url`, `extension`, `category`, `rank`, `icon`) VALUES ('$filename', '$url', '$ext', '$category', '$rank', '$icon')";
		$result = $wpdb->get_results($sql);
		echo "Файл добавлен";
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['redactfile'])) {
		$category = $_POST['category'];
		$filename = $_POST['filename'];
		$url = $_POST['url'];
		$rank = $_POST['rank'];
		$icon = $_POST['icon'];
		$docid = $_POST['redactfile'];
		$fileinfo = new SplFileInfo($url);
		$ext = $fileinfo->getExtension();
		$sql = "UPDATE gi_dealers_files SET category='$category', name='$filename', url='$url', `rank`='$rank', extension='$ext', icon='$icon'  WHERE `newid`='$docid'";
		$result = $wpdb->get_results($sql);
		echo "Правки внесены";
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['deletefile'])) {
		$docid = $_POST['deletefile'];
		$sql = "DELETE FROM gi_dealers_files WHERE newid='$docid'";
		$result = $wpdb->get_results($sql);
		echo "Файл удален";
		echo "<script>window.location.reload();</script>";
	}
	echo $a;
}
?>