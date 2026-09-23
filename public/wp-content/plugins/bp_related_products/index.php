<?php
/*Plugin Name: bp_related_products
Description: Сопутствующие товары: столы, стулья, Гостинные.
Version: 1.0
Author: Business Park*/
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;

add_shortcode('commode_print', 'commode_print');
add_shortcode('some_commode_print', 'some_commode_print');

add_action('admin_menu', 'add_new_page');
function add_new_page()
{
	add_menu_page('Добавить Гостинную', 'Гостинные', 8, __FILE__, 'add_commode', 'dashicons-grid-view');
	add_submenu_page(__FILE__, 'Редактировать', 'Редактировать', 8, 'redact_commode', 'redact_commode');

}


function add_commode()
{
	echo "<h1>Добавить Гостинную</h1>";
	global $wpdb;
	$rus = array('"', '(', ')', ' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
	$lat = array('', '', '', '-', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');
	$a = "
	<form method='POST' enctype='multipart/form-data' id='add_commode_form'>
		Информация о материале, фасадах, ручках и пр. будет взята из кухни.<br><br>
		Название Гостинной <br>
		<div id='ajaxcontent'></div>
		<input name='commode_name' placeholder='Имя, из которого генерится УРЛ!' id='check_alias'/><br><br>
		<select name='kitchen'>
			<option selected disabled>Выберите название кухни:</option>
	";
	$sqlKitchen = "SELECT * FROM gi_kitchen";
	$resultKitchen = $wpdb->get_results($sqlKitchen);
	foreach ($resultKitchen as $rowKitchen) {
		$kitchen = $rowKitchen->name;
		$kitchen_eng = $rowKitchen->name_eng;
		$a .= "
		<option value='$kitchen'>$kitchen</option>
		";
	}
	$a .= "</select>";
	$a .= "<br>
	Введите описание Гостинной:<br>
	<textarea name='description'></textarea><br>
	Введите скидку Гостинной<br>
	<input name='sales'><br><br>
	Добавить обложку Гостинной:<br>
	<input type='file' name='avatar' required><br><br>
	Добавить фотографии в галерею Гостинной:<br>
	<input type='file' name='gallery[]' multiple required><br><br>
	";

	if (!empty($_POST['kitchen']) and !empty($_POST['description'])) {
		$commode_name = $_POST['commode_name'];
		$kitchen = $_POST['kitchen'];
		$description = $_POST['description'];
		$sales = $_POST['sales'];
		$alias = "living-" . str_replace($rus, $lat, $commode_name);
		$alias = str_replace('---', '-', $alias);
		$alias = str_replace('-', '-', $alias);
		$commode_name_eng = str_replace($rus, $lat, $commode_name);
		$commode_name_eng = str_replace('-', '_', $commode_name_eng);
		$commode_name_eng = str_replace('___', '_', $commode_name_eng);
		$commode_name_eng = str_replace('__', '_', $commode_name_eng);
		$folder = "/wp-content/plugins/bp_related_products/images/commode/gallery/$commode_name_eng/";
		//СОЗДАЕМ ПАПКИ ДЛЯ ГАЛЕРЕИ
		$images_dir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_related_products/images/commode/gallery/$commode_name_eng/";
		if (!file_exists($images_dir)) {
			mkdir($images_dir);
			mkdir("$images_dir/images/");
			mkdir("$images_dir/thumbs/");
		} else {
			echo "Папка с таким нахванием уже существует! Грузим фото в нее!";
			//die;
		}
		//ЗАГРУЗКА ГАЛЕРЕИ

		// if (!empty($_FILES['gallery']) and $_FILES['gallery']['name'] == true) {
		// 	foreach ($_FILES['gallery']['name'] as $numberAddFile => $nameAddFile) {
		// 		$$numberAddFile = $nameAddFile; //надо 2 знака $
		// 	}
		// 	for ($i = 0; $i <= $numberAddFile; $i++) {
		// 		$imagelink = "/wp-content/plugins/bp_related_products/images/commode/gallery/$commode_name_eng/images/" . $_FILES['gallery']['name'][$i];
		// 		$thumblink = "/wp-content/plugins/bp_related_products/images/commode/gallery/$commode_name_eng/thumbs/" . $_FILES['gallery']['name'][$i];
		// 		$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
		// 		$thumblink = str_replace($rus, $lat, $thumblink);
		// 		$tmp = $_FILES['gallery']['tmp_name'][$i];
		// 		$path_parts = pathinfo($_FILES['gallery']['name'][$i]);
		// 		$extension = $path_parts['extension'];
		// 		//так надо :)
		// 		$hrefimage = ".." . $imagelink;
		// 		$hrefthumb = ".." . $thumblink;
		// 		if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
		// 			move_uploaded_file($tmp, $hrefimage);
		// 		} else {
		// 			echo "Вы попытались загрузить неподходящее изображение<br>";
		// 			continue;
		// 		}
		// 		//задаем размеры миниатюрам
		// 		list($width, $height) = getimagesize($hrefimage);
		// 		$otnosh = $width / $height;
		// 		$newWidth = 240;
		// 		$newHeight = $newWidth / $otnosh;
		// 		$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
		// 		switch ($extension) {
		// 			case "jpg":	//СОЗДАНИЕ JPG
		// 				$image = imagecreatefromjpeg($hrefimage); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
		// 				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
		// 				imagejpeg($image_p, $hrefthumb, 100);
		// 				break;
		// 			case "jpeg": //СОЗДАНИЕ JPG
		// 				$image = imagecreatefromjpeg($hrefimage);
		// 				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		// 				imagejpeg($image_p, $hrefthumb, 100);
		// 				break;
		// 			case "png":	//СОЗДАНИЕ ПНГ
		// 				$image = imagecreatefrompng($hrefimage);
		// 				imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
		// 				imagesavealpha($image_p, true); //Включаем сохранение альфа канала
		// 				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		// 				imagepng($image_p, $hrefthumb);
		// 		}
		// 	}
		// 	//echo "Фотографии загружены";
		// }
		//ЗАГРУЗКА ОБЛОЖКИ
		if (!empty($_FILES['avatar']) and $_FILES['avatar']['name'] == true) {
			$imagename = $_FILES['avatar']['name'];
			$imagename = str_replace($rus, $lat, $imagename);
			$linkImage = "/wp-content/plugins/bp_related_products/images/commode/avatar/fullsize/$imagename";
			$linkThumbs = "/wp-content/plugins/bp_related_products/images/commode/avatar/thumb/$imagename";
			$tmp = $_FILES['avatar']['tmp_name'];
			$path_parts = pathinfo($_FILES['avatar']['name']);
			$extension = $path_parts['extension'];
			if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
				$absPath = $_SERVER['DOCUMENT_ROOT'] . $linkImage;
				$absThumbPath = $_SERVER['DOCUMENT_ROOT'] . $linkThumbs;

				// Ensure directories exist
				$dir = dirname($absPath);
				if (!file_exists($dir)) {
					mkdir($dir, 0777, true);
				}
				$thumbDir = dirname($absThumbPath);
				if (!file_exists($thumbDir)) {
					mkdir($thumbDir, 0777, true);
				}

				if (move_uploaded_file($tmp, $absPath)) {
					$hrefimage = $absPath;
					$hrefthumb = $absThumbPath;
					//задаем размеры миниатюрам
					$size = getimagesize($hrefimage);
					if ($size) {
						list($width, $height) = $size;
						$otnosh = $height > 0 ? $width / $height : 1;
						$newWidth = 600;
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
				}
			}


			//СОЗДАЕМ СТРАНИЧКУ В WP
			$post_title = $commode_name;

			$strannie_bukvi = array("Ą", "ą", "Ć", "ć", "Ę", "ę", "Ł", "ł", "Ń", "ń", "Ó", "ó", "Ś", "ś", "Ź", "ź", "Ż", "ż");
			$normalnie_bukvi = array("A", "a", "C", "c", "E", "e", "L", "l", "N", "n", "O", "o", "S", "s", "Z", "z", "Z", "z");

			$alias = mb_strtolower(str_replace($strannie_bukvi, $normalnie_bukvi, $alias));

			$post_content = "<!-- wp:paragraph --><p>[some_commode_print alias='$alias']</p><!-- /wp:paragraph -->";
			$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
			$lat = array('-', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');
			//$post_name_eng = str_replace($rus, $lat, $post_title);
			$sql_page = "SELECT DISTINCT post_title FROM wp_posts WHERE post_title = '$post_title' AND post_parent = 44";
			$result_page = $wpdb->get_results($sql_page);
			$count_pages = count($result_page);



			if ($count_pages == 0) {
				// Создаем массив данных новой записи
				$post_data = array(
					'post_title' => $post_title,
					'post_content' => $post_content,
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type' => 'page',
					'post_name' => $alias,
					'ping_status' => 'closed',
					'comment_status' => 'closed',
					'post_parent' => 44,
				);
				// Вставляем запись в базу данных
				$post_id = wp_insert_post($post_data);
				echo "Ливинг добавлен!";
				echo "<br> Не заубдьте заполнить метатеги!";
				//echo "<script>window.location.reload();</script>";
				$sql = "INSERT INTO `gi_commode` (`name`, `name_eng`, `alias`, `kitchen`, `description`, `avatar`, `avatar_thumb`, `folder`, `sales`)
								VALUES ('$commode_name', '$commode_name_eng', '$alias', '$kitchen', '$description', '$linkImage', '$linkThumbs', '$folder', '$sales')";
				$result = $wpdb->get_results($sql);
				$commodeId = $wpdb->insert_id;
				addGalleryCommode($_FILES['gallery'], $commode_name_eng, $commodeId);
			} else {
				echo "Найден дубль в базе данных Гостинных. Гостинная не добавлена. Совпадение в названии Гостинной";
			}
		}
	}
	$a .= "
	<input type='submit' class='submit_btn' value='Добавить'/>
	</form>
	<style>
	#add_commode_form input, #add_commode_form select {width:300px; height:40px; padding:4px 8px; outline:none; border:1px solid #dcdcdc;}
	#add_commode_form textarea {width:300px; height:100px; padding:4px 8px; outline:none; border:1px solid #dcdcdc;}
	.submit_btn{background:#9BE86D;}
	</style>
	
	<script>
	jQuery('#check_alias').keyup(function(obj){
		jQuery.ajax({  
			type: 'POST',
			url: '/wp-content/plugins/bp_related_products/inc/check_commode_uniq.php',  
			cache: false, 
			data: {commode_name: this.value},
			success: function(html){  
				jQuery('#ajaxcontent').html(html);  
			}  
		});
	});
	</script>
	";
	echo $a;
}

function redact_commode_old()
{
	global $wpdb;
	$a = "<div style='width:calc(100% - 75px); text-align:center; padding:25px; margin:25px 25px 0 0; background:white;'>";
	$sql = "SELECT * FROM gi_commode";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$newid = $row->newid;
		$name = $row->name;
		$description = $row->description;
		$cost = $row->cost;
		$avatar = $row->avatar;
		$avatar_thumb = $row->avatar_thumb;
		$folder = $row->folder;
		$sales = $row->sales;
		$a .= "<div style='width:100%; min-height:100px; background:#f9f9f9; border:1px solid #dcdcdc;'>
			<div style='width:400px; max-width:100%; height:max-content; float:left;'>
				<img src='$avatar' style='max-width:100%;'/>
			</div>
			<div style='float:left; width:calc(100% - 450px); min-height:100px; text-align:left; padding:10px 25px; position:relative;'>
				<form method='POST'>
					<b>НАЗВАНИЕ:</b> <br>
					<textarea name='name' style='width:100%; height:30px;'>$name</textarea><br>
					<b>ОПИСАНИЕ:</b> <br>
					<textarea name='descr' style='width:100%; height:30px;'>$description</textarea><br>
					<b>ЦЕНА:</b> <br>
					<textarea name='cost' style='width:100%; height:30px;'>$cost</textarea><br>
					<b>СКИДКА:</b> <br>
					<textarea name='sales' style='width:100%; height:30px;'>$sales</textarea><br><br>
					<button type='submit' name='moderate' value='$newid' style='background:green; color:white; border:none; padding:7px 10px; cursor:pointer;'>ВНЕСТИ ПРАВКИ</button>
					<button name='deleteCommode' value='$newid' style='position:absolute; right:25px; bottom:10px; margin-left:10px; background:darkred; color:white; border:none; padding:7px 10px; cursor:pointer;'>УДАЛИТЬ ГОСТИННУЮ</button>
				</form>
				
			</div>

			<div style='clear:both'></div>
		</div><br><br>";

	}
	$a .= "</div>";
	$a .= "
	<script>
		function razrabotka(){
			alert('Данная функция в разработке.');
		}
	</script>
	";
	echo $a;
	if (isset($_POST['moderate'])) {
		$moderateid = $_POST['moderate'];
		$newname = $_POST['name'];
		$newdescr = $_POST['descr'];
		$newcost = $_POST['cost'];
		$newsales = $_POST['sales'];
		$sqlupdate = "UPDATE gi_commode SET name = '$newname', description='$newdescr', cost='$newcost', sales='$newsales' WHERE newid='$moderateid'";
		$resultupdate = $wpdb->get_results($sqlupdate);
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['deleteCommode'])) {
		$deleteId = $_POST['deleteCommode'];
		$sqlUpdate = "DELETE FROM gi_commode WHERE newid = '$deleteId'";
		$resultUpdate = $wpdb->get_results($sqlUpdate);
		echo "<script>window.location.reload();</script>";
	}
}

function redact_commode()
{
	global $wpdb;
	$sql = "SELECT * FROM gi_commode";
	$result = $wpdb->get_results($sql);
	$first_item = $result[0];
	?>
	<div class="header">
		<h1>Редактировать Гостинные</h1>
	</div>
	<div class="names_items">
		<?php
		$i = 0;
		foreach ($result as $row) {
			$class = $i === 0 ? 'active' : '';
			?>

			<button class="button_item <?php echo $class; ?>" value="<?php echo $row->newid; ?>">
				<?php echo $row->name; ?>
			</button>
			<?php $i++;
		} ?>
	</div>
	<div class="content_items">
		<?php
		$newid = $first_item->newid;
		$name = $first_item->name;
		$commode_name_eng = $first_item->name_eng;
		$description = $first_item->description;
		$cost = $first_item->cost;
		$avatar = $first_item->avatar;
		$avatar_thumb = $first_item->avatar_thumb;
		$folder = $first_item->folder;
		$sales = $first_item->sales;
		$sql_visualisation = "SELECT * FROM gi_commode_visualisation WHERE id_commode = '$newid'";
		$result_visualisation = $wpdb->get_results($sql_visualisation);
		?>
		<div class='content_item'>
			<form method='POST' enctype='multipart/form-data'>
				<div class='image'>
					<div class="delete_image">
						<div class="cross"></div>
					</div>
					<img src='<?php echo $avatar; ?>' style='max-width:100%;' />
				</div>
				<div class='content_item_form'>
					<label for='name'>НАЗВАНИЕ:</label>
					<textarea name='name' style='width:100%; height:30px;'><?php echo $name; ?></textarea>
					<label for='descr'>ОПИСАНИЕ:</label>
					<textarea name='descr' style='width:100%; height:30px;'><?php echo $description; ?></textarea>
					<label for='cost'>ЦЕНА:</label>
					<textarea name='cost' style='width:100%; height:30px;'><?php echo $cost; ?></textarea>
					<label for='sales'>СКИДКА:</label>
					<textarea name='sales' style='width:100%; height:30px;'><?php echo $sales; ?></textarea>
					<input type='hidden' name='commodeNameEng' value='<?php echo $commode_name_eng; ?>' />
					<input type='hidden' name='id' value='<?php echo $newid; ?>' />
				</div>
				<div class='files'>
					<?php
					foreach ($result_visualisation as $row_visualisation) {
						$visual_name = $row_visualisation->name_visual;
						$visual_url_image = $row_visualisation->url_image;
						$visual_url_thumb = $row_visualisation->url_thumb;
						$visual_id = $row_visualisation->id;
						$visual_rank = $row_visualisation->rank_visual;
						?>
						<div class='file'>
							<img src='<?php echo $visual_url_thumb; ?>' style='max-width:100%;' />
							<input class='checkbox' type='checkbox' name='deleteGallery[]' value='<?php echo $visual_id; ?>' />
							<input type='text' name='nameVisualisation[<?php echo $visual_id; ?>]' value='<?php echo $visual_name; ?>' />
							<input type='text' name='rankVisualisation[<?php echo $visual_id; ?>]' value='<?php echo $visual_rank; ?>' />
						</div>
						<?php
					}
					?>
				</div>
				<div class='upload_gallery'>
					<h2>Добавить новые фотографии в галерею:</h2>
					<input type='file' name='new_gallery[]' multiple />
				</div>
				<div class='buttons'>
					<button type='submit' name='moderate' class='submit' value='<?php echo $newid; ?>'>ВНЕСТИ ПРАВКИ</button><button
						name='deleteCommode' class='deleteCommode' value='<?php echo $newid; ?>'>УДАЛИТЬ Гостинную</button>
				</div>
			</form>
			<div style='clear:both'></div>
		</div>
		<script src='/wp-content/plugins/bp_contracts/js/utility.js'></script>
		<script src='/wp-content/plugins/bp_related_products/js/redact.js'></script>
		<link rel='stylesheet' href='/wp-content/plugins/bp_related_products/css/style.css'>
		<?php
	// if (isset($_POST['moderate'])) {
	// 	$moderateid = $_POST['moderate'];
	// 	$newname = $_POST['name'];
	// 	$newdescr = $_POST['descr'];
	// 	$newcost = $_POST['cost'];
	// 	$newsales = $_POST['sales'];
	// 	$deleteGallery = $_POST['deleteGallery'] ?? [];
	// 	$newFiles = $_FILES['newFiles'] ?? [];
	// 	$newFile = addNewFiles($newFiles, $commode_name_eng, $moderateid);
	// 	print_r($newFile);
	// 	$newFileThumb = str_replace('fullsize', 'thumb', $newFile);

	// 	$sqlupdate = "UPDATE gi_commode SET name = '$newname', description='$newdescr', cost='$newcost', sales='$newsales', avatar='$newFile', avatar_thumb='$newFileThumb' WHERE newid='$moderateid'";
	// 	$resultupdate = $wpdb->get_results($sqlupdate);
	// 	if (!empty($deleteGallery) && is_array($deleteGallery)) {
	// 		foreach ($deleteGallery as $idVisualisation) {
	// 			$sqlSelect = "SELECT * FROM gi_commode_visualisation WHERE id = '$idVisualisation'";
	// 			$resultSelect = $wpdb->get_results($sqlSelect);
	// 			foreach ($resultSelect as $rowSelect) {
	// 				$urlImage = $_SERVER['DOCUMENT_ROOT'] . $rowSelect->url_image;
	// 				$urlThumb = $_SERVER['DOCUMENT_ROOT'] . $rowSelect->url_thumb;
	// 				if (file_exists($urlImage)) {
	// 					// echo $urlImage;
	// 					unlink($urlImage);
	// 				}
	// 				if (file_exists($urlThumb)) {
	// 					// echo $urlThumb;
	// 					unlink($urlThumb);
	// 				}
	// 			}
	// 			$sqlDelete = "DELETE FROM gi_commode_visualisation WHERE id = '$idVisualisation'";
	// 			$resultDelete = $wpdb->query($sqlDelete);
	// 		}
	// 	}
	// 	if (!empty($_POST['nameVisualisation']) && is_array($_POST['nameVisualisation'])) {
	// 		foreach ($_POST['nameVisualisation'] as $idVisualisation => $nameVisualisation) {
	// 			$sqlUpdate = "UPDATE gi_commode_visualisation SET name_visual = '$nameVisualisation' WHERE id = '$idVisualisation'";
	// 			$resultUpdate = $wpdb->query($sqlUpdate);
	// 		}
	// 	}
	// 	if (!empty($_POST['rankVisualisation']) && is_array($_POST['rankVisualisation'])) {
	// 		foreach ($_POST['rankVisualisation'] as $idVisualisation => $rankVisualisation) {
	// 			$sqlUpdate = "UPDATE gi_commode_visualisation SET rank_visual = '$rankVisualisation' WHERE id = '$idVisualisation'";
	// 			$resultUpdate = $wpdb->query($sqlUpdate);
	// 		}
	// 	}
	// 	// print_r($_POST['new_gallery']);
	// 	// print_r($_FILES);
	// 	if (!empty($_FILES['new_gallery']) && is_array($_FILES['new_gallery'])) {
	// 		addGalleryCommode($_FILES['new_gallery'], $name, $moderateid);
	// 	}
	// 	// echo "<script>window.location.reload();</script>";
	// }
	// if (isset($_POST['deleteCommode'])) {
	// 	$deleteId = $_POST['deleteCommode'];
	// 	$sqlSelect = "SELECT * FROM gi_commode WHERE newid = '$deleteId'";
	// 	$sqlUpdate = "DELETE FROM gi_commode WHERE newid = '$deleteId'";
	// 	$resultUpdate = $wpdb->get_results($sqlUpdate);
	// 	echo "<script>window.location.reload();</script>";
	// }
}
function commode_print()
{

	wp_enqueue_style('lightbox', '/wp-content/plugins/bp_kitchen_print/assets/lightbox.css');
	wp_enqueue_script('lightbox', '/wp-content/plugins/bp_kitchen_print/assets/lightbox-2.6.min-2.js', array(), '1.0.0', true);

	global $wpdb;
	$i = 1; // Каждая картинка в новом лайтбоксе
	$a = "
	<div class='commode_grid'>
	";
	$sql = "SELECT * FROM gi_commode WHERE alias != 'living-damiana-komody' ORDER BY `rank`";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$name = $row->name;
		$alias = $row->alias;
		$name_eng = $row->name_eng;
		$description = $row->description;
		$cost = $row->cost;
		$avaimage = $row->avatar;
		$avathumb = $row->avatar_thumb;
		$sales = $row->sales;
		$folder = $row->folder;

		$a .= "
		<div class='commode_item'>
			<!---<div class='salesDiv'> - $sales% </div>--->
			<a href='/komody/$alias/'>
				<div class='commode_avatar' style='background:url(..$avathumb) no-repeat; background-position:center; background-size:cover;'></div>
			</a>
			<div class='commode_name'>$name</div>
		</div>
		";
		$i++;
	}

	$a .= "</div>";
	return $a;
	//echo "da";


}

function some_commode_print($atts)
{
	extract(shortcode_atts(array(
		'alias' => ""
	), $atts));

	/** @var Container */
	global $servicesContainer;
	/** @var DBWorker */
	$dbWorker = $servicesContainer->get('DBWorker');
	/** @var DBUtilities */
	$dbUtilities = $servicesContainer->get('DBUtilities');
	$version = setVSite();

	// Get commode data using DBWorker
	$result = $dbWorker->selectSimple('gi_commode', 'alias', $alias);
	if (empty($result)) {
		return '';
	}

	$newid = $result['newid'];
	$name = $result['name'];
	$kitchen = $result['kitchen'];
	$descr = isset($result['description']) ? $result['description'] : '';

	// Get linked kitchen info using DBWorker
	$kitchenInfo = $dbWorker->selectSimple('gi_kitchen', 'name', $kitchen);

	// Prepare material string from kitchen
	$material_arr = isset($kitchenInfo['material']) ? explode(';', $kitchenInfo['material']) : [];
	$material_arr = array_diff($material_arr, array(''));
	$material = implode(', ', $material_arr);

	$style = isset($kitchenInfo['style']) ? $kitchenInfo['style'] : '';
	$type = isset($kitchenInfo['type']) ? $kitchenInfo['type'] : '';
	$wood = isset($kitchenInfo['wood']) ? $kitchenInfo['wood'] : '';
	$plastic = isset($kitchenInfo['plastic']) ? $kitchenInfo['plastic'] : '';

	ob_start();
	?>

	<script src='/wp-content/plugins/bp_kitchen_print/js/lozad.min.js'></script>

	<script>
		let kitchenName = "<?php echo $name; ?>";
		let kitchenType = "commode";
		console.log(kitchenName);
	</script>
	<div class='kitchen_card_slider' style='position:relative;'>
		<div class='fotorama' data-allowfullscreen='native' data-keyboard='true' data-nav='thumbs' data-width='100%'
			data-max-width='100%' data-thumbwidth='170px' data-thumbheight='110px' data-thumbmargin='16'>
			<?php
			$condition = $dbUtilities->preparenSingleOperSepar('id_commode', $newid, ' = ', '');
			$resultVisual = $dbWorker->selectUni_2('gi_commode_visualisation', $condition);
			if (!empty($resultVisual)) {
				$resultVisual = isset($resultVisual[0]) ? $resultVisual : [$resultVisual];
				foreach ($resultVisual as $rowVisual) {
					$visual_name = isset($rowVisual['nameVisual']) ? $rowVisual['nameVisual'] : '';
					$visual_url_image = isset($rowVisual['urlImage']) ? $rowVisual['urlImage'] : '';
					$visual_url_thumb = isset($rowVisual['urlThumb']) ? $rowVisual['urlThumb'] : '';
					?>
					<a href='<?php echo $visual_url_image; ?>' data-caption='<?php echo $visual_name; ?>'><img
							src='<?php echo $visual_url_thumb; ?>' width='180' height='120' style='margin:0 16px; background: #f0f0f0;'
							class='lozad'>
					</a>
					<?php
				}
			}
			?>
		</div>
	</div>
	<div class='kitchen_card_info_div'>
		<div class='someheader'>
			<?php echo $name; ?>
		</div>
		<div class='kitchen_card_info_div_content'>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Стиль
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $style; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Материал фасада
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $material; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Древесина
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $wood; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Пластик / Акрил
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $plastic; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					<a href='/kitchen-accessories/' title='Смотреть фурнитуру'>Фурнитура</a>
				</div>
				<div class='kitchen_card_info_item_content'>
					Blum, Firmax
				</div>
			</div>
		</div>
		<div class='kitchen_info_button'>
			<button class='myBtn' id='opencitieswindow'>Где купить</button>
		</div>
	</div>
	<div style='clear:both;'></div>
	<br>
	<div class='about_kitchen_div'>
		<div class='about_kitchen'>
			<h2>Особенности мебели</h2>
			<?php echo $descr; ?>
		</div>
	</div>

	<div class="kitchen_card_ajax_block">
		<button class="kitchen_card_button facades_button" id="selected-button" value="facades">Фасады</button>
		<button class="kitchen_card_button handles_button" value="handles">Ручки</button>
	</div>
	<?php
	$resultMaterialType = $dbWorker->selectCrossTable(
		'gi_facades_list',
		'gi_facade_material',
		'type',
		'id',
		'rank_material',
		'kitchens',
		$kitchen,
		'type'
	);
	if (!empty($resultMaterialType)) {
		$resultMaterialType = is_array($resultMaterialType) ? $resultMaterialType : [$resultMaterialType];
	?>
	<div class="kitchen_card_ajax_block_inner" data-where="facades-list">
		<?php
		foreach ($resultMaterialType as $key => $rowOneType) {
			$resultMaterialNew = $dbWorker->selectSimple('gi_facade_material', 'id', $rowOneType);
			$classSelected = $key === 0 ? 'selected-inner-button' : '';
			?>
			<div class="kitchen_card_inner_block <?php echo $classSelected; ?>"
				data-material-id="<?php echo $resultMaterialNew['id']; ?>">
				<?php echo $resultMaterialNew['name']; ?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="kitchen_card_facade_div">
		<?php
		$condition = $dbUtilities->prepareEqualAndLike($resultMaterialType[0], $kitchen, 'type', 'kitchens');
		$condition['OrderBy'] = '`facade_rank` ASC';
		$facades = $dbWorker->selectUni_2('FacadesList', $condition);
		if (!empty($facades)) {
			$facades = isset($facades[0]) ? $facades : [$facades];
			foreach ($facades as $rowFacade) {
				$facadeName = !empty($rowFacade['name']) ? $rowFacade['name'] : '';
				$facadeImage = !empty($rowFacade['image']) ? 'https://geosideal.ru/wp-content/uploads/facades_list/textures/' . $rowFacade['image'] : '';
				$facadeThumb = !empty($rowFacade['image']) ? 'https://geosideal.ru/wp-content/uploads/facades_list/thumbnails/' . $rowFacade['image'] : '';
				$facadeDescription = !empty($rowFacade['description']) ? $rowFacade['description'] : '';

				$newfacade = !empty($rowFacade['newfacade']) ? " <img src='/wp-content/uploads/2020/10/new.png' class='new-span'> " : "";
				$silver = !empty($rowFacade['silver']) ? " <img src='/wp-content/uploads/2020/10/silver.png' class='silver-span'> " : "";
				$datanew = !empty($rowFacade['newfacade']) ? "data-new='yes'" : "data-new='no'";
				$datasilver = !empty($rowFacade['silver']) ? "data-silver='yes'" : "data-silver='no'";
				?>
				<div class="facades_list_item">
					<a data-lightbox='image-3' <?php echo $datanew; ?> <?php echo $datasilver; ?> href='<?php echo $facadeImage; ?>'>
						<?php echo $newfacade; ?>
						<?php echo $silver; ?>
						<img src='<?php echo $facadeThumb; ?>' class='lozad'>
					</a>
					<span class="list_item_name"><?php echo $facadeName; ?></span>
					<span class="list_item_description"><?php echo $facadeDescription; ?></span>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php } ?>
	<div class='more_kitchens'>
		<p class='h3'>К этой Гостинной подойдут кухни:</p><br>
		<?php
		if (!empty($type)) {
			$condition = $dbUtilities->preparenSingleOperSepar('type', $type, ' = ', '');
			$condition['Limit'] = 3;
			$resultLike = $dbWorker->selectUni_2('Kitchen', $condition);
			if (!empty($resultLike)) {
				$resultLike = isset($resultLike[0]) ? $resultLike : [$resultLike];
				foreach ($resultLike as $rowLike) {
					$names = $rowLike['name'];
					$aliases = $rowLike['nameEng'];
					$thisava = $rowLike['avatar'];
					$materials = $rowLike['material'];
					?>
					<a href='/catalog/<?php echo $aliases; ?>/'>
						<div class='byer_grid_div' style='position:relative; margin-top:32px;'>
							<div class='byer_grid_image'
								style='background:url(<?php echo $thisava; ?>) no-repeat; background-size:cover; background-position:center;'>
							</div>
							<div class='byer_grid_header' style='text-align:left;'><?php echo $names; ?><br>
								<span style='font-size:12px;'><b>Материал:</b>
									<span style='font-weight:200;'><?php echo $materials; ?></span>
								</span>
							</div>
						</div>
					</a>
					<?php
				}
			}
		}
		?>
	</div>
	<script>
		var opencitieswindow = document.getElementById('opencitieswindow');
		opencitieswindow.onclick = function () {
			gde_zakazat_bg.style.display = 'block';
		}	
	</script>
	<script src='/wp-content/plugins/bp_contracts/js/utility.js <?php echo $version; ?>'></script>
	<script src="/wp-content/plugins/bp_kitchen_print/js/script.js <?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function addNewFiles($filesArray, $commode_name_eng, $commodeId)
{
	global $wpdb;
	$rus = rusSymbols();
	$lat = latSymbols();
	if (!empty($filesArray) and $filesArray['name'] == true && is_array($filesArray)) {
		$imagename = $filesArray['name'][0];
		$imagename = str_replace($rus, $lat, $imagename);
		// print_r($imagename);
		$linkImage = "/wp-content/plugins/bp_related_products/images/commode/avatar/fullsize/$imagename";
		$linkThumbs = "/wp-content/plugins/bp_related_products/images/commode/avatar/thumb/$imagename";
		$tmp = $filesArray['tmp_name'][0];
		$path_parts = pathinfo($filesArray['name'][0]);
		$extension = isset($path_parts['extension']) ? $path_parts['extension'] : '';
		if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
			$hrefimageFull = $_SERVER['DOCUMENT_ROOT'] . $linkImage;

			// Ensure directory exists
			$dir = dirname($hrefimageFull);
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
			}
			$thumbDir = dirname($_SERVER['DOCUMENT_ROOT'] . $linkThumbs);
			if (!file_exists($thumbDir)) {
				mkdir($thumbDir, 0777, true);
			}

			if (move_uploaded_file($tmp, $hrefimageFull)) {
				$hrefimage = $linkImage; // Use absolute path from root for reading image

				//задаем размеры миниатюрам
				$size = getimagesize($hrefimageFull);
				if ($size !== false) {
					list($width, $height) = $size;
					if ($width > 0 && $height > 0) {
						$otnosh = $width / $height;
						$newWidth = 600;
						$newHeight = $newWidth / $otnosh;
						$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ

						$hrefthumbFull = $_SERVER['DOCUMENT_ROOT'] . $linkThumbs;

						switch ($extension) {
							case "jpg":	//СОЗДАНИЕ JPG
								$image = imagecreatefromjpeg($hrefimageFull); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
								imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
								imagejpeg($image_p, $hrefthumbFull, 100);
								break;
							case "jpeg": //СОЗДАНИЕ JPG
								$image = imagecreatefromjpeg($hrefimageFull);
								imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
								imagejpeg($image_p, $hrefthumbFull, 100);
								break;

							case "png":	//СОЗДАНИЕ ПНГ
								$image = imagecreatefrompng($hrefimageFull);
								imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
								imagesavealpha($image_p, true); //Включаем сохранение альфа канала
								imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
								imagepng($image_p, $hrefthumbFull);
						}
					}
				}
				return $linkImage;
			}
		}
	}
}
function addGalleryCommode($filesArray, $commode_name_eng, $commodeId)
{
	global $wpdb;
	$rus = rusSymbols();
	$lat = latSymbols();
	if (!empty($filesArray) and $filesArray['name'] == true && is_array($filesArray)) {
		foreach ($filesArray['name'] as $numberAddFile => $nameAddFile) {
			$$numberAddFile = $nameAddFile; //надо 2 знака $
		}
		for ($i = 0; $i <= $numberAddFile; $i++) {
			$imagelink = "/wp-content/plugins/bp_related_products/images/commode/gallery/$commode_name_eng/images/" . $filesArray['name'][$i];
			$thumblink = "/wp-content/plugins/bp_related_products/images/commode/gallery/$commode_name_eng/thumbs/" . $filesArray['name'][$i];
			$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
			$thumblink = str_replace($rus, $lat, $thumblink);
			$tmp = $filesArray['tmp_name'][$i];
			$path_parts = pathinfo($filesArray['name'][$i]);
			$extension = isset($path_parts['extension']) ? $path_parts['extension'] : '';
			//так надо :)
			$hrefimage = $_SERVER['DOCUMENT_ROOT'] . $imagelink;
			$hrefthumb = $_SERVER['DOCUMENT_ROOT'] . $thumblink;

			// Ensure directories exist
			$dir = dirname($hrefimage);
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
			}
			$thumbDir = dirname($hrefthumb);
			if (!file_exists($thumbDir)) {
				mkdir($thumbDir, 0777, true);
			}

			if ($extension == "png" or $extension == "jpg" or $extension == "jpeg") {
				if (!move_uploaded_file($tmp, $hrefimage)) {
					continue;
				}
			} else {
				// echo "Вы попытались загрузить неподходящее изображение<br>";
				continue;
			}
			//задаем размеры миниатюрам
			$size = getimagesize($hrefimage);
			if ($size === false) {
				continue;
			}
			list($width, $height) = $size;
			if ($height <= 0) {
				continue;
			}
			$otnosh = $width / $height;
			$newWidth = 240;
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
			$sqlInsert = "INSERT INTO gi_commode_visualisation (id_commode, name_visual, url_image, url_thumb, rank_visual) VALUES ('$commodeId', '', '$imagelink', '$thumblink', '$i')";
			$resultInsert = $wpdb->query($sqlInsert);

		}

	}
}
