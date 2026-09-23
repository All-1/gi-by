<?php

/*Plugin Name: bp_kitchen_add
Description: Работа с кухнями. Добавление в каталог, редактирование.
Version: 1.0
Author: Business Park*/
add_action('admin_menu', 'add_pages');
function add_pages() {
    add_menu_page('Каталог кухонь', 'Каталог кухонь', 8, __FILE__, 'kitchen_catalogue', 'dashicons-images-alt2');
		add_submenu_page(__FILE__, 'Добавить кухню', 'Добавить кухню', 8, 'add_kitchen_page', 'add_kitchen');
		//add_submenu_page(__FILE__, 'Добавить фасады', 'Добавить фасады', 8, 'add_facade_page', 'add_facade');
		//add_submenu_page(__FILE__, 'Столешницы', 'Столешницы', 8, 'add_tabpletop_page', 'add_tabletop');
		//add_submenu_page(__FILE__, 'Ручки', 'Ручки', 8, 'sub-page3', 'add_handle');
		add_submenu_page(__FILE__, 'Редактировать', 'Редактировать', 8, 'sub-page4', 'redact_kitchen');
		add_submenu_page(__FILE__, 'Наборы фильтров', 'Фильтры', 8, 'sub-page6', 'filtres_page');
		add_submenu_page(__FILE__, 'Инструкции', 'Инструкции', 8, 'sub-page5', 'instructions');
    add_menu_page('Ручки', 'Ручки', 8, 'Handles_page', 'add_handle', 'dashicons-editor-customchar');
		add_submenu_page('Handles_page', 'Редактировать', 'Редактировать', 8, 'redact_handles', 'redact_handles');
    add_menu_page('Столешницы', 'Столешницы', 8, 'Tabletops_page', 'add_tabletop', 'dashicons-welcome-add-page');
		add_submenu_page('Tabletops_page', 'Редактировать', 'Редактировать', 8, 'redact_tabletops', 'redact_tabletops');
    //add_menu_page('Фасады', 'Фасады', 8, 'Facades_page', 'add_facade', 'dashicons-visibility');
		//add_submenu_page('Facades_page', 'Редактировать', 'Редактировать', 8, 'redact_facades', 'redact_facades');
}


function filtres_page(){
	global $wpdb;
	
	echo "<h1>Наборы фильтров</h1>";
	$a ="
	<h2>Добавить новый фильтр</h2>
	<form method='POST' class='addfilterform'>
		<select name='category' required class='selectcategory'>
			<option selected disabled value=''>Выберите тип фильтра</option>
			<option value='color'>Цвет кухни </option>
			<option value='style'>Стиль кухни </option>
			<option value='config'>Конфигурация </option>
		</select><br>
		<input type='text' name='filtervalue' placeholder='Значение фильтра' required/><br>
		<input type='text' name='color' placeholder='Цвет' style='display:none;' id='colorinput'/><br><br>
		
		<input type='submit' name='addFilter' value='Добавить'/>
		
	</form>
	";
	
	//Фильтры по цвету
	$a.="
	<h2>Фильтры по цвету:</h2>
	<div style='background:white; padding:20px; width:max-content; max-width:calc(98% - 40px);'>
	<form method='POST'>"; 
	$sql = "SELECT * FROM gi_kitchen_filtres WHERE category = 'color'";
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$newid = $row->newid;
		$filtervalue = $row->filtervalue;
		$color = $row->color;
		$a.="
		<div style='float:left; margin:7px;'>
			<div style='width:20px; height:20px; background:$color; float:left; float:left; margin-right:5px;'></div> $filtervalue <button name='delete' value='$newid'>X</button> <br>
		</div>
		";
	}
	$a.="
	<div style='clear:both;'></div>
	</form>
	</div>
	";
	
	//Фильтры по конфигурации
	$a.="
	<h2>Фильтры по конфигурации:</h2>
	<div style='background:white; padding:20px; width:max-content; max-width:calc(98% - 40px);'>
	<form method='POST'>"; 
	$sql = "SELECT * FROM gi_kitchen_filtres WHERE category = 'config'";
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$newid = $row->newid;
		$filtervalue = $row->filtervalue;
		$a.="
		<div style='float:left; margin:7px;'>
			$filtervalue <button name='delete' value='$newid'>X</button> <br>
		</div>
		";
	}
	$a.="
	<div style='clear:both;'></div>
	</form>
	</div>
	";

	
	//Фильтры по стилю
	$a.="
	<h2>Фильтры по стилю:</h2>
	<div style='background:white; padding:20px; width:max-content; max-width:calc(98% - 40px);'>
	<form method='POST'>"; 
	$sql = "SELECT * FROM gi_kitchen_filtres WHERE category = 'style'";
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$newid = $row->newid;
		$filtervalue = $row->filtervalue;
		$a.="
		<div style='float:left; margin:7px;'>
			$filtervalue <button name='delete' value='$newid'>X</button> <br>
		</div>
		";
	}
	$a.="
	<div style='clear:both;'></div>
	</form>
	</div>
	";
	
	$a.="
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
	if(isset($_POST['delete'])){
		$deleteid = $_POST['delete'];
		$sql = "DELETE FROM gi_kitchen_filtres WHERE newid = '$deleteid'";
		$result = $wpdb->get_results($sql);
		echo "Фильтр удален";
		echo "<script>window.location.reload();</script>";
	}
	//Добавляем фильтр
	if(isset($_POST['addFilter'])){
		$category = $_POST['category'];
		$filtervalue = $_POST['filtervalue'];
		$color = $_POST['color'];
		
		$sql = "INSERT INTO `gi_kitchen_filtres` (`category`, `filtervalue`, `color`) VALUES ('$category', '$filtervalue', '$color');";
		$result = $wpdb->get_results($sql);
		echo "Фильтр добавлен";
		echo "<script>window.location.reload();</script>";
	}
	
	echo $a;
}


function kitchen_catalogue() {
    echo "<h1>Каталог кухонь</h1>";
	echo "Ниже перечислены кухни, уже добавленые в каталог<br>";
	global $wpdb;
	$sql = "SELECT * FROM `gi_kitchen`";
	$result = $wpdb->get_results($sql);
	$a ="<div style='width:100%; text-align:center;'>
	<form method='POST'>
	";
	foreach ($result as $row) {
		$name = $row->name;
		$description = $row->description;
		$avatar = $row->avatar;
		$directory = $row-> folder;
		$type = $row->type;
		$cost = $row->basic_cost;	
		$nameEng = $row -> name_eng;
		$newid= $row -> newid;
		$folder= $row -> folder;
		$a.= "
		<div class='kitchen_point' style='width:330px; margin:20px; display:inline-block; position:relative;'>
		<input type='checkbox' style='position:absolute; top:7px; left:5px;' name='$newid' value='$newid'/>
			<div style='width:100%; height:200px; background:url($avatar) no-repeat; background-size:cover; background-position:center;'></div>
			<h2>Кухня $name</h2>
			Ярлык: <b>$nameEng</b>
			<div style='width:100%; text-align:right; font-size:16px; color:green; font-weight:600;'>$cost руб.</div>
			<br><hr>
		</div>		
		";
		if (isset($_POST['kitchenDelete']) and isset($_POST[$newid])){
			$delete_newid = $_POST[$newid];
			$sqlVisual = "SELECT * FROM gi_kitchen_visualisation WHERE id_kitchen = '$delete_newid'";
			$resultVisual = $wpdb->get_results($sqlVisual);
			foreach ($resultVisual as $rowVisual) {
				$url_image = $rowVisual->url_image;
				$url_thumb = $rowVisual->url_thumb;
				unlink ($url_image);
				unlink ($url_thumb);
			}
			$sqlDelete = "DELETE FROM gi_kitchen WHERE newid='$delete_newid'";
			$resultDelete = $wpdb->query($sqlDelete);
			$sqlFacade = "DELETE FROM gi_facade WHERE kitchen_eng = '$nameEng'";
			$resultFacade = $wpdb->query($sqlFacade);
			$deleteVisual = "DELETE FROM gi_kitchen_visualisation WHERE id_kitchen = '$delete_newid'";
			$resultDeleteVisual = $wpdb->query($deleteVisual);
			$deleteImage = ".." . $avatar;
			unlink ($deleteImage);
			$folder = ".." . $folder;
			//Удаление папки и вложенных файлов (rmdir уаляет только пустую) - пишем функцию 
			function removeKitchenDir($folder) {
				if ($objs = glob($folder."/*")) {
					foreach($objs as $obj) {
						is_dir($obj) ? removeKitchenDir($obj) : unlink($obj); 
					}
				}
				rmdir($folder);
			}
			removeKitchenDir($folder);
			
			echo "Выбранные кухни удалены";
			echo "<script>window.location.reload();</script>";
		}
	}
	$a.="<br><Br><input type='submit' name='kitchenDelete' value='Удалить кухни'/>
	</form>
	</div>";
	echo $a;
}
//ДОБАВЛЕНИЕ КУХНИ
function add_kitchen() {
	$phpself = $_SERVER['PHP_SELF'];
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('-', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	global $wpdb;
    echo "<h1>Добавить кухню</h1>";
	$a ="
	<form method='POST' enctype='multipart/form-data'>
		<div style='width:46%; margin:1%; background:white; padding:1% 1%; float:left;'>
			<h2>Заполните поля формы</h2><br>
			<select name='type' style='width:100%; height:40px;'>
				<option disabled selected required>Выберите стиль кухни</option>
				<option value='Классика'>Классика</option>
				<option value='Модерн'>Модерн</option>
			</select><br>
			Опубликовать кухню:<br>
			<input type='checkbox' name='public' value='yes' checked><br>
			Название кухни:<br>
			<input type='text' name='name' required style='width:100%; height:40px;'><br>
			Описание кухни (выводится в начале страницы):<br>
			<textarea name='description' required style='width:100%; height:40px;'></textarea><br>
			Введите базовую стоимость кухни:<br>
			<input type='text' name='cost' required style='width:100%; height:40px;'><br><br>

			Загрузите обложку кухни (аватар):<br>
			<input type='file' name='avatar' required ><br>
			<br><bR>
			Добавить фотографии в галерею кухни:<br>
			<input type='file' name='gallery[]' multiple required><br><br>
		";
		
		$a.="<br><br><h2>Выберите материал</h2>";
		//ВЫВОД МАТЕРИАЛОВ
		

		$sqlMaterial = "SELECT * FROM `gi_kitchen_material`";
		$resultMaterial = $wpdb->get_results($sqlMaterial);
		foreach ($resultMaterial as $rowMaterial) {
			$material = $rowMaterial->material;
			$material_eng = $rowMaterial->material_eng;
			$a.="
			<div style='display:inline-block; text-align:center; vertical-align:top;'>
				<input type='checkbox' name='materials[]' value='$material'> $material
			</div>
			";
		}
		
		$a.="<br><br><br>
		Введите древесину или поставьте прочерк:<br>
		<input name='wood' placeholder='Например: Дуб, ясень' style='width:100%; height:40px;  border:1px solid #dcdcdc'/><br>
		Введите материал пластика или поставьте прочерк:<br>
		<input name='plastic' placeholder='Например: Fenix' style='width:100%; height:40px;  border:1px solid #dcdcdc'/><br>
		Введите производителей фурнитуры или поставьте прочерк: <br>
		<input name='furnitura' placeholder='Например: Firmax, Blum' style='width:100%; height:40px; border:1px solid #dcdcdc;'/><br><br>
		</div>
		<div style='float:left; width:46%; margin:1%; padding:1% 1%; background:white; min-height:100px;'>
			<h2>Выберите столешницы для кухни</h2>
		";
		//ВЫВОД СТОЛЕШНИЦ
		$sql = "SELECT `image_link`, `thumb_link`, `newid`, `tabletop_name`, `tabletop_name_eng`, `made_by` FROM `gi_tabletop` WHERE tabletop_name !=''";
		$result = $wpdb->get_results($sql);
		$a.="<div style='height:400px; overflow:auto;'>";
		foreach ($result as $row) {
			$imagelink = $row->image_link;
			$thumblink = $row->thumb_link;
			$tabletopname = $row->tabletop_name;
			$tabletopnameEng = $row->tabletop_name_eng;
			$madebytabletop = $row->made_by;
			$newidtabletop = $row->newid;
			$a.="
			<div style='display:inline-block; text-align:center; vertical-align:top;'>
				<input type='checkbox' name='tabletops[]' value='$tabletopname' style='position:relative; top:30px; left:-35px;'> 
				<div style='width:100px; height:100px; margin:5px; background:url(../$thumblink) no-repeat; background-position:center; background-size:cover;'></div>
				<div style='width:100px;'>$tabletopname</div>
			</div>
			";
		}
		$a.="</div>";
		$a.="<br><br><h2>Выберите ручки для кухни</h2>";
		//ВЫВОД РУЧЕК
		$a.="<div style='height:400px; overflow:auto;'>";
		$sqlHandle = "SELECT `image_href`, `thumb_href`, `newid`, `model_name`, `model_name_eng` FROM `gi_handle`";
		$resultHandle = $wpdb->get_results($sqlHandle);
		foreach ($resultHandle as $rowHandle) {
			$image_href = $rowHandle->image_href;
			$thumb_href = $rowHandle->thumb_href;
			$model_name = $rowHandle->model_name;
			$model_name_eng = $rowHandle->model_name_eng;
			$newidhandle = $rowHandle->newid;
			$a.="
			<div style='display:inline-block; text-align:center; vertical-align:top;'>
				<input type='checkbox' name='handles[]' value='$model_name' style='position:relative; top:27px; left:-35px;'> 
				<div style='width:100px; height:100px; margin:5px; background:url(../$image_href) no-repeat; background-position:center; background-size:cover;'></div>
				<div style='width:100px;'>$model_name</div>
			</div>
			";
		}	
		$a.="</div>";
		
		
		//вносим данные формы в базу
		if (!empty($_POST['materials'])){
			$string_materials = implode ("," ,$_POST['materials']) . ",";	
		}
		if (!empty($_POST['plastic'])) $plastic = $_POST['plastic']; else $plastic = "-";
		if (!empty($_POST['wood']))	$wood = $_POST['wood']; else $wood = "-";
		if (!empty($_POST['furnitura'])) $furnitura = $_POST['furnitura']; else $furnitura = "-";
		
		if (!empty($_POST)){
			//объединяем выбранные столешницы в строку через запятую
			$string_tabletop = implode ("," ,$_POST['tabletops']);
			//объединяем выбранные ручки
			$string_handle = implode ("," ,$_POST['handles']);
			$name_kitchen = $_POST['name'];
			$type_kitchen = $_POST['type'];
			$description_kitchen = $_POST['description'];
			$cost_kitchen = $_POST['cost'];
			$public_kitchen = $_POST['public'];
			$name_kitchen_eng = str_replace($rus, $lat, $name_kitchen);
			//проверяем наличие папок
			$images_dir = bp_kitchen_fs_path($name_kitchen_eng);
			if (!file_exists($images_dir)) {
				bp_kitchen_ensure_dirs($name_kitchen_eng);
			} else {
				echo "Такая кухня уже существует!";
				die;
			}
			
			//Загрузка аватара
			if(!empty($_FILES['avatar']) and $_FILES['avatar']['name']==true){
				if(is_uploaded_file($_FILES["avatar"]["tmp_name"])){		
					$avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_kitchen_eng/avatar/".basename($_FILES['avatar']['name']);				
					$avatarlink = str_replace($rus, $lat, $avatarlink);
					
					$tmp_avatar = $_FILES['avatar']['tmp_name'];
					$path_parts_avatar  = pathinfo($_FILES['avatar']['name']);
					$extension_avatar = $path_parts_avatar['extension'];
					$upload_link = bp_kitchen_url_to_fs($avatarlink);
					if ($extension_avatar == "png" or $extension_avatar=="jpg" or $extension_avatar=="jpeg"){
						move_uploaded_file ($tmp_avatar, $upload_link);
					}
				}
			}
			
			//СОЗДАЕМ СТРАНИЧКУ В WP
			$post_title = $name_kitchen;
			$post_content = "<!-- wp:paragraph --><p>[kitchen_print]</p><!-- /wp:paragraph -->";
			
			$strannie_bukvi  = array("Ą","ą","Ć","ć","Ę","ę","Ł","ł","Ń","ń","Ó","ó","Ś","ś","Ź","ź","Ż","ż");
			$normalnie_bukvi = array("A","a","C","c","E","e","L","l","N","n","O","o","S","s","Z","z","Z","z");
			
			$post_name_eng1 = mb_strtolower(str_replace($strannie_bukvi, $normalnie_bukvi, $post_title));
			
			$post_name_eng = replaceSymbols($post_name_eng1);

			$sql_page = "SELECT DISTINCT post_title FROM gi_posts WHERE post_title = '$post_title' AND post_parent = 42";
			$result_page = $wpdb -> get_results($sql_page);
			$count_pages = count($result_page);
			//Задаем guid (ссылку на страницу), применяем id посл. страницы +1, чтоб ничего не поломалось в случае каких-то изменений
			$siteurl = get_site_url();

			if($count_pages == 0){
				// Создаем массив данных новой записи
				$post_data = array(
					'post_title'    => $post_title,
					'post_content'  => $post_content,
					'post_status'   => 'publish',
					'post_author'   => 1,
					'post_type' 	=> 'page',
					'post_name'		=> $post_name_eng,
					'ping_status' 	=> 'closed',
					'comment_status'=> 'closed',
					'post_parent'	=> 42,
				);
				// Вставляем запись в базу данных
				$post_id = wp_insert_post( $post_data );
				
				//Добавление данных в БД
				$folder = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_kitchen_eng/";
				$timestamp = time();
				$sql = "INSERT INTO `gi_kitchen` (`name`, `name_eng`, `description`, `avatar`, `folder`, `type`, `basic_cost`, `tabletops`, `handles`, `material`, `sales`, `wood`, `plastic`,`furnitura`,`public`,`timestamp`) 
						VALUES ('$name_kitchen', '$name_kitchen_eng', '$description_kitchen', '$avatarlink', '$folder', '$type_kitchen', '$cost_kitchen', '$string_tabletop', '$string_handle', '$string_materials', 'no', '$wood', '$plastic', '$furnitura', '$public_kitchen', '$timestamp');";
				$result = $wpdb->get_results($sql);	
				$resultVisualisation = [];
				$kitchenId = $wpdb->get_var("SELECT MAX(newid) FROM gi_kitchen");
				addGallery($_FILES['gallery'], $name_kitchen_eng, $kitchenId);
				
				echo "<br>Кухня добавлена, теперь отредаутируйте фассады";
				// echo "<br>Результаты загрузки фотографий: " . print_r($resultVisualisation, true);
			}
			else {
				echo "Такая кухня уже существует! Найдено повторение в базе данных по Название модели и родительской странице (каталог)";
			}
			
			//Загружаем фотографии галереи
			
			
			
			
		}
		$a.="
		</div>
		<div style='clear:both;'></div>
		<input type='submit' style='padding:24px 48px; background:green; border:none; outline:none; color:#fff;'>
	</form>
	";
	echo $a;
}
//ДОБАВЛЕНИЕ ФАСАДОВ
function add_facade(){
	echo "<h1>Добавить фасады для кухонь</h1>";
	$phpself = $_SERVER['PHP_SELF'];
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	global $wpdb;
	$a ="
	<form method='POST' enctype='multipart/form-data'>
		Выберите кухню, фассады которой Хотите добавить:<br><br>
		<select name='kitchen' required>
			<option selected disabled>Выберите кухню</option>
	";	
	$sql = "SELECT `name`, `name_eng`, `folder` FROM `gi_kitchen`";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$name_kitchen = $row->name;
		$name_kitchen_eng = $row->name_eng;
		$folder = $row->folder;
		$a.="
		<option value='$name_kitchen'>$name_kitchen</option>
		";
	}
	$a.="</select><br>
		Выберите тип загружаемых фасадов<br>
		<select name='facades_type'>
			<option selected disabled>Выберите материал</option>
			<option value='Массив дерева'>Массив дерева</option>
			<option value='Шпон'> Шпон </option>
			<option value='Пластик'> Пластик </option>
			<option value='Акрил'> Акрил </option>
			<option value='Syncron'> Syncron </option>
			<option value='Skin'> Skin </option>
			<option value='МДФ Глянец'> МДФ Глянец </option>
			<option value='МДФ Матовый'> МДФ Матовый </option>
			<option value='Egger'> Egger </option>
		";
	/* чето не работает, добавил option вручную
	$sqlFacadesTypes = "SELECT * FROM `gi_facades_types`";
	$resultFacadesTypes = $wpdb->get_result($sqlFacadesTypes);
	foreach($resultFacadesTypes as $rowFacadesTypes){
		$rus_type = $rowFacadesTypes->rus_type;
		$eng_type = $rowFacadesTypes->eng_type;
		$rate = $rowFacadesTypes->rate;
		$a.="
		<option value='$rus_type'>$rus_type</option>
		";
	}*/
	$a.="
		</select><br>
		Выберите изобржения фасадов для загрузки:<br>
		<input type='file' name='facadeload[]' multiple accept='image/*'><br><br>
		<input type='submit'/>
	</form>
	";
	if(!empty($_POST['kitchen']) and !empty($_POST['facades_type']) and !empty($_FILES['facadeload']) and $_FILES['facadeload']['name']==true){
		$kitchen = $_POST['kitchen'];
		$kitchen_eng = str_replace($rus, $lat, $kitchen);
		$facade_type = $_POST['facades_type'];
		foreach($_FILES['facadeload']['name'] as $numberAddFile=>$nameAddFile){
			$$numberAddFile = $nameAddFile; //надо 2 знака $
		}
		for ($i=0; $i<=$numberAddFile; $i++) {
			$imagelink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$kitchen/facade/images/" . $_FILES['facadeload']['name'][$i];
			$thumblink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$kitchen/facade/thumbs/" . $_FILES['facadeload']['name'][$i];
			$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
			$thumblink = str_replace($rus, $lat, $thumblink);
			$tmp = $_FILES['facadeload']['tmp_name'][$i];
			$path_parts  = pathinfo($_FILES['facadeload']['name'][$i]);
			$extension = $path_parts['extension'];
			//так надо :)
			$hrefimage=".." . $imagelink;
			$hrefthumb=".." . $thumblink;
			if ($extension == "png" or $extension=="jpg" or $extension=="jpeg"){
				move_uploaded_file ($tmp, $hrefimage);
			}
			else {
				echo "Вы попытались загрузить неподходящее изображение<br>";
				continue;
			}
			//задаем размеры миниатюрам
			list($width, $height) = getimagesize($hrefimage);
			$otnosh = $width / $height;
			$newWidth = 200;
			$newHeight = $newWidth / $otnosh;
			$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				switch($extension){
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
		//ДОБАВЛЯЕМ ССЫЛКИ И ТИПЫ В БАЗУ
		$timestamp = time();
		echo "Фасады успешно загружены";
		echo "<script>window.location.reload();</script>";
		$sqlHrefs = "INSERT INTO `gi_facade` (`type`, `image`, `thumb`, `kitchen`, `kitchen_eng`, `timestamp`) VALUES ('$facade_type', '$imagelink', '$thumblink', '$kitchen', '$kitchen_eng', '$timestamp');";
		$resultHrefs = $wpdb->get_results($sqlHrefs);	
		}
		echo "Фотографии загружены";	
	}
	echo $a;
	//Название и описание столешниц
	$sqlText = "SELECT `type`, `kitchen`, `image`, `newid` FROM `gi_facade` WHERE name=''";
	$resultText = $wpdb->get_results($sqlText);
	if($resultText){
		echo "<h2>Заполните информацию по фасадам</h2>";
	}
	$b.="<form method='POST'>";
	foreach ($resultText as $rowText) {
		$facadeType = $rowText->type;
		$facadeKitchen = $rowText->kitchen;
		$facadeImage = $rowText->image;
		$newid = $rowText->newid;
		//Уникальные значения для инпутов
		$newidName = $newid . 'Name';
		$newidDesc = $newid . 'Made';
		$b.= "
		<div style='width:200px; float:left; margin:5px 10px;'>
			<img src='..$facadeImage' style='max-width:100%;'/>
			<br><b>Кухня: $facadeKitchen</b>
			<input style='width:100%;' placeholder='Введите название' name='$newidName'/>
			<input style='width:100%;' placeholder='Введите описание' name='$newidDesc'/>
		</div>
		";
		if(isset($_POST["$newidName"]) and isset($_POST["$newidDesc"])){
			$name_facade = $_POST["$newidName"];
			$name_facade_eng = str_replace($rus, $lat, $name_facade);
			$facade_description = $_POST["$newidDesc"];
			echo "Информация по фасадам добавлена";
			echo "<script>window.location.reload();</script>";
			$sqlBase = "UPDATE gi_facade SET name = '$name_facade', name_eng = '$name_facade_eng', description='$facade_description' WHERE image='$facadeImage'";
			$resultBase = $wpdb->get_results($sqlBase);
		}
	}
	$b.="<div style='clear:both;'></div>
		<br><br>";
	if($resultText){
		$b.="<input type='submit' value='Внести правки'/>";
	}
	$b.="</form>";
	echo $b;
	//СЮДА ФАСАДЫ В ВИДЕ ВЫПАДАЮЩИХ ВКЛАДОК С ВОЗМОЖНОСТЬЮ УДАЛЕНИЯ (ТАБЫ) http://shpargalkablog.ru/2012/03/css-tabs.html#tab1 -------------- заморочился
	echo "<br><h2>Фасады в каталоге:</h2>";
	$c.="<div class='korpus'>";
	$d.="<style>
		.korpus {position:relative;}
		.korpus label {position:relative; top:0;}
		.korpus > div, .korpus > input { display: none; }
		.korpus label { padding: 5px; border: 1px solid #aaa; line-height: 28px; cursor: pointer; position: relative; bottom: 1px; background: #fff; }
		.korpus input[type='radio']:checked + label {background:#98FB98;}";
	$sqlDistinctName = "SELECT DISTINCT kitchen FROM gi_facade WHERE kitchen!='' ORDER BY kitchen";
	$resultDistinctName = $wpdb->get_results($sqlDistinctName);
	$kitchen_count = count($resultDistinctName);
	$i=1;
	foreach ($resultDistinctName as $rowTabs) {
		$facade_kitchen = $rowTabs->kitchen;
		if ($i==1) $checked = "checked"; else $checked = "";
		$d.=".korpus > input:nth-of-type($i):checked ~ div:nth-of-type($i){display: block; padding: 5px; border: 1px solid #aaa;}";
		$c.="<input type='radio' name='tab' id='vk$i' $checked/><label for='vk$i'>$facade_kitchen</label>
		<div style='position: absolute; top:30px; width:95%; background:white; margin-bottom:30px;'>
		<form method='POST'>";
		$sqlFacade = "SELECT * FROM gi_facade WHERE kitchen = '$facade_kitchen'";
		$resultFacade = $wpdb->get_results($sqlFacade);
		foreach ($resultFacade as $rowFacade){
			$facade_descr = $rowFacade->description;
			$facade_name = $rowFacade->name;
			$facade_type = $rowFacade->type;
			$facade_image = $rowFacade->image;
			$facade_thumb = $rowFacade->thumb;
			$facade_newid = $rowFacade->newid;
			if($facade_name==''){
				$facade_name = "Не заполнено";
				$facade_descr = "Не заполнено";
			}
			$c.="
			<input type='checkbox' name='$facade_newid' value='$facade_newid' style='position:relative; top:10px; left:33px;'/>
			<div style='display:inline-block; margin:5px; overflow:hidden; text-align:center; vertical-align:top;'>
				<div style='width:120px; height:120px; background:url(..$facade_image) no-repeat; background-position:center; background-size:cover; margin-bottom:5px; word-wrap:break-word;'></div>
				<div style='width:120px; font-size:18px; font-weight:600;'>$facade_name</div><i style='font-size:11px; width:120px;'>($facade_type)</i><br>
				<div style='width:120px; width:120px;'>$facade_descr</div>
			</div>
			";
			if (!empty($_POST[$facade_newid]) and isset($_POST['delete'])){
				$facedeForDelete = $_POST[$facade_newid];
				//ТУТ НАДО СДЕЛАТЬ ВЫБОРКУ КАРТИНОК ПО NEWID (WHERE newid = '$facade_name'). поиск этой строки: Удалениефасада
				$sqlDeleteImage = "SELECT * from gi_facade WHERE newid = '$facedeForDelete'"; 
				$resultDeleteImage = $wpdb->get_results($sqlDeleteImage);
				foreach($resultDeleteImage as $rowDeleteImage){
					$imageDelete = $rowDeleteImage->image;
					$thumbDelete = $rowDeleteImage->thumb;
					$imageDelete = ".." . $imageDelete;
					$thumbDelete = ".." . $thumbDelete;
					unlink ($imageDelete);
					unlink ($thumbDelete);
				}
				
				$facadeKitchenForDelete = $facade_kitchen;
				$sqlDeleteFacade = "DELETE FROM gi_facade WHERE newid='$facedeForDelete'";
				$resultDeleteFacade = $wpdb->get_results($sqlDeleteFacade); 

				echo "Выбранные фасады удалены";
				echo "<script>window.location.reload();</script>";
			}
		}
		$c.="
		<br><input type='submit' name='delete' value='Удалить'/>
		</form></div>";
		$i++;
	}
	$c.="</div>";
	echo $c;
	echo $d;
}
//ДОБАВЛЕНИЕ СТОЛЕШНИЦ
function add_tabletop(){
	echo "<h1>Добавить столещницы</h1>";
	$phpself = $_SERVER['PHP_SELF'];
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	global $wpdb;
	?>
	<form enctype="multipart/form-data" method="post">
			<br>Выбрать изображения:<br>
			<br><input type='file' name='tabletopload[]' multiple accept="image/*"></input><br><br>
			<input type="submit" name="loadtabletop" value="Загрузить файлы"/><br><br><br>
	</form> 
	<?php
	if(!empty($_POST['loadtabletop'])){
		if(!empty($_FILES['tabletopload']) and $_FILES['tabletopload']['name']==true){
			foreach($_FILES['tabletopload']['name'] as $numberAddFile=>$nameAddFile){
				$$numberAddFile = $nameAddFile; //надо 2 знака $
			}
			for ($i=0; $i<=$numberAddFile; $i++) {
				$imagelink = "/wp-content/plugins/bp_kitchen_add/images/tabletop/base_images/".basename($_FILES['tabletopload']['name'][$i]);
				$thumblink = "/wp-content/plugins/bp_kitchen_add/images/tabletop/thumbs/".basename($_FILES['tabletopload']['name'][$i]);
				$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
				$thumblink = str_replace($rus, $lat, $thumblink);
				$tmp = $_FILES['tabletopload']['tmp_name'][$i];
				$path_parts  = pathinfo($_FILES['tabletopload']['name'][$i]);
				$extension = $path_parts['extension'];
				//так надо :)
				$hrefimage=".." . $imagelink;
				$hrefthumb=".." . $thumblink;
				if ($extension == "png" or $extension=="jpg" or $extension=="jpeg"){
					move_uploaded_file ($tmp, $hrefimage);
				}
				else {
					echo "Вы попытались загрузить неподходящее изображение<br>";
					continue;
				}
				//задаем размеры миниатюрам
				list($width, $height) = getimagesize($hrefimage);
				$otnosh = $width / $height;
				$newWidth = 200;
				$newHeight = $newWidth / $otnosh;
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				switch($extension){
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
				//ДОБАВЛЯЕМ ССЫЛКИ В БАЗУ
				$timestamp = time();
				echo "Данные успешно загружены";
				$sqlHrefs = "INSERT INTO `gi_tabletop` (`image_link`, `thumb_link`, `timestamp`) VALUES ('$imagelink', '$thumblink', '$timestamp');";
				$resultHrefs = $wpdb->get_results($sqlHrefs);
				echo "<script>window.location.reload();</script>";				
			}
		echo "Фотографии загружены";
		}
	}
	//Название и производитель столешниц
	$sql = "SELECT `image_link`, `thumb_link`, `newid` FROM `gi_tabletop` WHERE tabletop_name=''";
	$result = $wpdb->get_results($sql);
	if($result){
		echo "<h2>Заполните информацию по столешницам</h2>";
	}
	$b.="<form method='POST'>";
	foreach ($result as $row) {
		$imagelink = $row->image_link;
		$thumblink = $row->thumb_link;
		$newid = $row->newid;
		//Уникальные значения для инпутов
		$newidName = $newid . 'Name';
		$newidMade = $newid . 'Made';
		$b.= "
		<div style='width:200px; float:left; margin:5px 10px;'>
			<img src='../$imagelink' style='max-width:100%;'/>
			<input style='width:100%;' placeholder='Введите название' name='$newidName'/>
			<input style='width:100%;' placeholder='Введите производителя' name='$newidMade'/>
		</div>
		";
		if(isset($_POST["$newidName"]) and isset($_POST["$newidMade"])){
			$name_tabletop = $_POST["$newidName"];
			$name_tabletop_eng = str_replace($rus, $lat, $name_tabletop);
			$made_tabletop = $_POST["$newidMade"];
			//ААААААААААААААААААААААААААААА
			echo "Информация внесена";
			echo "<script>window.location.reload();</script>";
			$sqlBase = "UPDATE gi_tabletop SET tabletop_name = '$name_tabletop', tabletop_name_eng = '$name_tabletop_eng', made_by='$made_tabletop' WHERE image_link='$imagelink'";
			$resultBase = $wpdb->get_results($sqlBase);
		}	
	}
	$b.="
	<div style='clear:both;'></div>
	<br><input type='submit'/>
	</form><br><br>
	";
	echo $b;
	//Столешницы в наличии
	$sqlCat = "SELECT `image_link`, `thumb_link`, `newid`, `tabletop_name`, `made_by` FROM `gi_tabletop` WHERE tabletop_name !=''";
	$resultCat = $wpdb->get_results($sqlCat);
	if($resultCat){
		echo "<h2>Столешницы в каталоге:</h2>";	
	}
	$c.="
	<form method='POST'>
	";
	foreach ($resultCat as $rowCat) {
		$imagelink = $rowCat->image_link;
		$thumblink = $rowCat->thumb_link;
		$tabletopname = $rowCat->tabletop_name;
		$tabletopmadeby = $rowCat->made_by;
		$newid = $rowCat->newid;	
		$c.="
		<div style='float:left; text-align:center; position:relative;'>
			<input type='checkbox' name='$newid' value='$newid' style='position:absolute; top:21px; left:8px;'/>
			<div style='width:200px; height: 200px; background:url(../$imagelink) no-repeat; background-position:center; background-size: cover; margin:5px;'></div>
			<b>$tabletopname</b><br><br>
		</div>
		";
		
		if (isset($_POST['delete_tabletop']) and isset($_POST[$newid])){
				$tabletopForDelete = $_POST[$newid];
				$image_delete = ".." . $imagelink;
				$thumb_delete = ".." . $thumblink;
				
				//Заменяем в таблице кухонь столешницы (вырезаем удаленные)
				$sqlOldTabletops = "SELECT tabletops FROM gi_kitchen WHERE tabletops LIKE '%$tabletopname%'";
				$resultOldTabletops = $wpdb->get_results($sqlOldTabletops); 
				foreach ($resultOldTabletops as $rowOldTabletops){
					$oldTabletops = $rowOldTabletops -> tabletops;
					$tabletop_name_1 = $tabletopname . ",";
					$tabletop_name_2 = $tabletopname;
					$newTabletops = str_replace($tabletop_name_1, "", $oldTabletops);
					$newTabletops = str_replace($tabletop_name_2, "", $newTabletops);
					$sqlNewTabletops = "UPDATE gi_kitchen SET tabletops = '$newTabletops' WHERE tabletops = '$oldTabletops'";
					$resultNewTabletops = $wpdb->get_results($sqlNewTabletops);
				}
				
				//УДАЛЯЕМ
				echo "Выбранные столешницы удалены";
				echo "<script>window.location.reload();</script>";
				$sqlDeleteTabletop = "DELETE FROM gi_tabletop WHERE newid='$tabletopForDelete'";
				$resultDeleteTabletop= $wpdb->get_results($sqlDeleteTabletop); 
				unlink ($image_delete);
				unlink ($thumb_delete);
		}
	}
		
		$c.= "<div style='clear:both;'></div><br><br>";
		$c.="<input type='submit' name='delete_tabletop' value='Удалить'/></form>";
		echo $c;
}
//ДОБАВЛЕНИЕ РУЧЕК
function add_handle(){
	echo "<h1>Добавить ручки:</h1>";
	$phpself = $_SERVER['PHP_SELF'];
	$rus=array(',', '(', ')', ' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('', '', '', '_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	global $wpdb;
	?>
	<form method='POST' enctype='multipart/form-data'>
		Введите название ручки (номер):<br>
		<input name='handlename' required><br>
		Загрузите фото ручки:<br>
		<input type='file' name='handleimage' accept="image/*"><br><br>
		<input type='submit' name='load'>
	</form>
	<?php
	//ЗАГРУЗКА КАРТИНОК + СОЗДАНИЕ МИНИАТЮР
	if(!empty($_POST['load'])){
		if(!empty($_FILES['handleimage']) and $_FILES['handleimage']['name']==true){
			$imagename = $_FILES['handleimage']['name'];
			$imagename = str_replace($rus, $lat, $imagename);
			$linkImage = "/wp-content/plugins/bp_kitchen_add/images/handle/base_images/$imagename";
			$linkThumbs = "/wp-content/plugins/bp_kitchen_add/images/handle/thumbs/$imagename";
			$tmp = $_FILES['handleimage']['tmp_name'];
			$path_parts  = pathinfo($_FILES['handleimage']['name']);
			$extension = $path_parts['extension'];	
			if ($extension == "png" or $extension=="jpg" or $extension=="jpeg"){
				move_uploaded_file ($tmp, "../$linkImage");
				$hrefimage="../$linkImage";
				$hrefthumb="../$linkThumbs";
				//задаем размеры миниатюрам
				list($width, $height) = getimagesize($hrefimage);
				$otnosh = $width / $height;
				$newWidth = 130;
				$newHeight = $newWidth / $otnosh;
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				switch($extension){
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
				echo "Данные успешно загружены";
				echo "<script>window.location.reload();</script>";

				$handlename=$_POST['handlename'];
				$handlename_eng = str_replace($rus, $lat, $handlename);
				$timestamp = time();
				$sql = "INSERT INTO `gi_handle` (`model_name`, `model_name_eng`, `image_href`, `thumb_href`, `timestamp`) VALUES ('$handlename', '$handlename_eng', '$linkImage', '$linkThumbs', '$timestamp');";
				$result = $wpdb->get_results($sql);
			}
			else {
				echo "Вы попытались загрузить неподходящее изображение<br>";
			}
		}
		else echo "Не загружено изображеие";
	}
	echo "<br><br><h2>Ручки в наличии:</h2>";
	$sqlEcho = "SELECT `model_name`, `image_href`, `thumb_href`, `newid` FROM `gi_handle` ORDER BY `rank_handle` ASC";
	$resultEcho = $wpdb->get_results($sqlEcho);	
	$b ="<form method='POST'>";
	$b.="<div style='width:100%; text-align:center; position:relative;'>";
	foreach ($resultEcho as $rowEcho) {
		$modelname = $rowEcho->model_name;
		$imagehref = $rowEcho->image_href;
		$thumbhref = $rowEcho->thumb_href;
		$newid = $rowEcho -> newid;
		$b.= "
		<div class='kitchen_point' style='margin:5px; display:inline-block; width:130px; position:relative;'>
		<input type='checkbox' name='$newid' value='$newid' style='position:absolute; top:5px; left:5px;'/>
		<img src='$imagehref' style='max-width:100%;'/>
		<h2>$modelname</h2>
		</div>
		";
		
		if (isset($_POST['delete_handle']) and isset($_POST[$newid])){
			$handleForDelete = $_POST[$newid];
			$image_delete = ".." . $imagehref;
			$thumb_delete = ".." . $thumbhref;
			//Заменяем в таблице кухонь ручки (вырезаем удаленные)
			$sqlOldHandles = "SELECT handles FROM gi_kitchen WHERE handles LIKE '%$modelname%'";
			$resultOldHandles= $wpdb->get_results($sqlOldHandles); 
			foreach ($resultOldHandles as $rowOldHandles){
				$oldHandles = $rowOldHandles -> handles;
				$handle_name_1 = $modelname . ",";
				$handle_name_2 = $modelname;
				$newHandles = str_replace($handle_name_1, "", $oldHandles);
				$newHandles = str_replace($handle_name_2, "", $newHandles);
				$sqlNewHandles = "UPDATE gi_kitchen SET handles = '$newHandles' WHERE handles = '$oldHandles'";
				$resultNewHandles = $wpdb->get_results($sqlNewHandles);
			}
				
			//УДАЛЯЕМ
			echo "Выбранные ручки удалены";
			echo "<script>window.location.reload();</script>";
			$sqlDeleteHandle = "DELETE FROM gi_handle WHERE newid='$handleForDelete'";
			$resultDeleteHandle= $wpdb->get_results($sqlDeleteHandle); 
			unlink ($image_delete);
			unlink ($thumb_delete);
		}
		
	}
	$b.="
	</div>
	<input type='submit' value='Удалить' name='delete_handle'/>
	</form>
	";
	echo $b;
}
function instructions(){
	$a ="
	<h1>Инструкции по добавлению кухонь</h1>
	Все пункты подлежат дальнейшему редактированию.<br><br>
	<b>Для того, что бы добавить кухню, необходимо: </b><br><br>
	1) Должны быть добавлены столешницы<br>
	2) Должны быть добавлены ручки<br>
	3) В разделе ''Добавить кухню'' заполнить все необходимые поля.<br>
	4) В разделе ''Добавить фасады'' загрузить изображения фасадов.<br>
	5) В разделе ''Добавить фасады'' заполнить названия и описания ранее добавленных фасадов.<br>
	6) В разделе ''Каталог кухонь'' появится новая кухня. Ярлык - адрес страницы сайта. Перейти в раздел админки ''Страницы'' и создать новую страницу с названием новой кухни. Нажать ''Опубликовать''.<br>
	7) В строке ''Постоянная ссылка'' нажать на кнопку ''изменить''  и прописать туда ярлык кухни.<br>
	8) На странице кухни вставить шорткод [kitchen_print]<br><bR><br>
	
	<b>Для того, что бы добавить столешницы, необходимо: </b><br><br>
	1) Загрузить изобажения столешниц.<br>
	2) Заполнить необходимые поля: название, описание.<br><br><br>
	
	<b>Для того, что бы добавить ручки, необходимо: </b><br><br>
	1) Прописть название ручки<br>
	2) Добавить изображение ручки.<br>
	<i>Ручки можно добавлять только по 1шт.</i>
	";
	echo $a;
}


/*РЕДАКТИРОВАНИЕ*/

//$pageRedact = plugins_url() . "bp_kitchen_add/redact.php";
//include ($pageRedact);

function redact_kitchen(){
	
	echo "<h1>Редактировать кухни:</h1>";
	global $wpdb;
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');
	//СЮДА ФАСАДЫ В ВИДЕ ВЫПАДАЮЩИХ ВКЛАДОК С ВОЗМОЖНОСТЬЮ УДАЛЕНИЯ (ТАБЫ) http://shpargalkablog.ru/2012/03/css-tabs.html#tab1 -------------- заморочился
	$c ="<div class='korpus'>";
	$d ="<style>
		.korpus {position:relative;}
		.korpus label {position:relative; top:0;}
		.korpus > div, .korpus > input { display: none; }
		.korpus label { padding: 5px; border: 1px solid #aaa; line-height: 28px; cursor: pointer; position: relative; bottom: 1px; background: #fff; }
		.korpus input[type='radio']:checked + label {background:#98FB98;}";
		
		
	$sqlKitchen = "SELECT * FROM gi_kitchen";
	$resultKitchen = $wpdb->get_results($sqlKitchen);
	//$kitchen_count = count($resultKitchen);
	$i=1;
	foreach ($resultKitchen as $rowKitchen) {
		$kitchenId = $rowKitchen->newid;

		$name = $rowKitchen->name;
		$name_eng = $rowKitchen->name_eng;
		$description = $rowKitchen->description;
		$avatar = $rowKitchen->avatar;
		
		$avatar_massiv = $rowKitchen->avatar_massiv;
		$avatar_shpon = $rowKitchen->avatar_shpon;
		$avatar_plastik = $rowKitchen->avatar_plastik;
		$avatar_akril = $rowKitchen->avatar_akril;
		$avatar_steklo = $rowKitchen->avatar_steklo;
		$avatar_dsp = $rowKitchen->avatar_dsp;
		
		$folder = $rowKitchen->folder;
		$plastic = $rowKitchen->plastic;
		$wood = $rowKitchen->wood;
		$cost = $rowKitchen->basic_cost;
		$material = $rowKitchen->material;
		$style = $rowKitchen->style;
		$tabletops = $rowKitchen->tabletops;
		$handles = $rowKitchen->handles;
		$furnitura = $rowKitchen->furnitura;
		$newid = $rowKitchen->newid;
		$similar = $rowKitchen->similar;
		$video = $rowKitchen->video;
		$public = $rowKitchen->public === 'yes' ? 'checked' : '';

		if ($i==1) $checked = "checked"; else $checked = "";
		$d.=".korpus > input:nth-of-type($i):checked ~ div:nth-of-type($i){display: block; padding: 5px; border: 1px solid #aaa;}";
		$c.="<input type='radio' name='tab' id='vk$i' $checked/><label for='vk$i'>$name</label>
		<div style='position: absolute; top:60px; width:95%; background:white; margin-bottom:30px;'>
		<form method='POST' enctype='multipart/form-data'>";



		$c.="
		<div style='float:left; margin:0 15px 15px 0; word-wrap:break-word;'>
			<div style='width:400px; height:280px;  background:url(..$avatar) no-repeat; background-position:center; background-size:cover;'></div>
			<br> Загрузите новую обложку:<br>
			<input type='file' name='newAvatar'>
		</div>
		
		<div style='margin:10px; overflow:hidden; vertical-align:top; '>
			<b>Опубликовать кухню: </b><input type='checkbox' name='newPublic' value='yes' $public><br>
			<b>Название:</b><br><textarea style='width:100%; height:30px;' name='newName' required>$name</textarea><br>
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
		$sqlMaterial = "SELECT * FROM gi_kitchen_material";
		$resultMaterial = $wpdb->get_results($sqlMaterial);
		foreach($resultMaterial as $rowMaterial){
			$allMaterials = $rowMaterial->material;
			$kitchenMaterials = explode(', ', $material);
			$check = in_array($allMaterials, $kitchenMaterials);
			$checked = $check ? 'checked' : '';
			$c.="<input type='checkbox' name='newmaterials[]' value='$allMaterials' $checked> $allMaterials";
			//проверяем содержится ли в базе кухонь список материалов со значением материала из базы материалов - как-то так :)
		}
		
		//РЕДАКТИОВАНИЕ СТИЛЕЙ
		$c.="<h3>Укажите стили кухни</h3>";
		$sqlStyles = "SELECT * FROM gi_kitchen_filtres WHERE category = 'style'";
		$resultStyles = $wpdb->get_results($sqlStyles);
		foreach($resultStyles as $rowStyles){
			$allStyles = $rowStyles->filtervalue;
			$findStyle = stripos($style, $allStyles);
			//проверяем содержится ли в базе кухонь список материалов со значением материала из базы материалов - как-то так :)
			if ($findStyle !== FALSE){
				$checked =  "checked";
				$c.="<input type='checkbox' name='newstyles[]' value='$allStyles' $checked> $allStyles ";
			}
			else $c.="<input type='checkbox' name='newstyles[]' value='$allStyles'> $allStyles ";
		}
		
		//РЕДАКТИРОВАНИЕ СТОЛЕШНИЦ
		$c.="<br><br><b>Укажите столешницы:</b><br>";
		$sqlTabletops = "SELECT * FROM gi_tabletop ORDER BY number";
		$resultTabletops = $wpdb->get_results($sqlTabletops);
		foreach($resultTabletops as $rowTabletops){
			$allTabletops = $rowTabletops->tabletop_name;
			$tabletopImage = $rowTabletops->thumb_link;
			$findTabletop = stripos($tabletops, $allTabletops);
			if ($findTabletop !== FALSE){
				$checkedTabletop =  "checked";
				$c.="
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:105px; background:url(..$tabletopImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newtabletops[]' value='$allTabletops' $checkedTabletop>
					</div>
					 <div style='width:105px;'>$allTabletops</div>
				 </div>
				";
			}
			else $c.="
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:105px; background:url(..$tabletopImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newtabletops[]' value='$allTabletops'>
					</div>
					 <div style='width:105px;'>$allTabletops</div>
				 </div>
			";
		}

		
		//РЕДАКТИРОВАНИЕ РУЧЕК
		$c.="<br><br><b>Укажите ручки:</b><br>";
		$sqlHandle = "SELECT * FROM gi_handle ORDER BY `rank_handle` ASC";
		$resultHandles = $wpdb->get_results($sqlHandle);
		foreach($resultHandles as $rowHandles){
			$allHandles = $rowHandles->model_name;
			$handleImage = $rowHandles->thumb_href;
			$findHandle = stripos($handles, $allHandles);
			if ($findHandle !== FALSE){
				$checkedHandle =  "checked";
				$c.="
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:75px; background:url(..$handleImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newhandles[]' value='$allHandles' $checkedHandle>
					</div>
					 <div style='width:105px;'>$allHandles</div>
				 </div>
				";
			}
			else $c.="
				<div style='display:inline-block; position:relative; vertical-align:top; margin:5px;'>
					<div style='position:relative;  width:105px; height:75px; background:url(..$handleImage) no-repeat; background-size:cover; background-position:center; '>
						<input style='position:relative; top:10px; left:5px;' type='checkbox' name='newhandles[]' value='$allHandles'>
					</div>
					 <div style='width:105px;'>$allHandles</div>
				 </div>
			";
		}		
		
		//ФАСАДЫ
		$c.='<br><h2>Внимание! Фасады редактируются в разделе фасадов</h2><br>';
		
		//АВАТАРКИ В ЗАВИСИМОСТИ ОТ МАТЕРИАЛА
		$c.="<h1>Аватарки кухни по материалам:</h1>";
		//Задаем картинку "нет картинки"
		$templatedir = get_template_directory_uri();
		$noimage = $templatedir . "/images/no-image.png";
		$trashimage = $templatedir . "/images/trash.png";
		//Делаем переменную с echo, чтобы не вносило в БД потом no-imange.png
		if(empty($avatar_massiv)) $echo_avatar_massiv = $noimage; else $echo_avatar_massiv = $avatar_massiv;
		if(empty($avatar_shpon)) $echo_avatar_shpon = $noimage; else $echo_avatar_shpon = $avatar_shpon;
		if(empty($avatar_plastik)) $echo_avatar_plastik = $noimage; else $echo_avatar_plastik = $avatar_plastik;
		if(empty($avatar_akril)) $echo_avatar_akril = $noimage; else $echo_avatar_akril = $avatar_akril;
		if(empty($avatar_steklo)) $echo_avatar_steklo = $noimage; else $echo_avatar_steklo = $avatar_steklo;
		if(empty($avatar_dsp)) $echo_avatar_dsp = $noimage; else $echo_avatar_dsp = $avatar_dsp;
		//Задаем уникальные названия кнопок удаления материалов.
		$delete_avatar_massiv = "delete_avatar_massiv_" . $name_eng;
		$delete_avatar_shpon = "delete_avatar_shpon_" . $name_eng;
		$delete_avatar_plastik = "delete_avatar_plastik_" . $name_eng;
		$delete_avatar_akril = "delete_avatar_akril_" . $name_eng;
		$delete_avatar_steklo = "delete_avatar_steklo_" . $name_eng;
		$delete_avatar_dsp = "delete_avatar_dsp_" . $name_eng;
		
		
		$c.="
		
		<div style='float:left; margin:10px 0; word-wrap:break-word; position:relative;'>
			<div style='width:240px; height:180px; background:url($echo_avatar_massiv) no-repeat; background-position:center; background-size:cover;'></div>
			<br><b>''АВАТАР МАССИВ ДЕРЕВА''</b>
			<input type='submit' value='' name='$delete_avatar_massiv' style='cursor:pointer; width:25px; position:absolute; right:25px; background-image:url($trashimage); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; background-color:transparent;'/><br>
			<br>
			<i><b>Загрузить новый / заменить:</i></b><br>
			<input type='file' name='avatar_massiv'>
		</div>
		
		<div style='float:left; margin:10px 0; word-wrap:break-word; position:relative;'>
			<div style='width:240px; height:180px; background:url($echo_avatar_shpon) no-repeat; background-position:center; background-size:cover;'></div>
			<br><b>''АВАТАР ШПОН''</b>
			<input type='submit' value='' name='$delete_avatar_shpon' style='cursor:pointer; width:25px; position:absolute; right:25px; background-image:url($trashimage); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; background-color:transparent;'/><br>
			<br>
			<i><b>Загрузить новый / заменить:</i></b><br>
			<input type='file' name='avatar_shpon'>
		</div>
		
		<div style='float:left; margin:10px 0; word-wrap:break-word; position:relative;'>
			<div style='width:240px; height:180px; background:url($echo_avatar_plastik) no-repeat; background-position:center; background-size:cover;'></div>
			<br><b>''АВАТАР ПЛАСТИК''</b>
			<input type='submit' value='' name='$delete_avatar_plastik' style='cursor:pointer; width:25px; position:absolute; right:25px; background-image:url($trashimage); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; background-color:transparent;'/><br>
			<br>
			<i><b>Загрузить новый / заменить:</i></b><br>
			<input type='file' name='avatar_plastik'>
		</div>

		<div style='float:left; margin:10px 0; word-wrap:break-word; position:relative;'>
			<div style='width:240px; height:180px; background:url($echo_avatar_akril) no-repeat; background-position:center; background-size:cover;'></div>
			<br><b>''АВАТАР АКРИЛ''</b>
			<input type='submit' value='' name='$delete_avatar_akril' style='cursor:pointer; width:25px; position:absolute; right:25px; background-image:url($trashimage); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; background-color:transparent;'/><br>
			<br>
			<i><b>Загрузить новый / заменить:</i></b><br>
			<input type='file' name='avatar_akril'>
		</div>

		<div style='float:left; margin:10px 0; word-wrap:break-word; position:relative;'>
			<div style='width:240px; height:180px; background:url($echo_avatar_steklo) no-repeat; background-position:center; background-size:cover;'></div>
			<br><b>''АВАТАР СТЕКЛО''</b>
			<input type='submit' value='' name='$delete_avatar_steklo' style='cursor:pointer; width:25px; position:absolute; right:25px; background-image:url($trashimage); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; background-color:transparent;'/><br>
			<br>
			<i><b>Загрузить новый / заменить:</i></b><br>
			<input type='file' name='avatar_steklo'>
		</div>

		<div style='float:left; margin:10px 0; word-wrap:break-word; position:relative;'>
			<div style='width:240px; height:180px; background:url($echo_avatar_dsp) no-repeat; background-position:center; background-size:cover;'></div>
			<br><b>''АВАТАР ДСП'' <img src=''></b>
			<input type='submit' value='' name='$delete_avatar_dsp' style='cursor:pointer; width:25px; position:absolute; right:25px; background-image:url($trashimage); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; background-color:transparent;'/><br>
			<br>
			<i><b>Загрузить новый / заменить:</i></b><br>
			<input type='file' value='' name='avatar_dsp'>
		</div>
		
		<div style='clear:both'></div>
		";
		
		
		//УДАЛЕНИЕ АВАТАРОК МАТЕРИАЛОВ:
		if(isset($_POST[$delete_avatar_massiv])){
			unlink(".." . $avatar_massiv);
			$sql_delete_massiv_avatar = "UPDATE gi_kitchen SET avatar_massiv = '' WHERE name='$name'";
			$result_delete_massiv_avatar = $wpdb->get_results($sql_delete_massiv_avatar);
			echo "<script>window.location.reload();</script>";
		}
		if(isset($_POST[$delete_avatar_shpon])){
			unlink(".." . $avatar_shpon);
			$sql_delete_shpon_avatar = "UPDATE gi_kitchen SET avatar_shpon = '' WHERE name='$name'";
			$result_delete_shpon_avatar = $wpdb->get_results($sql_delete_shpon_avatar);
			echo "<script>window.location.reload();</script>";
		}				
		if(isset($_POST[$delete_avatar_plastik])){
			unlink(".." . $avatar_plastik);
			$sql_delete_plastik_avatar = "UPDATE gi_kitchen SET avatar_plastik = '' WHERE name='$name'";
			$result_delete_plastik_avatar = $wpdb->get_results($sql_delete_plastik_avatar);
			echo "<script>window.location.reload();</script>";
		}	
		if(isset($_POST[$delete_avatar_akril])){
			unlink(".." . $avatar_akril);
			$sql_delete_akril_avatar = "UPDATE gi_kitchen SET avatar_akril = '' WHERE name='$name'";
			$result_delete_akril_avatar = $wpdb->get_results($sql_delete_akril_avatar);
			echo "<script>window.location.reload();</script>";
		}	
		if(isset($_POST[$delete_avatar_steklo])){
			unlink(".." . $avatar_steklo);
			$sql_delete_steklo_avatar = "UPDATE gi_kitchen SET avatar_steklo = '' WHERE name='$name'";
			$result_delete_steklo_avatar = $wpdb->get_results($sql_delete_steklo_avatar);
			echo "<script>window.location.reload();</script>";
		}	
		if(isset($_POST[$delete_avatar_dsp])){
			unlink(".." . $avatar_dsp);
			$sql_delete_dsp_avatar = "UPDATE gi_kitchen SET avatar_dsp = '' WHERE name='$name'";
			$result_delete_dsp_avatar = $wpdb->get_results($sql_delete_dsp_avatar);
			echo "<script>window.location.reload();</script>";
		}	


		
		//ГАЛЕРЕЯ ......
		$c.="<br><h2>Удалить фотографии из галереи:</h2><br>";
		$sqlVisualisation = "SELECT * FROM gi_kitchen_visualisation WHERE id_kitchen = '$newid' ORDER BY rank_visual ASC";
		$resultVisualisation = $wpdb->get_results($sqlVisualisation);
		foreach ($resultVisualisation as $rowVisualisation){
			$idVisial = $rowVisualisation->id;
			$nameVisualisation = $rowVisualisation->name_visual;
			$urlImage = $rowVisualisation->url_image;
			$urlThumb = $rowVisualisation->url_thumb;
			$rankVisualisation = $rowVisualisation->rank_visual;
			$c.="
			<div style='position:relative; width:200px; display:inline-block;'>
				<div style='position:relative; width:200px; height:112px; background:url($urlThumb) no-repeat; background-position:center; background-size:cover; display:inline-block;'>
					<input type='checkbox' style='position:absolute; top:10px; left:5px;' name='deleteGallery[]' value='$idVisial'>
				</div>
				<input type='text' style='width:100%;' name='nameVisualisation[$idVisial]' value='$nameVisualisation'>
				<input type='text' name='rankVisualisation[$idVisial]' value='$rankVisualisation'>
			</div>
			";
		}
		bp_kitchen_ensure_dirs($name_eng);
		$directoryThumb = bp_kitchen_fs_path($name_eng) . 'gallery/thumbs/';
		$directoryImage = bp_kitchen_fs_path($name_eng) . 'gallery/images/';
		$c.="<br><h2>Добавить новые фотографии в галерею:</h2><br>
		<input type='file' name='new_gallery[]' multiple /><Br>
		";
		$c.="<br><h2>Похожие модели кухонь:</h2><br>
				<div>
		";
		foreach ($resultKitchen as $rowKitchen) {
		
			$similarName = $rowKitchen->name;
			$avatarSimilar = $rowKitchen->avatar;
			$findSimilar = stripos($similar, $similarName);
			if ($findSimilar !== FALSE){
				$checkedkitchen =  "checked";
				$c.="
				<div style='float:left; margin-right: 10px; margin-bottom: 10px;'><div style='width:200px; height:140px;  background:url(..$avatarSimilar) no-repeat; background-position:center; background-size:cover;'>
					<input style='position:relative; top:10px; left:5px;' type='checkbox' name='similar[]' value='$similarName' $checkedkitchen>
				</div>$similarName</div>";
			} else {
				$c.="
				<div style='float:left; margin-right: 10px; margin-bottom: 10px;'><div style='width:200px; height:140px;  background:url(..$avatarSimilar) no-repeat; background-position:center; background-size:cover;'>
					<input style='position:relative; top:10px; left:5px;' type='checkbox' name='similar[]' value='$similarName'>
				</div>$similarName</div>";

			}

		}
		$c.="<div style='clear: both;'></div></div>";
	
		
		$redact = "redact".$name_eng;
		$c.="<br><br><button type='submit' name='$redact' value='$name'>Редактировать кухню $name</button>";
		$c.="
		</form></div>";
		$i++;
		//ВНЕСЕНИЕ ИЗМЕНЕНИЙ В БАЗУ
		if(isset($_POST[$redact])){
			//var_dump($_POST);
			if(!empty($_POST)){
				$newName = $_POST['newName'];
				//$newNameEng = str_replace($rus, $lat, $newName);
				$newDescr = $_POST['newDescr'];
				$newWood = $_POST['newWood'];
				$newPlastic = $_POST['newPlastic'];
				$newCost = $_POST['newCost'];
				$newFurnitura = $_POST['newFurnitura'];
				$newVideo = $_POST['newVideo'];
				$newPublic = $_POST['newPublic'];
			}
			if (!empty($_POST['newhandles'])){
				$newhandles = implode ("," ,$_POST['newhandles']);
			}
			if (!empty($_POST['newtabletops'])){
				$newtabletops = implode ("," ,$_POST['newtabletops']);
			}
			if (!empty($_POST['newmaterials'])){
				$newmaterials = implode (", " ,$_POST['newmaterials']);
			}
			if (!empty($_POST['newstyles'])){
				$newstyles = implode (", " ,$_POST['newstyles']);
			}
			if (!empty($_POST['newstyles'])){
				$newstyles = implode (", " ,$_POST['newstyles']);
			}
			if (!empty($_POST['similar'])){
				$newSimilar = implode ("," ,$_POST['similar']);
			}	
			//АВАТАР
			if (!empty($_FILES['newAvatar']) and $_FILES['newAvatar']['name']==true){
				if(is_uploaded_file($_FILES["newAvatar"]["tmp_name"])){		
					$new_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/".$_FILES['newAvatar']['name'];
					$new_avatarlink = str_replace($rus, $lat, $new_avatarlink);
					$tmp_new_avatar = $_FILES['newAvatar']['tmp_name'];
					$path_parts_new_avatar  = pathinfo($_FILES['newAvatar']['name']);
					$extension_new_avatar = $path_parts_new_avatar['extension'];
					$new_upload_link = "..$new_avatarlink";
					$unlinkAvatar = ".." . $avatar;
					if ($extension_new_avatar == "png" or $extension_new_avatar=="jpg" or $extension_new_avatar=="jpeg"){
						unlink($unlinkAvatar);
						move_uploaded_file ($tmp_new_avatar, $new_upload_link);
					}
				}
			}
			else $new_avatarlink = $avatar;

			//ГРУЗИМ АВАТАРКИ ПО ТИПУ МАТЕРИАЛА
			//МАССИВ ДЕРЕВА
			if (!empty($_FILES['avatar_massiv']) and $_FILES['avatar_massiv']['name']==true){
				if(is_uploaded_file($_FILES["avatar_massiv"]["tmp_name"])){		
					$massiv_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/avatar_massiv";				
					$tmp_massiv_avatar = $_FILES['avatar_massiv']['tmp_name'];
					$path_parts_massiv_avatar  = pathinfo($_FILES['avatar_massiv']['name']);
					$extension_massiv_avatar = $path_parts_massiv_avatar['extension'];
					$massiv_avatarlink = $massiv_avatarlink . "." . $extension_massiv_avatar;
					$massiv_upload_link = "..$massiv_avatarlink";
					$unlinkMassivAvatar = ".." . $avatar_massiv;
					if ($extension_massiv_avatar == "png" or $extension_massiv_avatar=="jpg" or $extension_massiv_avatar=="jpeg" or $extension_massiv_avatar=="JPG"){
						unlink($unlinkMassivAvatar);
						move_uploaded_file ($tmp_massiv_avatar, $massiv_upload_link);
					}
				}
			}
			else $massiv_avatarlink = $avatar_massiv;
			//ШПОН
			if (!empty($_FILES['avatar_shpon']) and $_FILES['avatar_shpon']['name']==true){
				if(is_uploaded_file($_FILES["avatar_shpon"]["tmp_name"])){		
					$shpon_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/avatar_shpon";				
					$tmp_shpon_avatar = $_FILES['avatar_shpon']['tmp_name'];
					$path_parts_shpon_avatar  = pathinfo($_FILES['avatar_shpon']['name']);
					$extension_shpon_avatar = $path_parts_shpon_avatar['extension'];
					$shpon_avatarlink = $shpon_avatarlink . "." . $extension_shpon_avatar;
					$shpon_upload_link = "..$shpon_avatarlink";
					$unlinkshponAvatar = ".." . $avatar_shpon;
					if ($extension_shpon_avatar == "png" or $extension_shpon_avatar=="jpg" or $extension_shpon_avatar=="jpeg" or $extension_shpon_avatar=="JPG"){
						unlink($unlinkshponAvatar);
						move_uploaded_file ($tmp_shpon_avatar, $shpon_upload_link);
					}
				}
			}
			else $shpon_avatarlink = $avatar_shpon;
			//ПЛАСТИК
			if (!empty($_FILES['avatar_plastik']) and $_FILES['avatar_plastik']['name']==true){
				if(is_uploaded_file($_FILES["avatar_plastik"]["tmp_name"])){		
					$plastik_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/avatar_plastik";				
					$tmp_plastik_avatar = $_FILES['avatar_plastik']['tmp_name'];
					$path_parts_plastik_avatar  = pathinfo($_FILES['avatar_plastik']['name']);
					$extension_plastik_avatar = $path_parts_plastik_avatar['extension'];
					$plastik_avatarlink = $plastik_avatarlink . "." . $extension_plastik_avatar;
					$plastik_upload_link = "..$plastik_avatarlink";
					$unlinkplastikAvatar = ".." . $avatar_plastik;
					if ($extension_plastik_avatar == "png" or $extension_plastik_avatar=="jpg" or $extension_plastik_avatar=="jpeg" or $extension_plastik_avatar=="JPG"){
						unlink($unlinkplastikAvatar);
						move_uploaded_file ($tmp_plastik_avatar, $plastik_upload_link);
					}
				}
			}
			else $plastik_avatarlink = $avatar_plastik;
			//АКРИЛ
			if (!empty($_FILES['avatar_akril']) and $_FILES['avatar_akril']['name']==true){
				if(is_uploaded_file($_FILES["avatar_akril"]["tmp_name"])){		
					$akril_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/avatar_akril";				
					$tmp_akril_avatar = $_FILES['avatar_akril']['tmp_name'];
					$path_parts_akril_avatar  = pathinfo($_FILES['avatar_akril']['name']);
					$extension_akril_avatar = $path_parts_akril_avatar['extension'];
					$akril_avatarlink = $akril_avatarlink . "." . $extension_akril_avatar;
					$akril_upload_link = "..$akril_avatarlink";
					$unlinkakrilAvatar = ".." . $avatar_akril;
					if ($extension_akril_avatar == "png" or $extension_akril_avatar=="jpg" or $extension_akril_avatar=="jpeg" or $extension_akril_avatar=="JPG"){
						unlink($unlinkakrilAvatar);
						move_uploaded_file ($tmp_akril_avatar, $akril_upload_link);
					}
				}
			}
			else $akril_avatarlink = $avatar_akril;
			//СТЕКЛО
			if (!empty($_FILES['avatar_steklo']) and $_FILES['avatar_steklo']['name']==true){
				if(is_uploaded_file($_FILES["avatar_steklo"]["tmp_name"])){		
					$steklo_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/avatar_steklo";				
					$tmp_steklo_avatar = $_FILES['avatar_steklo']['tmp_name'];
					$path_parts_steklo_avatar  = pathinfo($_FILES['avatar_steklo']['name']);
					$extension_steklo_avatar = $path_parts_steklo_avatar['extension'];
					$steklo_avatarlink = $steklo_avatarlink . "." . $extension_steklo_avatar;
					$steklo_upload_link = "..$steklo_avatarlink";
					$unlinkstekloAvatar = ".." . $avatar_steklo;
					if ($extension_steklo_avatar == "png" or $extension_steklo_avatar=="jpg" or $extension_steklo_avatar=="jpeg" or $extension_steklo_avatar=="JPG"){
						unlink($unlinkstekloAvatar);
						move_uploaded_file ($tmp_steklo_avatar, $steklo_upload_link);
					}
				}
			}
			else $steklo_avatarlink = $avatar_steklo;
			//ДСП
			if (!empty($_FILES['avatar_dsp']) and $_FILES['avatar_dsp']['name']==true){
				if(is_uploaded_file($_FILES["avatar_dsp"]["tmp_name"])){		
					$dsp_avatarlink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_eng/avatar/avatar_dsp";				
					$tmp_dsp_avatar = $_FILES['avatar_dsp']['tmp_name'];
					$path_parts_dsp_avatar  = pathinfo($_FILES['avatar_dsp']['name']);
					$extension_dsp_avatar = $path_parts_dsp_avatar['extension'];
					$dsp_avatarlink = $dsp_avatarlink . "." . $extension_dsp_avatar;
					$dsp_upload_link = "..$dsp_avatarlink";
					$unlinkdspAvatar = ".." . $avatar_dsp;
					if ($extension_dsp_avatar == "png" or $extension_dsp_avatar=="jpg" or $extension_dsp_avatar=="jpeg" or $extension_dsp_avatar=="JPG"){
						unlink($unlinkdspAvatar);
						move_uploaded_file ($tmp_dsp_avatar, $dsp_upload_link);
					}
				}
			}
			else $dsp_avatarlink = $avatar_dsp;

			//РАБОТАЕМ С ГАЛЕРЕЕЙ
			if(!empty($_POST['gallery']) and $_POST['gallery'] == TRUE){
				foreach ($_POST['gallery'] as $numberImage => $deleteImage){
					unlink("$directoryThumb/$deleteImage");
					unlink("$directoryImage/$deleteImage");
				}
			}

			if(!empty($_POST['galleryOld']) && is_array($_POST['galleryOld'])){
				foreach ($_POST['galleryOld'] as $deleteImage){
					
					$nameFile = prepareVisiualName($deleteImage);
					$rankFile = getOldVisualRank($deleteImage);
					$urlImage = "$directoryImage/$deleteImage";
					$urlThumb = "$directoryThumb/$deleteImage";
					$sqlInsert = "INSERT INTO gi_kitchen_visualisation (id_kitchen, name_visual, url_image, url_thumb, rank_visual) VALUES ('$kitchenId', '$nameFile', '$urlImage', '$urlThumb', '$rankFile')";
					$resultInsert = $wpdb->query($sqlInsert);
				}
			}
						
			$deleteImage_1 = '';
			$deleteThumb_1 = '';
			$deleteImage_2 = '';
			$deleteThumb_2 = '';
			if(!empty($_POST['deleteGallery']) && is_array($_POST['deleteGallery'])){
				foreach ($_POST['deleteGallery'] as $deleteImage){
					$sqlSelect = "SELECT * FROM gi_kitchen_visualisation WHERE id = '$deleteImage'";
					$resultSelect = $wpdb->get_results($sqlSelect);
					foreach ($resultSelect as $rowSelect){
						$urlImage =  $rowSelect->url_image;
						$urlThumb = $rowSelect->url_thumb;
						$fsImage = bp_kitchen_url_to_fs($urlImage);
						$fsThumb = bp_kitchen_url_to_fs($urlThumb);
						if (file_exists($fsImage)){
							$deleteImage_1 = $fsImage;
							unlink($fsImage);
						} else {
							$deleteImage_1 = 'Нет изображения';
							$deleteImage_2 = $urlImage;
						}
						if (file_exists($fsThumb)){
							$deleteThumb_1 = $fsThumb;
							unlink($fsThumb);
						} else {
							$deleteThumb_1 = 'Нет миниатюры';
							$deleteThumb_2 = $urlThumb;
						}
					}
					$sqlDelete = "DELETE FROM gi_kitchen_visualisation WHERE id = '$deleteImage'";
					$resultDelete = $wpdb->query($sqlDelete);
				}
			}
			$resultUpdate = '';
			$resultUpdateNew = 'Тут будет ошибка';
			if (!empty($_POST['nameVisualisation']) && is_array($_POST['nameVisualisation'])){
				foreach ($_POST['nameVisualisation'] as $idVisualisation => $nameVisualisation){
					$sqlUpdate = "UPDATE gi_kitchen_visualisation SET name_visual = '$nameVisualisation' WHERE id = '$idVisualisation'";
					$resultUpdate = $wpdb->query($sqlUpdate);
					// if (!$resultUpdateNew){
					// 	$error = $wpdb->last_error;
					// 	$resultUpdate [] = $error;
					// }
				}
			}
			//РЕДАКТИРОВАНИЕ РАНГА ГАЛЕРЕИ
			if (!empty($_POST['rankVisualisation']) && is_array($_POST['rankVisualisation'])){
				foreach ($_POST['rankVisualisation'] as $idVisualisation => $rankVisualisation){
					$sqlUpdate = "UPDATE gi_kitchen_visualisation SET rank_visual = '$rankVisualisation' WHERE id = '$idVisualisation'";
					$resultUpdate = $wpdb->query($sqlUpdate);
					$resultUpdateNew = $sqlUpdate;
					if (!$resultUpdate){
						$error = $wpdb->last_error;
						$resultUpdate = $error;
					}
				}
			}
			
			$resultVisualisation = addGallery($_FILES['new_gallery'], $name_eng, $kitchenId);

			// if(!empty($_FILES['new_gallery']) and $_FILES['new_gallery']==TRUE){
			// 	foreach($_FILES['new_gallery']['name'] as $numberAddFile=>$nameAddFile){
			// 		$$numberAddFile = $nameAddFile; //надо 2 знака $
			// 	}
			// 	for ($i=0; $i<=$numberAddFile; $i++) {
			// 		$dirThumb = $folder . "gallery/thumbs/";
			// 		$dirImage = $folder . "gallery/images/";
					
			// 		$imagelink = $dirImage . $_FILES['new_gallery']['name'][$i];
			// 		$thumblink = $dirThumb . $_FILES['new_gallery']['name'][$i];
			// 		$imagelinkrus = $dirImage . $_FILES['new_gallery']['name'][$i];
					
			// 		//$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
			// 		//$thumblink = str_replace($rus, $lat, $thumblink);
			// 		$tmp = $_FILES['new_gallery']['tmp_name'][$i];
			// 		$path_parts  = pathinfo($_FILES['new_gallery']['name'][$i]);
			// 		$extension = $path_parts['extension'];
			// 		//так надо :)
			// 		$hrefimage=".." . $imagelink;
			// 		$hrefthumb=".." . $thumblink;
			// 		if ($extension == "png" or $extension=="jpg" or $extension=="jpeg"){
			// 			move_uploaded_file ($tmp, $hrefimage);

			// 		}
			// 		else {
			// 			//echo "Вы попытались загрузить неподходящее изображение<br>";
			// 			continue;
			// 		}
			// 		//задаем размеры миниатюрам
			// 		list($width, $height) = getimagesize($hrefimage);
			// 		$otnosh = $width / $height;
			// 		$newWidth = 200;
			// 		$newHeight = $newWidth / $otnosh;
			// 		$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
			// 		switch($extension){
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
			// }
	
			$sqlRedact = "UPDATE gi_kitchen SET video='$newVideo', style='$newstyles', avatar_dsp = '$dsp_avatarlink', avatar_steklo = '$steklo_avatarlink', avatar_akril = '$akril_avatarlink', avatar_plastik = '$plastik_avatarlink', avatar_shpon = '$shpon_avatarlink', avatar_massiv = '$massiv_avatarlink', avatar = '$new_avatarlink', name ='$newName', description='$newDescr', material='$newmaterials', wood='$newWood', plastic='$newPlastic', furnitura='$newFurnitura', basic_cost='$newCost', tabletops='$newtabletops', handles='$newhandles', public='$newPublic', similar='$newSimilar' WHERE name='$name'";
			echo "<br>Изменения в кухню $name внесены!";
			// echo $sqlRedact;
			// echo $_FILES['new_gallery']['name'][0];
			// echo $imagelink;
			$goRedact = $wpdb->query($sqlRedact);
			echo $resultUpdate;
			echo $resultUpdateNew;
			echo $deleteImage_1 . "<br>";
			echo $deleteThumb_1 . "<br>";
			echo $deleteImage_2 . "<br>";
			echo $deleteThumb_2 . "<br>";
			print_r($_POST['rankVisualisation'], true);
			echo "<script>window.location.reload();</script>";	
		}
	}
	$c.="</div>";
	echo $c;
	echo $d;
}


function redact_handles(){
	function handleOutputAdmin ($sql, $wpdb) {
		$resultEcho = $wpdb->get_results($sql);	
		$post ="<div style='width:100%; text-align:center; position:relative;'>";
		foreach ($resultEcho as $rowEcho) {
			$modelname = $rowEcho->model_name;
			$imagehref = $rowEcho->image_href;
			$thumbhref = $rowEcho->thumb_href;
			$rank =  $rowEcho->rank_handle;
			$newid = $rowEcho -> newid;
			$post.="";
			
			$post.= "
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
			if (isset($_POST['updateHandels'])){
				$handleForUpdate = $_POST[$newid];
				$newRank = $_POST['rank'];
				
				$sqlUpdateHandle = "UPDATE gi_handle SET rank_handle = '$newRank' WHERE newid='$handleForUpdate'";
				$resultUpdateHandle= $wpdb->get_results($sqlUpdateHandle); 
				echo "Выбранные ручки удалены";
				echo "<script>window.location.reload();</script>";
			}	
		}
		
		$post.="
		</div>
		";
		return $post;
	}
	
	global $wpdb;
	// Запрос для записей без "Gola" в названии
	$sqlWithoutGola = "SELECT `model_name`, `image_href`, `thumb_href`, `newid`, `rank_handle` FROM `gi_handle` WHERE `model_name` NOT LIKE '%Gola%' ORDER BY `rank_handle` ASC";

	// Запрос для записей с "Gola" в названии
	$sqlWithGola = "SELECT `model_name`, `image_href`, `thumb_href`, `newid`, `rank_handle` FROM `gi_handle` WHERE `model_name` LIKE '%Gola%' ORDER BY `rank_handle` ASC";

	echo "<h1>Редактировать ручки:</h1>";
	
	$phpself = $_SERVER['PHP_SELF'];
	
	if ($sqlWithoutGola) {
		echo "<br><br><h2>Ручки в наличии:</h2>";
		$b = handleOutputAdmin($sqlWithoutGola, $wpdb);
	}
	if ($sqlWithGola) {
		$b .= "<br><br><h2>Gola-профиль:</h2>";
		$b .= handleOutputAdmin($sqlWithGola, $wpdb);
	}
	
	echo $b;
}


function redact_tabletops(){
	echo "<h1>Редактировать столешницы:</h1>";
	echo "Данный раздел в разработке";
}


function redact_facades(){
	echo "<h1>Редактировать фасады:</h1>";
	echo "Данный раздел в разработке";
}

function prepareVisiualName($fileName){
	$nameFile = mb_strstr($fileName, '_');
	$nameFile = str_replace('_', ' ', $nameFile);
	$nameFile = str_replace('-', ' ', $nameFile);
	$nameFile = explode( '.', $nameFile);
	$nameFile = $nameFile[0];
	return $nameFile;
}
function getOldVisualRank($fileName){
	$nameFile = explode( '_', $fileName);
	$nameFile = $nameFile[0];
	return $nameFile;
}

/** Absolute filesystem path to a kitchen images folder (with trailing slash). */
function bp_kitchen_fs_path($name_kitchen_eng = '') {
	$base = plugin_dir_path(__FILE__) . 'images/kitchen/';
	if ($name_kitchen_eng === '') {
		return $base;
	}
	return $base . $name_kitchen_eng . '/';
}

/** Convert a site-relative URL (/wp-content/plugins/...) to an absolute filesystem path. */
function bp_kitchen_url_to_fs($url) {
	$url = '/' . ltrim(str_replace('\\', '/', $url), '/');
	$marker = '/wp-content/plugins/bp_kitchen_add/';
	$pos = strpos($url, $marker);
	if ($pos === false) {
		return ABSPATH . ltrim($url, '/');
	}
	$relative = substr($url, $pos + strlen($marker));
	return plugin_dir_path(__FILE__) . $relative;
}

/** Create kitchen image directories if they are missing. */
function bp_kitchen_ensure_dirs($name_kitchen_eng) {
	$images_dir = bp_kitchen_fs_path($name_kitchen_eng);
	$dirs = array(
		$images_dir,
		$images_dir . 'avatar/',
		$images_dir . 'gallery/',
		$images_dir . 'gallery/images/',
		$images_dir . 'gallery/thumbs/',
		$images_dir . 'facade/',
		$images_dir . 'facade/images/',
		$images_dir . 'facade/thumbs/',
	);
	foreach ($dirs as $dir) {
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}
	}
	return $images_dir;
}

function addGallery($filesArray, $name_kitchen_eng, $kitchenId = null){
	global $wpdb;
	
	if(!empty($filesArray) and $filesArray['name']==true && is_array($filesArray)){
		bp_kitchen_ensure_dirs($name_kitchen_eng);
		foreach($filesArray['name'] as $numberAddFile=>$nameAddFile){
			$$numberAddFile = $nameAddFile; //надо 2 знака $
		}
		$resultVisualisation = [];
		for ($i=0; $i<=$numberAddFile; $i++) {
			if (empty($filesArray['name'][$i]) || empty($filesArray['tmp_name'][$i])) {
				continue;
			}
			$fileName = basename($filesArray['name'][$i]);
			$imagelink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_kitchen_eng/gallery/images/" . $fileName;
			$thumblink = "/wp-content/plugins/bp_kitchen_add/images/kitchen/$name_kitchen_eng/gallery/thumbs/" . $fileName;
			$imagelink = replaceSymbols($imagelink);//Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
			$thumblink = replaceSymbols($thumblink);
			$tmp = $filesArray['tmp_name'][$i];
			$path_parts  = pathinfo($filesArray['name'][$i]);
			$extension = isset($path_parts['extension']) ? strtolower($path_parts['extension']) : '';
			$hrefimage = bp_kitchen_url_to_fs($imagelink);
			$hrefthumb = bp_kitchen_url_to_fs($thumblink);
			if ($extension == "png" or $extension=="jpg" or $extension=="jpeg"){
				$sql = "INSERT INTO `gi_kitchen_visualisation` (`id_kitchen`, `name_visual`, `url_image`, `url_thumb`, `rank_visual`) VALUES ('$kitchenId', '$fileName', '$imagelink', '$thumblink', '$i')";
				$resultVisualisation[$i] = $wpdb->query($sql);
				if ($resultVisualisation[$i]) {
					// echo "Фотография $fileName загружена в базу данных";
				} else {
					$resultVisualisation[$i] = $wpdb->last_error;
					// echo "Ошибка загрузки фотографии $fileName в базу данных";
				}
				if (!move_uploaded_file($tmp, $hrefimage)) {
					$resultVisualisation[$i] = 'Не удалось сохранить файл: ' . $hrefimage;
					continue;
				}
			} else {
				continue;
			}
			//задаем размеры миниатюрам
			$size = @getimagesize($hrefimage);
			if (!$size) {
				$resultVisualisation[$i] = 'Не удалось прочитать изображение: ' . $fileName;
				continue;
			}
			list($width, $height) = $size;
			$otnosh = $width && $height ? $width / $height : 1;
			$newWidth = 200;
			$newHeight = $newWidth / $otnosh;
			$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
			switch($extension){
				case "jpg":	//СОЗДАНИЕ JPG
				case "jpeg": //СОЗДАНИЕ JPG
					$image = @imagecreatefromjpeg($hrefimage); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
					if (!$image) {
						$resultVisualisation[$i] = 'Ошибка обработки JPG: ' . $fileName;
						break;
					}
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
					imagejpeg($image_p, $hrefthumb, 100);
					imagedestroy($image);
					break;
				case "png":	//СОЗДАНИЕ ПНГ
					$image = @imagecreatefrompng($hrefimage);
					if (!$image) {
						$resultVisualisation[$i] = 'Ошибка обработки PNG: ' . $fileName;
						break;
					}
					imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
					imagesavealpha($image_p, true); //Включаем сохранение альфа канала
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
					imagepng($image_p, $hrefthumb);
					imagedestroy($image);
			}
			imagedestroy($image_p);
		}
		return $resultVisualisation;
	}
}
?>