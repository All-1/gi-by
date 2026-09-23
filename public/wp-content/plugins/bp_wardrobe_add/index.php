<?php
/*Plugin Name: bp_wardrobe_add
Description: Работа с гардеробами. Добавление в каталог, редактирование.
Version: 1.0
Author: Business Park*/
add_action('admin_menu', 'add_pages_wardrobe');
function add_pages_wardrobe()
{
	add_menu_page('Каталог гардеробов', 'Каталог гардеробов', 8, __FILE__, 'wardrobe_catalogue', 'dashicons-images-alt2');
	add_submenu_page(__FILE__, 'Добавить гардероб', 'Добавить гардероб', 8, 'add_wardrobe_page', 'add_wardrobe');
	add_submenu_page(__FILE__, 'Редактировать', 'Редактировать', 8, 'sub-wardrobe-page4', 'redact_wardrobe');
	add_submenu_page(__FILE__, 'Наборы фильтров', 'Фильтры', 8, 'sub-wardrobe-page6', 'filtres_wardrobe_page');
	add_submenu_page(__FILE__, 'Инструкции', 'Инструкции', 8, 'sub-wardrobe-page5', 'instructions_wardrobe');
	add_menu_page('Наполнение', 'Наполнение', 8, 'fittings_page', 'add_fittings', 'dashicons-editor-customchar');
	add_submenu_page('fittings_page', 'Редактировать', 'Редактировать', 8, 'redact_fittings', 'redact_fittings');
}


function filtres_wardrobe_page()
{
	global $wpdb;

	echo "<h1>Наборы фильтров</h1>";
	$a = "
	<h2>Добавить новый фильтр</h2>
	<form method='POST' class='addfilterform'>
		<select name='category' required class='selectcategory'>
			<option selected disabled value=''>Выберите тип фильтра</option>
			<option value='color'>Цвет гардероба </option>
			<option value='style'>Стиль гардероба </option>
			<option value='config'>Конфигурация </option>
		</select><br>
		<input type='text' name='filtervalue' placeholder='Значение фильтра' required/><br>
		<input type='text' name='color' placeholder='Цвет' style='display:none;' id='colorinput'/><br><br>
		
		<input type='submit' name='addFilter' value='Добавить'/>
		
	</form>
	";

	//Фильтры по цвету
	$a .= "
	<h2>Фильтры по цвету:</h2>
	<div style='background:white; padding:20px; width:max-content; max-width:calc(98% - 40px);'>
	<form method='POST'>";
	$sql = "SELECT * FROM gi_wardrobe_filtres WHERE category = 'color'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$newid = $row->newid;
		$filtervalue = $row->filtervalue;
		$color = $row->color;
		$a .= "
		<div style='float:left; margin:7px;'>
			<div style='width:20px; height:20px; background:$color; float:left; float:left; margin-right:5px;'></div> $filtervalue <button name='delete' value='$newid'>X</button> <br>
		</div>
		";
	}
	$a .= "
	<div style='clear:both;'></div>
	</form>
	</div>
	";

	//Фильтры по конфигурации
	$a .= "
	<h2>Фильтры по конфигурации:</h2>
	<div style='background:white; padding:20px; width:max-content; max-width:calc(98% - 40px);'>
	<form method='POST'>";
	$sql = "SELECT * FROM gi_wardrobe_filtres WHERE category = 'config'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$newid = $row->newid;
		$filtervalue = $row->filtervalue;
		$a .= "
		<div style='float:left; margin:7px;'>
			$filtervalue <button name='delete' value='$newid'>X</button> <br>
		</div>
		";
	}
	$a .= "
	<div style='clear:both;'></div>
	</form>
	</div>
	";


	//Фильтры по стилю
	$a .= "
	<h2>Фильтры по стилю:</h2>
	<div style='background:white; padding:20px; width:max-content; max-width:calc(98% - 40px);'>
	<form method='POST'>";
	$sql = "SELECT * FROM gi_wardrobe_filtres WHERE category = 'style'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$newid = $row->newid;
		$filtervalue = $row->filtervalue;
		$a .= "
		<div style='float:left; margin:7px;'>
			$filtervalue <button name='delete' value='$newid'>X</button> <br>
		</div>
		";
	}
	$a .= "
	<div style='clear:both;'></div>
	</form>
	</div>
	";

	$a .= "
	<script>
	jQuery( '.selectcategory' ).change(function() {
		var type = jQuery( '.selectcategory' ).val();
		if(type == 'color'){
			var colorinput = document.getElementById('colorinput');
			colorinput.style.display='block';
		}
	});
	
	</script>
	";

	//Удаляем фильтр
	if (isset($_POST['delete'])) {
		$deleteid = $_POST['delete'];
		$sql = "DELETE FROM gi_wardrobe_filtres WHERE newid = '$deleteid'";
		$result = $wpdb->get_results($sql);
		echo "Фильтр удален";
		echo "<script>window.location.reload();</script>";
	}
	//Добавляем фильтр
	if (isset($_POST['addFilter'])) {
		$category = $_POST['category'];
		$filtervalue = $_POST['filtervalue'];
		$color = $_POST['color'];

		$sql = "INSERT INTO `gi_wardrobe_filtres` (`category`, `filtervalue`, `color`) VALUES ('$category', '$filtervalue', '$color');";
		$result = $wpdb->get_results($sql);
		echo "Фильтр добавлен";
		echo "<script>window.location.reload();</script>";
	}

	echo $a;
}


function wardrobe_catalogue()
{
	echo "<h1>Каталог гардероба</h1>";
	echo "Ниже перечислены гардероба, уже добавленые в каталог<br>";
	global $wpdb;
	$sql = "SELECT * FROM `gi_wardrobe`";
	$result = $wpdb->get_results($sql);
	$a = "<div style='width:100%; text-align:center;'>
	<form method='POST'>
	";
	foreach ($result as $row) {
		$name = $row->name;
		$description = $row->description;
		$avatar = $row->avatar;
		$directory = $row->folder;
		$type = $row->type;
		$cost = $row->basic_cost;
		$nameEng = $row->name_eng;
		$newid = $row->newid;
		$folder = $row->folder;
		$a .= "
		<div class='kitchen_point' style='width:330px; margin:20px; display:inline-block; position:relative;'>
		<input type='checkbox' style='position:absolute; top:7px; left:5px;' name='$newid' value='$newid'/>
			<div style='width:100%; height:200px; background:url($avatar) no-repeat; background-size:cover; background-position:center;'></div>
			<h2>Гардероб $name</h2>
			Ярлык: <b>$nameEng</b>
			<div style='width:100%; text-align:right; font-size:16px; color:green; font-weight:600;'>$cost руб.</div>
			<br><hr>
		</div>		
		";
		if (isset($_POST['wardrobeDelete']) and isset($_POST[$newid])) {
			$delete_newid = $_POST[$newid];
			$sqlDelete = "DELETE FROM gi_wardrobe WHERE newid='$delete_newid'";
			$resultDelete = $wpdb->get_results($sqlDelete);
			$sqlFacade = "DELETE FROM gi_facade WHERE wardrobe_eng = '$nameEng'";
			$resultFacade = $wpdb->get_results($sqlFacade);
			$deleteImage = ".." . $avatar;
			unlink($deleteImage);
			$folder = ".." . $folder;
			//Удаление папки и вложенных файлов (rmdir уаляет только пустую) - пишем функцию 
			function removeWardrobeDir($folder)
			{
				if ($objs = glob($folder . "/*")) {
					foreach ($objs as $obj) {
						is_dir($obj) ? removeWardrobeDir($obj) : unlink($obj);
					}
				}
				rmdir($folder);
			}
			removeWardrobeDir($folder);

			echo "Выбранные гардероба удалены";
			echo "<script>window.location.reload();</script>";
		}
	}
	$a .= "<br><Br><input type='submit' name='wardrobeDelete' value='Удалить гардероба'/>
	</form>
	</div>";
	echo $a;
}
//ДОБАВЛЕНИЕ гардероба
function add_wardrobe()
{
	$phpself = $_SERVER['PHP_SELF'];
	$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
	$lat = array('-', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');
	global $wpdb;
	echo "<h1>Добавить гардероб</h1>";
	$a = "
	<form method='POST' enctype='multipart/form-data'>
		<div style='width:46%; margin:1%; background:white; padding:1% 1%; float:left;'>
			<h2>Заполните поля формы</h2><br>
			<select name='type' style='width:100%; height:40px;'>
				<option disabled selected required>Выберите стиль гардероба</option>
				<option value='Классика'>Классика</option>
				<option value='Модерн'>Модерн</option>
			</select><br>
			Название гардероба:<br>
			<input type='text' name='name' required style='width:100%; height:40px;'><br>
			Дополнительное название:<br>
			<input type='text' name='additional_name' required style='width:100%; height:40px;'><br>
			Привязать к ярлыку:<br>
			<input type='text' name='bind_to' required style='width:100%; height:40px;'><br>
			Описание гардероба (выводится в начале страницы):<br>
			<textarea name='description' required style='width:100%; height:40px;'></textarea><br>
			Введите базовую стоимость гардероба:<br>
			<input type='text' name='cost' required style='width:100%; height:40px;'><br><br>

			Загрузите обложку гардероба (аватар):<br>
			<input type='file' name='avatar' required ><br>
			<br><bR>
			Добавить фотографии в галерею гардероба:<br>
			<input type='file' name='gallery[]' multiple required><br><br>
		";

	$a .= "<br><br><h2>Выберите материал</h2>";
	//ВЫВОД МАТЕРИАЛОВ
	$sqlMaterial = "SELECT * FROM `gi_wardrobe_material`";
	$resultMaterial = $wpdb->get_results($sqlMaterial);
	foreach ($resultMaterial as $rowMaterial) {
		$material = $rowMaterial->material;
		$material_eng = $rowMaterial->material_eng;
		$a .= "
			<div style='display:inline-block; text-align:center; vertical-align:top;'>
				<input type='checkbox' name='materials[]' value='$material'> $material
			</div>
			";
	}

	$a .= "<br><br><br>
		Введите древесину или поставьте прочерк:<br>
		<input name='wood' placeholder='Например: Дуб, ясень' style='width:100%; height:40px;  border:1px solid #dcdcdc'/><br>
		Введите материал пластика или поставьте прочерк:<br>
		<input name='plastic' placeholder='Например: Fenix' style='width:100%; height:40px;  border:1px solid #dcdcdc'/><br>
		Введите производителей фурнитуры или поставьте прочерк: <br>
		<input name='furnitura' placeholder='Например: Firmax, Blum' style='width:100%; height:40px; border:1px solid #dcdcdc;'/><br><br>
		</div>
		<div style='float:left; width:46%; margin:1%; padding:1% 1%; background:white; min-height:100px;'>
			 ";

	$a .= "<br><br><h2>Выберите ручки для гардероба</h2>";
	//ВЫВОД наполнения
	$a .= "<div style='height:400px; overflow:auto;'>";
	$sqlHandle = "SELECT `image_href`, `thumb_href`, `newid`, `model_name`, `model_name_eng` FROM `gi_handle`";
	$resultHandle = $wpdb->get_results($sqlHandle);
	foreach ($resultHandle as $rowHandle) {
		$image_href = $rowHandle->image_href;
		$thumb_href = $rowHandle->thumb_href;
		$model_name = $rowHandle->model_name;
		$model_name_eng = $rowHandle->model_name_eng;
		$newidhandle = $rowHandle->newid;
		$a .= "
			<div style='display:inline-block; text-align:center; vertical-align:top;'>
				<input type='checkbox' name='handles[]' value='$model_name' style='position:relative; top:27px; left:-35px;'> 
				<div style='width:100px; height:100px; margin:5px; background:url(../$image_href) no-repeat; background-position:center; background-size:cover;'></div>
				<div style='width:100px;'>$model_name</div>
			</div>
			";
	}
	$a .= "</div>";
	$a .= "<br><br><h2>Выберите наполнение для гардероба</h2>";
	//ВЫВОД наполнения
	$a .= "<div style='height:400px; overflow:auto;'>";
	$sqlHandle = "SELECT `image_href`, `thumb_href`, `newid`, `model_name`, `model_name_eng` FROM `gi_wardrobe_fittings`";
	$resultHandle = $wpdb->get_results($sqlHandle);
	foreach ($resultHandle as $rowHandle) {
		$image_href = $rowHandle->image_href;
		$thumb_href = $rowHandle->thumb_href;
		$model_name = $rowHandle->model_name;
		$model_name_eng = $rowHandle->model_name_eng;
		$newidhandle = $rowHandle->newid;
		$a .= "
			<div style='display:inline-block; text-align:center; vertical-align:top;'>
				<input type='checkbox' name='fittings[]' value='$model_name' style='position:relative; top:27px; left:-35px;'> 
				<div style='width:100px; height:100px; margin:5px; background:url(../$image_href) no-repeat; background-position:center; background-size:cover;'></div>
				<div style='width:100px;'>$model_name</div>
			</div>
			";
	}
	$a .= "</div>";

	//вносим данные формы в базу
	if (!empty($_POST['materials'])) {
		$string_materials = implode(",", $_POST['materials']) . ",";
	}
	if (!empty($_POST['plastic']))
		$plastic = $_POST['plastic'];
	else
		$plastic = "-";
	if (!empty($_POST['wood']))
		$wood = $_POST['wood'];
	else
		$wood = "-";
	if (!empty($_POST['furnitura']))
		$furnitura = $_POST['furnitura'];
	else
		$furnitura = "-";

	if (!empty($_POST)) {
		//объединяем выбранные столешницы в строку через запятую
		$string_tabletop = '';
		//объединяем выбранные наполнители
		$string_handle = !empty($_POST['handles']) ? implode(",", $_POST['handles']) : '';
		$string_fittings = !empty($_POST['fittings']) ? implode(",", $_POST['fittings']) : '';
		$name_wardrobe = $_POST['name'];
		$additional_name = $_POST['additional_name'];
		$bind_to = $_POST['bind_to'];
		$name_wardrobe_tech = 'Гардеробная ' . $_POST['name'];
		$type_wardrobe = $_POST['type'];
		$description_wardrobe = $_POST['description'];
		$cost_wardrobe = $_POST['cost'];
		$name_wardrobe_eng = str_replace($rus, $lat, $name_wardrobe_tech);
		//проверяем наличие папок
		$images_dir = "../wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_wardrobe_eng/";
		if (!file_exists($images_dir)) {
			mkdir($images_dir);
			mkdir("$images_dir/avatar/");
			mkdir("$images_dir/gallery/");
			mkdir("$images_dir/gallery/images");
			mkdir("$images_dir/gallery/thumbs");
			mkdir("$images_dir/facade/");
			mkdir("$images_dir/facade/images");
			mkdir("$images_dir/facade/thumbs");
		} else {
			echo "Такой гардероб уже существует!";
			die;
		}
		//Загружаем фотографии галереи
		if (!empty($_FILES['gallery']) and $_FILES['gallery']['name'] == true) {
			foreach ($_FILES['gallery']['name'] as $numberAddFile => $nameAddFile) {
				$$numberAddFile = $nameAddFile; //надо 2 знака $
			}
			for ($i = 0; $i <= $numberAddFile; $i++) {
				$imagelink = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_wardrobe_eng/gallery/images/" . basename($_FILES['gallery']['name'][$i]);
				$thumblink = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_wardrobe_eng/gallery/thumbs/" . basename($_FILES['gallery']['name'][$i]);
				// $imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
				// $thumblink = str_replace($rus, $lat, $thumblink);
				$tmp = $_FILES['gallery']['tmp_name'][$i];
				$path_parts = pathinfo($_FILES['gallery']['name'][$i]);
				$extension = $path_parts['extension'];
				//так надо :)
				$hrefimage = ".." . $imagelink;
				$hrefthumb = ".." . $thumblink;
				if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
					move_uploaded_file($tmp, $hrefimage);
				} else {
					echo "Вы попытались загрузить неподходящее изображение<br>";
					continue;
				}
				//задаем размеры миниатюрам
				list($width, $height) = getimagesize($hrefimage);
				$otnosh = $width / $height;
				$newWidth = 200;
				$newHeight = $newWidth / $otnosh;
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				switch ($extension) {
					case "jpg":	//СОЗДАНИЕ JPG
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
			}
			//echo "Фотографии загружены";
		}
		//Загрузка аватара
		if (!empty($_FILES['avatar']) and $_FILES['avatar']['name'] == true) {
			if (is_uploaded_file($_FILES["avatar"]["tmp_name"])) {
				$avatarlink = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_wardrobe_eng/avatar/" . basename($_FILES['avatar']['name']);
				$avatarlink = str_replace($rus, $lat, $avatarlink);

				$tmp_avatar = $_FILES['avatar']['tmp_name'];
				$path_parts_avatar = pathinfo($_FILES['avatar']['name']);
				$extension_avatar = $path_parts_avatar['extension'];
				$upload_link = "..$avatarlink";
				if ($extension_avatar == "png" or $extension_avatar == "jpg" or $extension_avatar == "jpeg") {
					move_uploaded_file($tmp_avatar, $upload_link);
				}
			}
		}

		//СОЗДАЕМ СТРАНИЧКУ В WP
		$post_title = $name_wardrobe_tech;
		$post_content = "<!-- wp:paragraph --><p>[wardrobe_print]</p><!-- /wp:paragraph -->";
		$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
		$lat = array('-', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');

		$strannie_bukvi = array("Ą", "ą", "Ć", "ć", "Ę", "ę", "Ł", "ł", "Ń", "ń", "Ó", "ó", "Ś", "ś", "Ź", "ź", "Ż", "ż");
		$normalnie_bukvi = array("A", "a", "C", "c", "E", "e", "L", "l", "N", "n", "O", "o", "S", "s", "Z", "z", "Z", "z");

		$post_name_eng1 = mb_strtolower(str_replace($strannie_bukvi, $normalnie_bukvi, $post_title));
		$post_name_eng = str_replace($rus, $lat, $post_name_eng1);

		$sql_page = "SELECT DISTINCT post_title FROM gi_posts WHERE post_title = '$post_title' AND post_parent = 13334";
		$result_page = $wpdb->get_results($sql_page);
		$count_pages = count($result_page);
		//Задаем guid (ссылку на страницу), применяем id посл. страницы +1, чтоб ничего не поломалось в случае каких-то изменений
		$siteurl = get_site_url();

		if ($count_pages == 0) {
			// Создаем массив данных новой записи
			$post_data = array(
				'post_title' => $post_title,
				'post_content' => $post_content,
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page',
				'post_name' => $post_name_eng,
				'ping_status' => 'closed',
				'comment_status' => 'closed',
				'post_parent' => 13334,
			);
			// Вставляем запись в базу данных
			$post_id = wp_insert_post($post_data);

			//Добавление данных в БД
			$folder = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_wardrobe_eng/";
			$timestamp = time();
			$sql = "INSERT INTO `gi_wardrobe` (`name`, `additional_name`, `bind_to`, `name_eng`, `description`, `avatar`, `folder`, `type`, `basic_cost`, `fittings`, `handles`, `material`, `sales`, `wood`, `plastic`,`furnitura`,`timestamp`) 
						VALUES ('$name_wardrobe', '$additional_name', '$bind_to', '$name_wardrobe_eng', '$description_wardrobe', '$avatarlink', '$folder', '$type_wardrobe', '$cost_wardrobe', '$string_fittings', '$string_handle', '$string_materials', 'no', '$wood', '$plastic', '$furnitura', '$timestamp');";
			$result = $wpdb->get_results($sql);

			$wardrobeId = $wpdb->get_var("SELECT MAX(newid) FROM gi_wardrobe");
			addGalleryWardrobe($_FILES['gallery'], $name_wardrobe, $wardrobeId);
			echo "<br>Гардероб добавлена, теперь отредаутируйте фассады";
		} else {
			echo "Такой гардероб уже существует! Найдено повторение в базе данных по Название модели и родительской странице (каталог)";
		}



	}
	$a .= "
		</div>
		<div style='clear:both;'></div>
		<input type='submit' style='padding:24px 48px; background:green; border:none; outline:none; color:#fff;'>
	</form>
	";
	echo $a;
}
//ДОБАВЛЕНИЕ СТОЛЕШНИЦ

function instructions_wardrobe()
{
	$a = "
	<h1>Инструкции по добавлению кухонь</h1>
	Все пункты подлежат дальнейшему редактированию.<br><br>
	<b>Для того, что бы добавить гардероб, необходимо: </b><br><br>
	1) Должны быть добавлены столешницы<br>
	2) Должны быть добавлены наполнения<br>
	3) В разделе ''Добавить гардероб'' заполнить все необходимые поля.<br>
	4) В разделе ''Добавить фасады'' загрузить изображения фасадов.<br>
	5) В разделе ''Добавить фасады'' заполнить названия и описания ранее добавленных фасадов.<br>
	6) В разделе ''Каталог кухонь'' появится новый гардероб. Ярлык - адрес страницы сайта. Перейти в раздел админки ''Страницы'' и создать новую страницу с названием новой гардероба. Нажать ''Опубликовать''.<br>
	7) В строке ''Постоянная ссылка'' нажать на кнопку ''изменить''  и прописать туда ярлык гардероба.<br>
	8) На странице гардероба вставить шорткод [wardrobe_print]<br><bR><br>
	
	<b>Для того, что бы добавить столешницы, необходимо: </b><br><br>
	1) Загрузить изобажения столешниц.<br>
	2) Заполнить необходимые поля: название, описание.<br><br><br>
	
	<b>Для того, что бы добавить наполнение, необходимо: </b><br><br>
	1) Прописть название наполнения<br>
	2) Добавить изображение наполнения.<br>
	<i>Наполнение можно добавлять только по 1шт.</i>
	";
	echo $a;
}


/*РЕДАКТИРОВАНИЕ*/

//$pageRedact = plugins_url() . "bp_wardrobe_add/redact.php";
//include ($pageRedact);

function redact_wardrobe()
{

	echo "<h1>Редактировать гардероба:</h1>";
	global $wpdb;
	$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
	$lat = array('_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
	//СЮДА ФАСАДЫ В ВИДЕ ВЫПАДАЮЩИХ ВКЛАДОК С ВОЗМОЖНОСТЬЮ УДАЛЕНИЯ (ТАБЫ) http://shpargalkablog.ru/2012/03/css-tabs.html#tab1 -------------- заморочился
	$c = "<div class='korpus'>";
	$d = "<style>
		.korpus {position:relative;}
		.korpus label {position:relative; top:0;}
		.korpus > div, .korpus > input { display: none; }
		.korpus label { padding: 5px; border: 1px solid #aaa; line-height: 28px; cursor: pointer; position: relative; bottom: 1px; background: #fff; }
		.korpus input[type='radio']:checked + label {background:#98FB98;}";


	$sqlWardrobe = "SELECT * FROM gi_wardrobe";
	$resultWardrobe = $wpdb->get_results($sqlWardrobe);
	//$kitchen_count = count($resultKitchen);
	$i = 1;
	foreach ($resultWardrobe as $rowWardrobe) {
		$wardrobe_id = $rowWardrobe->newid;
		$name = $rowWardrobe->name;
		$additional_name = $rowWardrobe->additional_name;
		$bind_to = $rowWardrobe->bind_to;
		$name_eng = $rowWardrobe->name_eng;
		$description = $rowWardrobe->description;
		$avatar = $rowWardrobe->avatar;


		$folder = $rowWardrobe->folder;
		$plastic = $rowWardrobe->plastic;
		$wood = $rowWardrobe->wood;
		$cost = $rowWardrobe->basic_cost;
		$material = $rowWardrobe->material;
		$style = $rowWardrobe->style;
		$fittings = $rowWardrobe->fittings;
		$handles = $rowWardrobe->handles;
		$furnitura = $rowWardrobe->furnitura;
		$newid = $rowWardrobe->newid;
		$similar = $rowWardrobe->similar;
		$video = $rowWardrobe->video;


		if ($i == 1)
			$checked = "checked";
		else
			$checked = "";
		$d .= ".korpus > input:nth-of-type($i):checked ~ div:nth-of-type($i){display: block; padding: 5px; border: 1px solid #aaa;}";
		$c .= "<input type='radio' name='tab' id='vk$i' $checked/><label for='vk$i'>$name - $additional_name</label>
		<div style='position: absolute; top:60px; width:95%; background:white; margin-bottom:30px;'>
		<form method='POST' enctype='multipart/form-data'>";



		$c .= "
		<div style='float:left; margin:0 15px 15px 0; word-wrap:break-word;'>
			<div style='width:400px; height:280px;  background:url(..$avatar) no-repeat; background-position:center; background-size:cover;'></div>
			<br> Загрузите новую обложку:<br>
			<input type='file' name='newAvatar'>
		</div>
		
		<div style='margin:10px; overflow:hidden; vertical-align:top; '>
			<b>Название:</b><br><textarea style='width:100%; height:30px;' name='newName' required>$name</textarea><br>
			<b>Дополнительное название:</b><br><textarea style='width:100%; height:30px;' name='newAdditionalName' required>$additional_name</textarea><br>
			<b>Модель:</b><br><textarea style='width:100%; height:30px;' name='newBindTo' required>$bind_to</textarea><br>
			<b>Описание:</b> <br><textarea style='width:100%; height:120px;' name='newDescr' required>$description</textarea>
			<b>Видео:</b> <br><textarea style='width:100%; height:30px;' name='newVideo'>$video</textarea>
			<b>Древесина:</b> <br><textarea style='width:100%; height:30px;' name='newWood' required>$wood</textarea>
			<b>Пластик:</b> <br><textarea style='width:100%; height:30px;' name='newPlastic' required>$plastic</textarea>
			<b>Фурнитура:</b> <br><textarea style='width:100%; height:30px;' name='newFurnitura' required>$furnitura</textarea>
			<b>Цена на базовую модель:</b> <br><textarea style='width:100%; height:30px;' name='newCost' required>$cost</textarea>
		</div>
		<b>Укажите типы материалов:</b><bR>
		";

		//РЕДАКТИОВАНИЕ МАТЕРИАЛОВ
		$sqlMaterial = "SELECT * FROM gi_wardrobe_material";
		$resultMaterial = $wpdb->get_results($sqlMaterial);
		foreach ($resultMaterial as $rowMaterial) {
			$allMaterials = $rowMaterial->material;
			$wardrobeMaterials = explode(', ', $material);
			$check = in_array($allMaterials, $wardrobeMaterials);
			$checked = $check ? 'checked' : '';
			$c .= "<input type='checkbox' name='newmaterials[]' value='$allMaterials' $checked> $allMaterials";
			//проверяем содержится ли в базе кухонь список материалов со значением материала из базы материалов - как-то так :)
		}

		//РЕДАКТИОВАНИЕ СТИЛЕЙ
		$c .= "<h3>Укажите стили гардероба</h3>";
		$sqlStyles = "SELECT * FROM gi_wardrobe_filtres WHERE category = 'style'";
		$resultStyles = $wpdb->get_results($sqlStyles);
		foreach ($resultStyles as $rowStyles) {
			$allStyles = $rowStyles->filtervalue;
			$findStyle = stripos($style, $allStyles);
			//проверяем содержится ли в базе кухонь список материалов со значением материала из базы материалов - как-то так :)
			if ($findStyle !== FALSE) {
				$checked = "checked";
				$c .= "<input type='checkbox' name='newstyles[]' value='$allStyles' $checked> $allStyles ";
			} else
				$c .= "<input type='checkbox' name='newstyles[]' value='$allStyles'> $allStyles ";
		}



		//РЕДАКТИРОВАНИЕ Наполнения
		$c .= "<br><br><b>Укажите ручки:</b><br>";
		$sqlHandle = "SELECT * FROM gi_handle ORDER BY `rank` ASC";
		$resultHandles = $wpdb->get_results($sqlHandle);
		foreach ($resultHandles as $rowHandles) {
			$allHandles = $rowHandles->model_name;
			$handleImage = $rowHandles->thumb_href;
			$findHandle = stripos($handles, $allHandles);
			if ($findHandle !== FALSE) {
				$checkedHandle = "checked";
				$c .= "
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:75px; background:url(..$handleImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newhandles[]' value='$allHandles' $checkedHandle>
					</div>
					 <div style='width:105px;'>$allHandles</div>
				 </div>
				";
			} else
				$c .= "
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:75px; background:url(..$handleImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newhandles[]' value='$allHandles'>
					</div>
					 <div style='width:105px;'>$allHandles</div>
				 </div>
			";
		}

		//РЕДАКТИРОВАНИЕ Наполнения
		$c .= "<br><br><b>Укажите наполнение:</b><br>";
		$sqlFitting = "SELECT * FROM gi_wardrobe_fittings ORDER BY `fitting_rank` ASC";
		$resultFittings = $wpdb->get_results($sqlFitting);
		foreach ($resultFittings as $rowFittings) {
			$allFittings = $rowFittings->model_name;
			$fittingImage = $rowFittings->thumb_href;
			$findFitting = stripos($fittings, $allFittings);
			if ($findFitting !== FALSE) {
				$checkedFittings = "checked";
				$c .= "
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:75px; background:url(..$fittingImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newFittings[]' value='$allFittings' $checkedFittings>
					</div>
					 <div style='width:105px;'>$allFittings</div>
				 </div>
				";
			} else
				$c .= "
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:75px; background:url(..$fittingImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newFittings[]' value='$allFittings'>
					</div>
					 <div style='width:105px;'>$allFittings</div>
				 </div>
			";
		}

		//ФАСАДЫ
		$c .= '<br><h2>Внимание! Фасады редактируются в разделе фасадов</h2><br>';




		//ГАЛЕРЕЯ ......
		$c .= "<br><h2>Удалить фотографии из галереи:</h2><br>";
		$sqlVisualisation = "SELECT * FROM gi_wardrobe_visualisation WHERE id_wardrobe = '$newid' ORDER BY rank_visual ASC";
		$resultVisualisation = $wpdb->get_results($sqlVisualisation);
		foreach ($resultVisualisation as $rowVisualisation) {
			$idVisial = $rowVisualisation->id;
			$nameVisualisation = $rowVisualisation->name_visual;
			$urlImage = $rowVisualisation->url_image;
			$urlThumb = $rowVisualisation->url_thumb;
			$rankVisualisation = $rowVisualisation->rank_visual;
			$c .= "
			<div style='position:relative; width:200px; display:inline-block;'>
				<div style='position:relative; width:200px; height:112px; background:url($urlThumb) no-repeat; background-position:center; background-size:cover; display:inline-block;'>
					<input type='checkbox' style='position:absolute; top:10px; left:5px;' name='deleteGallery[]' value='$idVisial'>
				</div>
				<input type='text' style='width:100%;' name='nameVisualisation[$idVisial]' value='$nameVisualisation'>
				<input type='text' name='rankVisualisation[$idVisial]' value='$rankVisualisation'>
			</div>
			";
		}
		$directoryThumb = ".." . $folder . "gallery/thumbs/";
		$directoryImage = ".." . $folder . "gallery/images/";
		// $dir = scandir($directoryThumb);
		// foreach ($dir as $number => $file) {
		// 	if ($file == '.' or $file == '..')
		// 		continue;
		// 	$nameFile = $file;
		// 	$nameFile = str_replace('_', ' ', $nameFile);
		// 	$nameFile = str_replace('-', ' ', $nameFile);
		// 	$nameFile = explode('.', $nameFile);
		// 	$c .= "
		// 	<div style='position:relative; width:130px; height:100px; display:inline-block;'>
		// 		<div style='position:relative; width:130px; height:100px; background:url($directoryThumb/$file) no-repeat; background-position:center; background-size:cover; display:inline-block;'>
		// 			<input type='checkbox' style='position:absolute; top:10px; left:5px;' name='galleryOld[]' value='$file'>
		// 		</div>
		// 		<div style=''>$nameFile[0]</div>
				
		// 	</div>
		// 	";
		// }
		$c .= "<br><h2>Добавить новые фотографии в галерею:</h2><br>
		<input type='file' name='new_gallery[]' multiple /><Br>
		";
		$sqlKitchen = "SELECT * FROM gi_kitchen";
		$resultKitchen = $wpdb->get_results($sqlKitchen);
		$c .= "<br><h2>Похожие модели кухонь:</h2><br>
				<div>
		";
		foreach ($resultKitchen as $rowKitchen) {

			$similarName = $rowKitchen->name;
			$avatarSimilar = $rowKitchen->avatar;
			$findSimilar = stripos($similar, $similarName);
			if ($findSimilar !== FALSE) {
				$checkedWardrobe = "checked";
				$c .= "
				<div style='float:left; margin-right: 10px; margin-bottom: 10px;'><div style='width:200px; height:140px;  background:url(..$avatarSimilar) no-repeat; background-position:center; background-size:cover;'>
					<input style='position:relative; top:10px; left:5px;' type='checkbox' name='similar[]' value='$similarName' $checkedWardrobe>
				</div>$similarName</div>";
			} else {
				$c .= "
				<div style='float:left; margin-right: 10px; margin-bottom: 10px;'><div style='width:200px; height:140px;  background:url(..$avatarSimilar) no-repeat; background-position:center; background-size:cover;'>
					<input style='position:relative; top:10px; left:5px;' type='checkbox' name='similar[]' value='$similarName'>
				</div>$similarName</div>";

			}

		}
		$c .= "<div style='clear: both;'></div></div>";


		$redact = "redact" . $name_eng;
		$c .= "<br><br><button type='submit' name='$redact' value='$name'>Редактировать гардероб $name - $additional_name</button>";
		$c .= "
		</form></div>";
		$i++;
		//ВНЕСЕНИЕ ИЗМЕНЕНИЙ В БАЗУ
		if (isset($_POST[$redact])) {
			//var_dump($_POST);
			if (!empty($_POST)) {
				$newName = $_POST['newName'];
				$newAdditionalName = $_POST['newAdditionalName'];
				$newBindTo = $_POST['newBindTo'];
				//$newNameEng = str_replace($rus, $lat, $newName);
				$newDescr = $_POST['newDescr'];
				$newWood = $_POST['newWood'];
				$newPlastic = $_POST['newPlastic'];
				$newCost = $_POST['newCost'];
				$newFurnitura = $_POST['newFurnitura'];
				$newVideo = $_POST['newVideo'];
				$newstyles = '';
				$newmaterials = '';
				$newFittings = '';
				$newSimilar = '';
			}
			if (!empty($_POST['newhandles'])) {
				$newhandles = implode(",", $_POST['newhandles']);
			}
			if (!empty($_POST['newFittings'])) {
				$newFittings = implode(",", $_POST['newFittings']);
			}
			if (!empty($_POST['newmaterials'])) {
				$newmaterials = implode(", ", $_POST['newmaterials']);
			}

			if (!empty($_POST['newstyles'])) {
				$newstyles = implode(", ", $_POST['newstyles']);
			}
			if (!empty($_POST['similar'])) {
				$newSimilar = implode(",", $_POST['similar']);
			}
			//АВАТАР
			if (!empty($_FILES['newAvatar']) and $_FILES['newAvatar']['name'] == true) {
				if (is_uploaded_file($_FILES["newAvatar"]["tmp_name"])) {
					$new_avatarlink = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_eng/avatar/" . $_FILES['newAvatar']['name'];
					$new_avatarlink = str_replace($rus, $lat, $new_avatarlink);
					$tmp_new_avatar = $_FILES['newAvatar']['tmp_name'];
					$path_parts_new_avatar = pathinfo($_FILES['newAvatar']['name']);
					$extension_new_avatar = $path_parts_new_avatar['extension'];
					$new_upload_link = "..$new_avatarlink";
					$unlinkAvatar = ".." . $avatar;
					if ($extension_new_avatar == "png" or $extension_new_avatar == "jpg" or $extension_new_avatar == "jpeg") {
						unlink($unlinkAvatar);
						move_uploaded_file($tmp_new_avatar, $new_upload_link);
					}
				}
			} else
				$new_avatarlink = $avatar;


			//РАБОТАЕМ С ГАЛЕРЕЕЙ
			if (!empty($_POST['gallery']) and $_POST['gallery'] == TRUE) {
				foreach ($_POST['gallery'] as $numberImage => $deleteImage) {
					unlink("$directoryThumb/$deleteImage");
					unlink("$directoryImage/$deleteImage");
				}
			}

			if (!empty($_POST['galleryOld']) && is_array($_POST['galleryOld'])) {
				foreach ($_POST['galleryOld'] as $deleteImage) {

					$nameFile = prepareVisiualName($deleteImage);
					$rankFile = getOldVisualRank($deleteImage);
					$urlImage = "$directoryImage/$deleteImage";
					$urlThumb = "$directoryThumb/$deleteImage";
					$sqlInsert = "INSERT INTO gi_wardrobe_visualisation (id_wardrobe, name_visual, url_image, url_thumb, rank_visual) VALUES ('$newid', '$nameFile', '$urlImage', '$urlThumb', '$rankFile')";
					$resultInsert = $wpdb->query($sqlInsert);
				}
			}

			$deleteImage_1 = '';
			$deleteThumb_1 = '';
			$deleteImage_2 = '';
			$deleteThumb_2 = '';
			if (!empty($_POST['deleteGallery']) && is_array($_POST['deleteGallery'])) {
				foreach ($_POST['deleteGallery'] as $deleteImage) {
					$sqlSelect = "SELECT * FROM gi_wardrobe_visualisation WHERE id = '$deleteImage'";
					$resultSelect = $wpdb->get_results($sqlSelect);
					foreach ($resultSelect as $rowSelect) {
						$urlImage = $rowSelect->url_image;
						$urlThumb = $rowSelect->url_thumb;
						if (file_exists($urlImage)) {
							$deleteImage_1 = $urlImage;
							unlink("$urlImage");
						} else {
							$deleteImage_1 = 'Нет изображения';
							$deleteImage_2 = $urlImage;
						}
						if (file_exists($urlThumb)) {
							$deleteThumb_1 = $urlThumb;
							unlink("$urlThumb");
						} else {
							$deleteThumb_1 = 'Нет миниатюры';
							$deleteThumb_2 = $urlThumb;
						}
					}
					$sqlDelete = "DELETE FROM gi_wardrobe_visualisation WHERE id = '$deleteImage'";
					$resultDelete = $wpdb->query($sqlDelete);
				}
			}
			$resultUpdate = '';
			$resultUpdateNew = 'Тут будет ошибка';
			if (!empty($_POST['nameVisualisation']) && is_array($_POST['nameVisualisation'])) {
				foreach ($_POST['nameVisualisation'] as $idVisualisation => $nameVisualisation) {
					$sqlUpdate = "UPDATE gi_wardrobe_visualisation SET name_visual = '$nameVisualisation' WHERE id = '$idVisualisation'";
					$resultUpdate = $wpdb->query($sqlUpdate);
					// if (!$resultUpdateNew){
					// 	$error = $wpdb->last_error;
					// 	$resultUpdate [] = $error;
					// }
				}
			}
			//РЕДАКТИРОВАНИЕ РАНГА ГАЛЕРЕИ
			if (!empty($_POST['rankVisualisation']) && is_array($_POST['rankVisualisation'])) {
				foreach ($_POST['rankVisualisation'] as $idVisualisation => $rankVisualisation) {
					$sqlUpdate = "UPDATE gi_wardrobe_visualisation SET rank_visual = '$rankVisualisation' WHERE id = '$idVisualisation'";
					$resultUpdate = $wpdb->query($sqlUpdate);
					$resultUpdateNew = $sqlUpdate;
					if (!$resultUpdate) {
						$error = $wpdb->last_error;
						$resultUpdate = $error;
					}
				}
			}

			$resultVisualisation = addGalleryWardrobe($_FILES['new_gallery'], $name_eng, $newid);

			// if (!empty($_FILES['new_gallery']) and $_FILES['new_gallery'] == TRUE) {
			// 	foreach ($_FILES['new_gallery']['name'] as $numberAddFile => $nameAddFile) {
			// 		$$numberAddFile = $nameAddFile; //надо 2 знака $
			// 	}

			// 	for ($i = 0; $i <= $numberAddFile; $i++) {
			// 		if (!empty($_FILES['new_gallery']['name'][$i])) {
			// 			print_r($_FILES['new_gallery']);

			// 			$dirThumb = $folder . "gallery/thumbs/";
			// 			$dirImage = $folder . "gallery/images/";

			// 			$imagelink = $dirImage . $_FILES['new_gallery']['name'][$i];
			// 			$thumblink = $dirThumb . $_FILES['new_gallery']['name'][$i];
			// 			$imagelinkrus = $dirImage . $_FILES['new_gallery']['name'][$i];

			// 			//$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
			// 			//$thumblink = str_replace($rus, $lat, $thumblink);
			// 			$tmp = $_FILES['new_gallery']['tmp_name'][$i];
			// 			$path_parts = pathinfo($_FILES['new_gallery']['name'][$i]);
			// 			$extension = $path_parts['extension'];
			// 			//так надо :)
			// 			$hrefimage = ".." . $imagelink;
			// 			$hrefthumb = ".." . $thumblink;
			// 			if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
			// 				move_uploaded_file($tmp, $hrefimage);
			// 			} else {
			// 				//echo "Вы попытались загрузить неподходящее изображение<br>";
			// 				continue;
			// 			}
			// 			//задаем размеры миниатюрам
			// 			list($width, $height) = getimagesize($hrefimage);
			// 			$otnosh = $width / $height;
			// 			$newWidth = 200;
			// 			$newHeight = $newWidth / $otnosh;
			// 			$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
			// 			switch ($extension) {
			// 				case "jpg":	//СОЗДАНИЕ JPG
			// 					$image = imagecreatefromjpeg($hrefimage); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
			// 					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
			// 					imagejpeg($image_p, $hrefthumb, 100);
			// 					break;
			// 				case "jpeg": //СОЗДАНИЕ JPG
			// 					$image = imagecreatefromjpeg($hrefimage);
			// 					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			// 					imagejpeg($image_p, $hrefthumb, 100);
			// 					break;
			// 				case "png":	//СОЗДАНИЕ ПНГ
			// 					$image = imagecreatefrompng($hrefimage);
			// 					imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
			// 					imagesavealpha($image_p, true); //Включаем сохранение альфа канала
			// 					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			// 					imagepng($image_p, $hrefthumb);
			// 			}
			// 		}
			// 	}
			// }

			$sqlRedact = "UPDATE `gi_wardrobe` SET video='$newVideo', style='$newstyles', avatar = '$new_avatarlink', name = '$newName', additional_name='$newAdditionalName', bind_to='$newBindTo', description='$newDescr', material='$newmaterials', wood='$newWood', plastic='$newPlastic', furnitura='$newFurnitura', basic_cost='$newCost', fittings='$newFittings', handles='$newhandles', similar='$newSimilar' WHERE newid='$wardrobe_id'";
			echo "<br>Изменения в гардероб $name внесены! <br>";
			echo $sqlRedact;
			$goRedact = $wpdb->query($sqlRedact);

			echo "<script>window.location.reload();</script>";
		}
	}
	$c .= "</div>";
	echo $c;
	echo $d;
}



function add_fittings()
{
	echo "<h1>Добавить наполнение:</h1>";
	$phpself = $_SERVER['PHP_SELF'];
	$rus = array(',', '(', ')', ' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
	$lat = array('', '', '', '_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');
	global $wpdb;
	?>
	<form method='POST' enctype='multipart/form-data'>
		Введите название наполнения (номер):<br>
		<input name='fittingName' required><br>
		Загрузите фото наполнения:<br>
		<input type='file' name='fittingImage' accept="image/*"><br><br>
		<input type='submit' name='load'>
	</form>
	<?php
	//ЗАГРУЗКА КАРТИНОК + СОЗДАНИЕ МИНИАТЮР
	if (!empty($_POST['load'])) {
		if (!empty($_FILES['fittingImage']) and $_FILES['fittingImage']['name'] == true) {
			$imagename = $_FILES['fittingImage']['name'];
			$imagename = str_replace($rus, $lat, $imagename);

			// Create directories if they don't exist
			$baseDir = "../wp-content/plugins/bp_wardrobe_add/images/fittings/";
			$baseImagesDir = $baseDir . "base_images/";
			$thumbsDir = $baseDir . "thumbs/";

			if (!file_exists($baseDir)) {
				mkdir($baseDir, 0755, true);
			}
			if (!file_exists($baseImagesDir)) {
				mkdir($baseImagesDir, 0755, true);
			}
			if (!file_exists($thumbsDir)) {
				mkdir($thumbsDir, 0755, true);
			}

			$linkImage = "/wp-content/plugins/bp_wardrobe_add/images/fittings/base_images/$imagename";
			$linkThumbs = "/wp-content/plugins/bp_wardrobe_add/images/fittings/thumbs/$imagename";
			$tmp = $_FILES['fittingImage']['tmp_name'];
			$path_parts = pathinfo($_FILES['fittingImage']['name']);
			$extension = $path_parts['extension'];
			if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
				$uploadPath = "../wp-content/plugins/bp_wardrobe_add/images/fittings/base_images/$imagename";
				if (move_uploaded_file($tmp, $uploadPath)) {
					$hrefimage = "../wp-content/plugins/bp_wardrobe_add/images/fittings/base_images/$imagename";
					$hrefthumb = "../wp-content/plugins/bp_wardrobe_add/images/fittings/thumbs/$imagename";

					//задаем размеры миниатюрам
					$imageInfo = getimagesize($hrefimage);
					if ($imageInfo !== false) {
						list($width, $height) = $imageInfo;
						if ($height > 0) { // Prevent division by zero
							$otnosh = $width / $height;
							$newWidth = 130;
							$newHeight = $newWidth / $otnosh;
							$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
							switch ($extension) {
								case "jpg":	//СОЗДАНИЕ JPG
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

							$fittingName = $_POST['fittingName'];
							$fittingName_eng = str_replace($rus, $lat, $fittingName);
							$timestamp = time();
							$sql = "INSERT INTO `gi_wardrobe_fittings` (`model_name`, `model_name_eng`, `image_href`, `thumb_href`, `timestamp`) VALUES ('$fittingName', '$fittingName_eng', '$linkImage', '$linkThumbs', '$timestamp');";
							$result = $wpdb->get_results($sql);

							echo "Данные успешно загружены";
							echo "<script>window.location.reload();</script>";
						} else {
							echo "Ошибка: неверные размеры изображения<br>";
						}
					} else {
						echo "Ошибка: не удалось получить информацию об изображении<br>";
					}
				} else {
					echo "Ошибка: не удалось загрузить файл<br>";
				}
			} else {
				echo "Вы попытались загрузить неподходящее изображение<br>";
			}
		} else {
			echo "Не загружено изображение";
		}
	}
	echo "<br><br><h2>Наполнение в наличии:</h2>";
	$sqlEcho = "SELECT `model_name`, `image_href`, `thumb_href`, `newid` FROM `gi_wardrobe_fittings` ORDER BY `fitting_rank` ASC";
	$resultEcho = $wpdb->get_results($sqlEcho);
	$b = "<form method='POST'>";
	$b .= "<div style='width:100%; text-align:center; position:relative;'>";
	foreach ($resultEcho as $rowEcho) {
		$modelname = $rowEcho->model_name;
		$imagehref = $rowEcho->image_href;
		$thumbhref = $rowEcho->thumb_href;
		$newid = $rowEcho->newid;
		$b .= "
			<div class='kitchen_point' style='margin:5px; display:inline-block; width:130px; position:relative;'>
			<input type='checkbox' name='$newid' value='$newid' style='position:absolute; top:5px; left:5px;'/>
			<img src='$imagehref' style='max-width:100%;'/>
			<h2>$modelname</h2>
			</div>
			";

		if (isset($_POST['delete_fittings']) and isset($_POST[$newid])) {
			$fittingForDelete = $_POST[$newid];
			$image_delete = ".." . $imagehref;
			$thumb_delete = ".." . $thumbhref;
			//Заменяем в таблице наполнение гардеробных (вырезаем удаленные)
			$sqlOldFittings = "SELECT fittings FROM gi_wardrobe WHERE fittings LIKE '%$modelname%'";
			$resultOldFittings = $wpdb->get_results($sqlOldFittings);
			foreach ($resultOldFittings as $rowOldFittings) {
				$oldFittings = $rowOldFittings->fittings;
				$handle_name_1 = $modelname . ",";
				$handle_name_2 = $modelname;
				$newHandles = str_replace($handle_name_1, "", $oldFittings);
				$newHandles = str_replace($handle_name_2, "", $newHandles);
				$sqlNewHandles = "UPDATE gi_wardrobe SET fittings = '$newHandles' WHERE fittings = '$oldFittings'";
				$resultNewHandles = $wpdb->get_results($sqlNewHandles);
			}

			//УДАЛЯЕМ
			echo "Выбранные наполнения удалены";
			echo "<script>window.location.reload();</script>";
			$sqlDeleteFitting = "DELETE FROM gi_wardrobe_fittings WHERE newid='$fittingForDelete'";
			$resultDeleteFitting = $wpdb->get_results($sqlDeleteFitting);
			unlink($image_delete);
			unlink($thumb_delete);
		}

	}
	$b .= "
		</div>
		<input type='submit' value='Удалить' name='delete_fittings'/>
		</form>
		";
	echo $b;
}
function redact_fittings()
{
	function fittingsOutputAdmin($sql, $wpdb)
	{
		$resultEcho = $wpdb->get_results($sql);
		$post = "<div style='width:100%; text-align:center; position:relative;'>";
		foreach ($resultEcho as $rowEcho) {
			$modelname = $rowEcho->model_name;
			$imagehref = $rowEcho->image_href;
			$thumbhref = $rowEcho->thumb_href;
			$rank = $rowEcho->fitting_rank;
			$newid = $rowEcho->newid;
			$post .= "";

			$post .= "
			<div class='kitchen_point' style='margin:5px; display:inline-block; width:230px; position:relative;'>
			<form method='POST'>
				<input type='checkbox' name='$newid' value='$newid' checked style='position:absolute; top:5px; left:5px;'>
				<img src='$imagehref' style='max-width:100%;'/>
				<input type='' name='rank' value='$rank' style='top:5px; left:5px;'/>
				<h2 style='top:5px; left:5px;'>$modelname </h2>
			<input type='submit' value='Редактировать' name='updateHandels'/>
			</div>
			</form>
			";
			if (isset($_POST['updateHandels'])) {
				$handleForUpdate = $_POST[$newid];
				$newRank = $_POST['rank'];

				$sqlUpdateHandle = "UPDATE gi_wardrobe_fittings SET fitting_rank = '$newRank' WHERE newid='$handleForUpdate'";
				$resultUpdateHandle = $wpdb->get_results($sqlUpdateHandle);
				echo "Наполнение обновлено";
				echo "<script>window.location.reload();</script>";
			}
		}

		$post .= "
		</div>
		";
		return $post;
	}

	global $wpdb;
	// Запрос для записей без "Gola" в названии
	$sql = "SELECT `model_name`, `image_href`, `thumb_href`, `newid`, `fitting_rank` FROM `gi_wardrobe_fittings` ORDER BY `fitting_rank` ASC";

	// Запрос для записей с "Gola" в названии

	echo "<h1>Редактировать наполнения:</h1>";

	$b = '';
	if ($sql) {
		$b .= "<br><br><h2>Наполнение в наличии:</h2>";
		$b .= fittingsOutputAdmin($sql, $wpdb);
	}

	echo $b;
}

function addGalleryWardrobe($filesArray, $name_kitchen_eng, $kitchenId = null)
{
	global $wpdb;

	if (!empty($filesArray) and $filesArray['name'] == true && is_array($filesArray)) {
		foreach ($filesArray['name'] as $numberAddFile => $nameAddFile) {
			$$numberAddFile = $nameAddFile; //надо 2 знака $
		}
		$resultVisualisation = [];
		for ($i = 0; $i <= $numberAddFile; $i++) {
			$fileName = basename($filesArray['name'][$i]);
			$imagelink = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_kitchen_eng/gallery/images/" . $fileName;
			$thumblink = "/wp-content/plugins/bp_wardrobe_add/images/wardrobe/$name_kitchen_eng/gallery/thumbs/" . $fileName;
			$imagelink = replaceSymbols($imagelink);//Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
			$thumblink = replaceSymbols($thumblink);
			$tmp = $filesArray['tmp_name'][$i];
			$path_parts = pathinfo($filesArray['name'][$i]);
			$extension = $path_parts['extension'];
			//так надо :)
			echo $imagelink;
			$hrefimage = ".." . $imagelink;
			$hrefthumb = ".." . $thumblink;
			if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
				$sql = "INSERT INTO `gi_wardrobe_visualisation` (`id_wardrobe`, `name_visual`, `url_image`, `url_thumb`, `rank_visual`) VALUES ('$kitchenId', '$fileName', '$imagelink', '$thumblink', '$i')";
				$resultVisualisation[$i] = $wpdb->query($sql);
				if ($resultVisualisation[$i]) {
					// echo "Фотография $fileName загружена в базу данных";
				} else {
					$resultVisualisation[$i] = $wpdb->last_error;
					// echo "Ошибка загрузки фотографии $fileName в базу данных";
				}
				move_uploaded_file($tmp, $hrefimage);
			}
			//задаем размеры миниатюрам
			if ($hrefimage) {
				list($width, $height) = getimagesize($hrefimage);
			}
			// list($width, $height) = getimagesize($hrefimage);
			$otnosh = $width && $height ? $width / $height : 1;
			$newWidth = 200;
			$newHeight = $newWidth / $otnosh;
			$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
			switch ($extension) {
				case "jpg":	//СОЗДАНИЕ JPG
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
		}
		return $resultVisualisation;
	}
}
?>