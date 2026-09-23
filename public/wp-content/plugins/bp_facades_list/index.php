<?php
/*Plugin Name: Фасады кухонь
Description: Новый плагин по фасадам кухонь
Version: 1.0
Author: Business Park*/

add_action('admin_menu', 'add_facades_page');
function add_facades_page()
{
	add_menu_page('Фасады new', 'Фасады new!!!', 'read', __FILE__, 'facades_new', 'dashicons-media-default', 12);
	add_submenu_page(__FILE__, 'Материалы Фасада', 'Материалы Фасада', 8, 'facades_material', 'facades_material');
}


function facades_material()
{
	global $wpdb;
	$a = "<h1>Материалы Фасада</h1>";
	$a .= "<h3>Добавить материал Фасада</h3>";
	$a .= "
	<form method='POST'>
		<input type='text' name='facade_material' placeholder='Материал фасада' />
		<input type='text' name='rank_material' placeholder='Рейтинг материала' />
		<button type='submit' name='add_facade_material' value='add'>Добавить</button>
	</form>";

	$sql = "SELECT * FROM `gi_facade_material`";
	$result = $wpdb->get_results($sql);
	$a .= "<h3>Редактировать материалы Фасада</h3>";

	foreach ($result as $row) {
		$a .= "<form method='POST'>";
		$id = $row->id;
		$facade_material = $row->name;
		$facade_material_eng = $row->eng_name;
		$rank_material = $row->rank_material;
		$a .= "
		<div>
			<input type='text' name='redact_facade_material' value='$facade_material'/>
			<input type='text' name='redact_rank_material' value='$rank_material'/>
			<button type='submit' name='redact_facade_material_button' value='$id'>Изменить</button>
			<button type='submit' name='delete_facade_material_button' value='$id'>Удалить</button>
			<!-- <button type='submit' name='bind_in_db_button' value='$id'>Привязать в БД</button> -->
		</div>";
		$a .= "</form>";
	}
	$a .= "<form method='POST'>";
	$a .= "<div>
			<!-- <button type='submit' name='delete_not_in_db_button' value=''>Удалить чего нет в БД</button> -->
		</div>";
	$a .= "</form>";
	// Add material facade
	if (isset($_POST['facade_material'])) {
		$facade_material = $_POST['facade_material'];
		$facade_material_eng = replaceSymbols($facade_material);
		$rank_material = $_POST['rank_material'] ?? 0;
		$sql = "INSERT INTO `gi_facade_material`(`name`, `eng_name`, `rank_material`) VALUES ('$facade_material','$facade_material_eng','$rank_material')";
		$result = $wpdb->get_results($sql);
		echo "Материал Фасада добавлен";
		echo "<script>window.location.reload();</script>";
	}

	// Redact material facade
	if (isset($_POST['redact_facade_material_button'])) {
		$newid = $_POST['redact_facade_material_button'];
		$facade_material = $_POST['redact_facade_material'];
		$facade_material_eng = replaceSymbols($facade_material);
		$rank_material = $_POST['redact_rank_material'] ? $_POST['redact_rank_material'] : 0;
		$sql = "UPDATE `gi_facade_material` SET `name`='$facade_material', `eng_name`='$facade_material_eng',`rank_material`='$rank_material' WHERE id = '$newid'";
		$result = $wpdb->query($sql);
		echo "Материал Фасада изменен" . $rank_material;
		echo "<script>window.location.reload();</script>";
	}

	// Delete material facade
	if (isset($_POST['delete_facade_material_button'])) {
		$id = $_POST['delete_facade_material_button'];
		$sql_facades = "SELECT * FROM gi_facades_list WHERE type = '$id'";
		$result_facades = $wpdb->get_results($sql_facades);
		foreach ($result_facades as $row_facades) {
			$url_image = $row_facades->image;
			$texture = "../wp-content/uploads/facades_list/textures/" . $url_image;
			$thumb = "../wp-content/uploads/facades_list/thumbnails/" . $url_image;
			if (file_exists($texture)) {
				unlink($texture);
			}
			if (file_exists($thumb)) {
				unlink($thumb);
			}
		}
		$sql = "DELETE FROM `gi_facade_material` WHERE id = '$id'";
		$result = $wpdb->query($sql);
		$sql_facades = "DELETE FROM gi_facades_list WHERE `type` = '$id'";
		$result_facades = $wpdb->query($sql_facades);
		echo "Материал Фасада удален" . $id;
		// echo "<script>window.location.reload();</script>";
	}

	// Bind in DB material facade
	if (isset($_POST['bind_in_db_button']) && isset($_POST['redact_facade_material'])) {
		$id = $_POST['bind_in_db_button'];
		$facade_material = $_POST['redact_facade_material'];
		$sql = "UPDATE `gi_facades_list` SET `type`='$id' WHERE `type` = '$facade_material'";
		$result = $wpdb->query($sql);
		echo "Материал Фасада привязан в БД: " . $facade_material . " - " . $id;
		// echo "<script>window.location.reload();</script>";
	}

	// Delete not in DB material facade
	if (isset($_POST['delete_not_in_db_button'])) {
		// $sql = "DELETE FROM `gi_facade_material` WHERE id NOT IN (SELECT DISTINCT `type` FROM `gi_facades_list`)";
		deleteTextures("textures");
		deleteTextures("thumbnails");
	}
	echo $a;
}


function deleteTextures($directory)
{
	global $wpdb;
	$directory = "../wp-content/uploads/facades_list/" . $directory . "/";
	$dir = scandir($directory);
	foreach ($dir as $file) {
		if ($file == '.' or $file == '..')
			continue;
		$sql = "SELECT * FROM `gi_facades_list` WHERE `image` = '$file'";
		$result = $wpdb->get_results($sql);
		if (empty($result)) {
			unlink($directory . $file);
		}
	}
}

function materialFacadeSelect($a)
{
	global $wpdb;
	$sql_material = "SELECT * FROM gi_facade_material ORDER BY `rank_material` ASC";
	$result_material = $wpdb->get_results($sql_material);
	foreach ($result_material as $row_material) {
		$material = $row_material->name;
		$id = $row_material->id;
		$a .= "<option value='$id'>$material</option>";
	}
	return $a;
}

function facades_new()
{
	global $wpdb;

	$x = "<div style='display:inline-grid; grid-template-columns:repeat(3,1fr); grid-gap:8px;'>";
	$search_kitchens = "<select name='search_kitchens' id='search_kitchens' style='height:45px;' id='mat_filter' onchange='search_filtres();'>
							<option selected disabled value=''>Дополнительный фильтр по моделям</option>
							<option value=''>Ничего не выбирать</option>";
	$sql_kitchens = "SELECT DISTINCT `name` FROM gi_kitchen ORDER BY `name`";
	$result_kitchens = $wpdb->get_results($sql_kitchens);
	foreach ($result_kitchens as $row_kitchens) {
		$kitchen = $row_kitchens->name;
		$x .= "<div><input type='checkbox' name='kitchens[]' value='$kitchen'/> $kitchen</div>";
		$search_kitchens .= "<option value='$kitchen'>$kitchen</option>";
	}
	$x .= "</div>";
	$search_kitchens .= "</select>";


	$a = "<h1>Добавить фасады</h1>";
	$a .= "<form method='POST' enctype='multipart/form-data'>";
	$a .= "<div style='width:calc(100% - 64px); padding:32px; display:inline-grid; grid-template-columns:repeat(3,2fr) 1fr; grid-gap:32px; background:#fff;'>";
	$a .= "
		<div>
			<select name='texture_type' style='width:100%; height:45px;'>
				<option selected disabled value=''>Выберите тип фасадов</option>";
	$a = materialFacadeSelect($a);
	$a .= "</select>
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
				$imagelink = "/wp-content/uploads/facades_list/textures/" . $_FILES['gallery']['name'][$i];
				$thumblink = "/wp-content/uploads/facades_list/thumbnails/" . $_FILES['gallery']['name'][$i];
				$tmp = $_FILES['gallery']['tmp_name'][$i];
				$path_parts = pathinfo($_FILES['gallery']['name'][$i]);
				$extension = $path_parts['extension'];
				//так надо :)
				$hrefimage = ".." . $imagelink;
				$hrefthumb = ".." . $thumblink;
				if ($extension == "png" or $extension == "jpg" or $extension == "jpeg" or $extension == "JPG") {
					move_uploaded_file($tmp, $hrefimage);
					echo "<script>window.location.reload();</script>";
				} else {
					echo "Вы попытались загрузить неподходящее изображение<br>";
					continue;
				}
				//задаем размеры миниатюрам
				list($width, $height) = getimagesize($hrefimage);
				$otnosh = $width / $height;
				$newWidth = 400;
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
				$sql = "INSERT INTO gi_facades_list SET name='', type='$type', image='$imagename', kitchens='$kitchens'";
				$result = $wpdb->get_results($sql);

			}
			echo $sql;
		}

	}

	//Вносим названия всякие
	$sql_names = "SELECT * FROM gi_facades_list WHERE name = ''";
	$result_names = $wpdb->get_results($sql_names);


	$b = '';
	if ($result_names) {
		$b .= "<h1>Впишите названия</h1>";
		$b .= "<div style='width:calc(100% - 64px); padding:32px; background:#fff; display:inline-grid; grid-template-columns:repeat(4, 1fr); grid-gap:32px;'>";
		foreach ($result_names as $row_names) {
			$file = $row_names->image;
			$newid = $row_names->newid;
			$imgsrc = "/wp-content/uploads/facades_list/thumbnails/" . $file;
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
				url: '/wp-content/plugins/bp_facades_list/redact_texture_name.php',
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
			<option selected disabled value=''>Выберите тип фасадов</option>";
			$c = materialFacadeSelect($c);
	$c .= "</select>
		$search_kitchens
		<br><br>";
	$c .= "<div id='redact_content' style='width:98%; display:inline-grid; grid-template-columns:repeat(1,1fr); grid-row-gap:32px;'>";
	$sql2 = "SELECT * FROM gi_facades_list WHERE name != '' ORDER BY `facade_rank` ASC LIMIT 15";
	$result2 = $wpdb->get_results($sql2);

	foreach ($result2 as $row2) {
		$newid = $row2->newid;
		$type = $row2->type;
		$kitchens = $row2->kitchens;
		$thisfile = $row2->image;
		$thisname = $row2->name;
		$rank = $row2->facade_rank;
		$thisdescr = $row2->description;
		$new = $row2->newfacade;
		$antibac = $row2->antibac;
		$silver = $row2->silver;
		$checked_new = "";
		$checked_silver = "";
		$checked_antibac = "";
		if ($new) {
			$checked_new = "checked";
		} else {
			$checked_new = "";
		}

		if ($silver) {
			$checked_silver = "checked";
		} else {
			$checked_silver = "";
		}

		if ($antibac) {
			$checked_antibac = "checked";
		} else {
			$checked_antibac = "";
		}

		$kitchen_cheboxes = "<div id='div_content_$newid' style='width:100%; display:inline-grid; grid-template-columns:repeat(4,1fr); grid-gap:8px;'>";
		$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen";
		$result_kitchens = $wpdb->get_results($sql_kitchens);
		foreach ($result_kitchens as $row_kitchens) {
			$kitchen = $row_kitchens->name;
			if (stristr($kitchens, $kitchen)) {
				$checked = "checked";
			} else {
				$checked = "";
			}
			$kitchen_cheboxes .= "
			<div class='kitchen_checkboxes_$newid'><input type='checkbox' name='kitchens[]' value='$kitchen' $checked> $kitchen</div>
			";
		}
		$kitchen_cheboxes .= "</div>";

		$imgurl = "/wp-content/uploads/facades_list/textures/$thisfile";
		$thumburl = "/wp-content/uploads/facades_list/thumbnails/$thisfile";
		$c .= "
		<div id='redact_$newid' style='width:calc(100% - 32px); background:#fff; box-shadow:3px 6px 18px rgba(1,1,1,0.1); display:inline-grid; grid-template-columns:1fr 2fr 1fr 5fr 1fr; grid-gap:16px; padding:16px;'>
			<div>
				<a href='$imgurl' target='_blank'>
					<img src='$thumburl' style='width:100%; max-height:150px;'/>
					<input id='file_$newid' value='$thisfile' style='display:none;'>
				</a>
			</div>
			<div>	
				<input id='thisname_$newid' name='thisname' value='$thisname' style='width:100%; height:45px; border:1px solid #dcdcdc;'><br>
				<textarea id='thisdescr_$newid' style='width:100%; height:45px; border:1px solid #dcdcdc; margin-top:8px;' placeholder='Описание'>$thisdescr</textarea>
				<div class='input-type' style='display: grid; grid-template-columns: 1fr 1fr;'>
					<div class='input-rate'>
						Рейтинг: <input name='thisrank' value='$rank' id='thisrank_$newid' style='margin-top:8px; width:45px; height:35px; text-align:center; border:1px solid #dcdcdc; margin-right:16px;' type='number' min='0'/>
					</div>
					<div>
						<div class='input-new' style='float: right; margin-bottom: 5px'>
						Новинка <input id='new_$newid' type='checkbox' name='new' value='new' style='margin-left:8px;' $checked_new/>
						</div>
						<div class='input-silver' style='float: right; margin-bottom: 5px'>
						Silver Defence <input id='silver_$newid' type='checkbox' name='silver' value='silver' style='margin-left:8px;' $checked_silver/>
						</div>
						<div class='input-antibac' style='float: right; margin-bottom: 5px'>
						Antibac <input id='antibac_$newid' type='checkbox' name='antibac' value='antibac' style='margin-left:8px;' $checked_antibac/>
						</div>
					</div>
				</div>
			</div>
			<div>
				<select id='thistype_$newid' name='thistype' style='width:100%; height:45px;'>
					<option selected disabled value=''>Выберите тип фасадов</option>";
		$c = materialFacadeSelect($c);

		$c .= "</select>
			</div>
			
			$kitchen_cheboxes
			
			<div>
				<button id='$newid' value='redact' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:green; border:none; padding:8px 24px;' onclick=\"redact_texture($newid);\">Изменить</button>
				<button id='$newid' value='delete' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:darkred; border:none; padding:8px 24px' onclick=\"delete_texture($newid);\">Удалить</button>
			</div>				
		</div>";

		$c .= "<script>
		jQuery('#thistype_$newid option[value=\'$type\']').attr('selected','selected');
		</script>";

	}
	$c .= "</div>";


	$c .= "
	<link rel='stylesheet' href='/wp-content/plugins/bp_facades_list/css/style.css'>
	<script src='/wp-content/plugins/bp_contracts/js/utility.js'></script>
	<script src='/wp-content/plugins/bp_facades_list/js/facades_list.js'></script>";


	$d = "
	<script>
	// jQuery('#search_redact').keyup(function(){
		// search_filtres();
	// });
	function search_filtres(){
		thisval = jQuery('#search_redact').val();
		matval = jQuery('#mat_filter').val();
		modelval = jQuery('#search_kitchens').val();
		jQuery.ajax({
			url: '/wp-content/plugins/bp_facades_list/search_redact_files.php',
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


?>