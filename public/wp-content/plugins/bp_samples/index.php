<?php
/*Plugin Name: bp_samples
Description: Образцы.
Version: 1.0
Author: Business Park*/
add_action('admin_menu', 'add_samples_page');
function add_samples_page() {
    add_menu_page('Образцы', 'Образцы', 'read', __FILE__, 'all_samples', 'dashicons-image-filter');
		add_submenu_page(__FILE__, 'Добавить', 'Добавить', 'read', 'add_sample', 'add_sample');
		add_submenu_page(__FILE__, 'Модерация', 'Модерация', 8, 'moderate', 'moderate');

}

add_shortcode('samples_print', 'samples_print');
add_shortcode('my_samples_print', 'my_samples_print');
add_shortcode('add_sample', 'my_add_sample');


function moderate(){
	echo "<h1>Запросы на модерацию</h1>";
	require_once("../wp-content/plugins/bp_samples/moderate.php");
	
}
function my_add_sample(){
	
	global $user_role;
	if($user_role == 'designer_architect'){
		header("Location: /designer-conditions/");
	}
	
	global $wpdb;
	global $lang_adm;
	
	$rus=array('"','(',')',' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('','','','_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');

	$a ="<div class='add_my_sample'>
	<h1>$lang_adm->ao_h1</h1>";
	$user= wp_get_current_user()->user_login;
	if($user != "admin"){
		$displayuserselect = "display:none;";
		$requireuserselect = "";
	}
	else {
		$requireuserselect = "required";
	}
	$a.="
	<form method='POST' enctype='multipart/form-data'>
		<select name='user' style='width:100%; margin:10px 0; $displayuserselect' $requireuserselect>
			<option selected disabled value=''>$lang_adm->ao_pl_1</option>
	";
	$sqluser = "SELECT user_login FROM wp_users ORDER BY user_nicename";
	$resultuser = $wpdb->get_results($sqluser);
	foreach ($resultuser as $rowuser){
		$username = $rowuser->user_login;
		$a.="<option value='$username'>$username</option>";
	}
	$a.="</select>
		<select name='kitchen' style='width:100%; margin:10px 0;' required>
			<option selected disabled value=''>$lang_adm->ao_pl_2</option>
	";
	$sqlkitchen = "SELECT name FROM gi_kitchen ORDER BY name";
	$resultkitchen = $wpdb->get_results($sqlkitchen);
	foreach ($resultkitchen as $rowkitchen){
		$kitchen = $rowkitchen->name;
		$a.="
		<option value='$kitchen'>$kitchen</option>
		";
	}
	$a.="
		</select><br>
		<select name='salon' style='width:100%; margin:10px 0;' required>
			<option selected disabled value=''>$lang_adm->ao_pl_3</option>
			
	";
	//Полная выборка только для админа
	if ($user == "admin"){
		$whereuser = "";
	}
	else {
		$whereuser = "AND user = '$user'";
	}
	$sqlsalon = "SELECT newid, country, city, address FROM gi_salons WHERE moderate = 'yes' $whereuser";
	$resultsalon = $wpdb->get_results($sqlsalon);
	foreach($resultsalon as $rowsalon){
		$newid = $rowsalon->newid;
		$country = $rowsalon->country;
		$city = $rowsalon->city;
		$address = $rowsalon->address;
		$a.="<option value='$newid'>$country, $city, $address</option>";
	}
	$a.="
		</select><br>
		<input name='size' placeholder='$lang_adm->ao_pl_4' style='width:100%; margin:10px 0;' required/><br>
		
		<textarea name='descr' placeholder='$lang_adm->ao_pl_5' style='width:100%; margin:10px 0;' required></textarea><br>
		
		<input name='cost' placeholder='$lang_adm->ao_pl_6' style='width:100%; margin:10px 0;' required/><br>
		
		<b>$lang_adm->ao_set_curr:</b> 
		<select name='currency'>
			<option value='$lang_adm->cur_bel_rub'>$lang_adm->cur_bel_rub</option>
			<option value='$lang_adm->cur_ros_rub'>$lang_adm->cur_ros_rub</option>
			<option value='$lang_adm->cur_grivna'>$lang_adm->cur_grivna</option>
			<option value='$lang_adm->cur_evro'>$lang_adm->cur_evro</option>
			<option value='$lang_adm->cur_dollar'>$lang_adm->cur_dollar</option>
			<option value='$lang_adm->cur_cron'>$lang_adm->cur_cron</option>
		</select><br>
		
		<input name='sales' type='number' placeholder='$lang_adm->mo_sales_proc' style='width:100%; margin:10px 0;' required/><br>

		
		$lang_adm->mo_add_images: <br> 
		<input class='my_sample_files_input' type='file' name='images[]' id='images' multiple required><br><br>
		<span id='outputMulti'></span><br><br>
	
		<button name='addsample' type='submit' class='positive_btn'>$lang_adm->ao_add_sample</button>
	</form>
	";
	$a.="</div>";

	$a.="
	<script>
	/*ПОДГРУЗКА ФОТОГРАФИЙ ПРИ ЗАГРУЗКЕ*/


		function handleFileSelectMulti(evt) {
		var files = evt.target.files; // FileList object
		document.getElementById('outputMulti').innerHTML = \"\";
		for (var i = 0, f; f = files[i]; i++) {
		  // Only process image files.
		  if (!f.type.match('image.*')) {
			alert('$lang_adm->ao_alert_message_2');
		  }
		  var reader = new FileReader();
		  // Closure to capture the file information.
		  reader.onload = (function(theFile) {
			return function(e) {
			  // Render thumbnail.
			  var span = document.createElement('span');
			  var imagename = theFile.name;
			  span.innerHTML = [\"<input type='radio' name='avatar' value='\", imagename, \"' required/>Аватар<br><img class='img-thumbnail' src='\", e.target.result,
								\"' title='\", escape(theFile.name), \"'/> <br>\"].join('');
			  document.getElementById('outputMulti').insertBefore(span, null);
			};
		  })(f);
		  // Read in the image file as a data URL.
		  reader.readAsDataURL(f);
		}
	  }
	  document.getElementById('images').addEventListener('change', handleFileSelectMulti, false);
	</script>
	";
	echo $a;
	
	if(isset($_POST['addsample'])){
		$kitchen = $_POST['kitchen'];
		$kitchen_eng = str_replace($rus, $lat, $kitchen);
		$salon_id = $_POST['salon'];
		$size = $_POST['size'];
		$descr = $_POST['descr'];
		$cost = $_POST['cost'];
		$sales = $_POST['sales'];
		$currency = $_POST['currency'];
		$avatar = $_POST['avatar'];
		//Заново получаем адрес салона
		$sqladdress = "SELECT country, city, address FROM gi_salons WHERE newid = '$salon_id'";
		$resultaddress = $wpdb->get_results($sqladdress);
		foreach ($resultaddress as $rowaddress){
			$country = $rowaddress->country;
			$city = $rowaddress->city;
			$address = $rowaddress->address;
			$fulladdress = $country . ", " . $city . ", " . $address;
		}
		$images_dir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_samples/images/samples";
		$dirname = $salon_id . "_" . $kitchen_eng;
		$imagesname_data = date("dmY");

		if(!empty($_FILES['images']) and $_FILES['images']==TRUE){
			if (!file_exists($images_dir)) {
				mkdir($images_dir);
				mkdir("$images_dir/$dirname/");
				mkdir("$images_dir/$dirname/images");
				mkdir("$images_dir/$dirname/thumbs");
			}
			else {
				if(!file_exists("$images_dir/$dirname/")){
					mkdir("$images_dir/$dirname/");
					mkdir("$images_dir/$dirname/images");
					mkdir("$images_dir/$dirname/thumbs");
				}
			}
			
			foreach($_FILES['images']['name'] as $numberAddFile=>$nameAddFile){
				$$numberAddFile = $nameAddFile; //надо 2 знака $
			}			
			for ($i=0; $i<=$numberAddFile; $i++) {
				$images_folder = "$images_dir/$dirname/images/";
				$thumbs_folder = "$images_dir/$dirname/thumbs/";
				$imagelink = $images_folder .  $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$thumblink = $thumbs_folder . $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];				
				$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
				$thumblink = str_replace($rus, $lat, $thumblink);
				$tmp = $_FILES['images']['tmp_name'][$i];
				$path_parts  = pathinfo($_FILES['images']['name'][$i]);
				$extension = $path_parts['extension'];

				//задаем размеры миниатюрам и большим картинкам
				list($width, $height) = getimagesize($tmp);
				$otnosh = $width / $height;
				if ($otnosh >= 1){
					$thumb_width = 600;
					$image_width = 1300;
				}
				else{
					$thumb_width = 400;
					$image_width = 800;					
				}
				$thumb_height = $thumb_width / $otnosh;
				$image_height = $image_width / $otnosh;
				$image_p = imagecreatetruecolor($thumb_width, $thumb_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ МИНИАТЮРЫ
				$image_p2 = imagecreatetruecolor($image_width, $image_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ БОЛЬШОЙ КАРТИНКИ
				switch($extension){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
						imagepng($image_p, $thumblink);
						imagepng($image_p2, $imagelink);
						break; //Раньше брейка не было, ничего не поломал?????
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
						imagepng($image_p, $thumblink);
						imagepng($image_p2, $imagelink);
						break;
				}
				//имена картинок для внесения в БД
				$image_name = $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$thumb_name = $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$images_db .="$image_name;";
				$thumbs_db .="$thumb_name;";	
				$folder_db = "wp-content/plugins/bp_samples/images/samples/$dirname";
				$cur_user = wp_get_current_user()->user_login;
				if(isset($_POST['user']) and $cur_user == "admin"){
					$username = $_POST['user'];					
				}
				else {
					$username = wp_get_current_user()->user_login;;
				}
			}
			
			$avatar = $salon_id . "_" . $imagesname_data . "_" . $avatar;
			$avatar = str_replace($rus, $lat, $avatar);
			$images_db = str_replace($rus, $lat, $images_db);
			$thumbs_db = str_replace($rus, $lat, $thumbs_db);
			//удаляем аватар из списка картинок
			$images_db = str_replace($avatar . ";", "", $images_db);
			$thumbs_db = str_replace($avatar . ";", "", $thumbs_db);
			$sql="INSERT INTO `gi_samples` 
				(`name`, `salon_id`, `city`, `salon_address`, `description`, `size`, `cost`, `currency`, `sales`, `avatar`, `images`, `thumbs`, `folder`, `user`, `moderate`) 
			VALUES
				('$kitchen', '$salon_id', '$city', '$fulladdress', '$descr', '$size', '$cost', '$currency', '$sales', '$avatar', '$images_db', '$thumbs_db', '$folder_db', '$username', 'no');";
			$result = $wpdb->get_results($sql);	
		
			$message = "На модерацию поступил образец. Проверьте админку сайта gi.by";
			$subject = "Модерация образцов";
			mail('info@gi.by', $subject, $message);
			
			echo "<script>alert('$lang_adm->ao_alert_message_1');</script>";
		}
	}
}
function my_samples_print(){
	
	global $user_role;
	if($user_role == 'designer_architect'){
		header("Location: /designer-conditions/");
	}
	
	$rus=array('"','(',')',' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('','','','_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');

	global $wpdb;
	global $lang_adm;
	
	$a ="
	<div style='float:left;'>
		<h1>$lang_adm->mo_h1</h1>
	</div>
	<div class='addsalonandsample'>
		<a href='/add-sample/'><button class='positive_btn'>$lang_adm->mo_add_sample</button></a>
	</div>
	<div style='clear:both'></div>
	";
	$user= wp_get_current_user()->user_login;
	//$user = "shef_kuhni_Minsk";
	$sqlsalons = "SELECT newid, name, country, city, address FROM gi_salons WHERE user = '$user'";
	$resultsalons = $wpdb->get_results($sqlsalons);
	foreach($resultsalons as $rowsalons){
		$salon_id = $rowsalons -> newid;
		$salon_name = $rowsalons -> name;
		$salon_country = $rowsalons -> country;
		$salon_city = $rowsalons -> city;
		$salon_address = $rowsalons -> address;
		$sqlsamples = "SELECT * FROM gi_samples WHERE salon_id = '$salon_id'";	
		$resultsamples = $wpdb->get_results($sqlsamples);
		if(count($resultsamples) !=0){
		$b="
			<div class='header_samples' style='margin:20px 0;'>
				<h3>$salon_name</h3>
				<span><i>$salon_country, $salon_city, $salon_address</i></span>
				<hr>
			</div>
			";
		}
		foreach($resultsamples as $rowsamples){
			$newid = $rowsamples->newid;
			$kitchen = $rowsamples->name;
			$address = $rowsamples->salon_address;
			$descr = $rowsamples->description;
			$size = $rowsamples->size;
			$cost = $rowsamples->cost;
			$sales = $rowsamples->sales;
			$images = $rowsamples->images;
			$thumbs = $rowsamples->thumbs;
			$folder = $rowsamples->folder;
			$moderate = $rowsamples->moderate;
			$dorab_reason = $rowsamples->dorab_reason;
			if($moderate == 'no'){
				$moderdiv = "<div style='padding:10px 20px; position:absolute; top:0; right:0; background:#6694B7; color:white;'>
					$lang_adm->mo_moderation
				</div>";
			}
			elseif($moderate == 'dorab'){
				$moderdiv = "<div style='padding:10px 20px; position:absolute; top:0; right:0; background:#EAC766; color:white;'>
					$lang_adm->mo_dorabotai
				</div>";
				$dorab_reason_div = "<div style='padding:8px 16px; background:#EAC766; width:calc(100% - 180px); color:#fff;'>
					$dorab_reason
				</div>";
			}
			$avatar = $rowsamples->avatar;
			$images_arr = explode (';', $images);
			$delete_icon = get_template_directory_uri() . '/images/delete.png';
			$c="";
			foreach($images_arr as $number => $imagename){
				if($imagename !=''){
					$c.="
					<div class='other_image_div'>
						<img src='../$folder/images/$imagename' class='other_image'><br>
						<input type='submit' value='$newid/$imagename' name='delete_image' style='color:transparent; cursor:pointer; width:30px; height:30px; margin:auto; position:relative;  
						background-image:url($delete_icon); background-repeat: no-repeat; background-size:contain; background-position:center; border:none; margin-top:16px;'/>
					</div>
					";

				}
			}
			$a.="
			<div class='sample_div' style='position:relative;'>
				$moderdiv
				$dorab_reason_div
				$b
				<form method='post' enctype='multipart/form-data'>
					<div class='mysamples_ava_settings' style='float:left;'>
						<div class='mysample_ava' style='background:url(../$folder/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
						<br>$lang_adm->mo_new_ava:<br>
						<input type='file' name='newAva'/>
					</div>
					<div class='mysample_inputs'>
						<b>$lang_adm->mo_kitchen</b><br>
						<textarea name='kitchen' style='width:100%; height:30px;' required>$kitchen</textarea><br>
						<b>$lang_adm->mo_descr</b><br>
						<textarea name='descr' style='width:100%; height:30px;' required>$descr</textarea><br>
						<b>$lang_adm->mo_sizes</b><br>
						<textarea name='size' style='width:100%; height:30px;' required>$size</textarea><br>
						<b>$lang_adm->mo_cost</b><br>
						<input name='cost' type='number' style='width:100%; height:30px; outline:none; border:1px solid #dcdcdc; margin-bottom:16px;' value='$cost' required><br>
						<b>$lang_adm->mo_currency:</b>
						<select name='currency'>
							<option value='$lang_adm->cur_bel_rub'>$lang_adm->cur_bel_rub</option>
							<option value='$lang_adm->cur_ros_rub'>$lang_adm->cur_ros_rub</option>
							<option value='$lang_adm->cur_grivna'>$lang_adm->cur_grivna</option>
							<option value='$lang_adm->cur_evro'>$lang_adm->cur_evro</option>
							<option value='$lang_adm->cur_dollar'>$lang_adm->cur_dollar</option>
							<option value='$lang_adm->cur_cron'>$lang_adm->cur_cron</option>
						</select>
						<b>$lang_adm->mo_sales_proc:</b>
						<textarea name='sales' style='width:80px; height:30px;'>$sales</textarea><br>
					</div>
					<div style='clear:both;'></div>
					<div class='mysamples_other_images'>
						$c
					</div><br><br>
					<b>$lang_adm->mo_add_images:</b><br>
					<input type='file' name='images[]' id='images' multiple><br><br><br>
					<button name='moderate' value='$newid' type='submit' class='classic_btn'>$lang_adm->mo_edits</button>
					<button name='delete_sample' value='$newid' type='submit' class='negative_btn' style='margin-left:32px;'>$lang_adm->mo_remove</button>
				</form>
				
			</div>
			";

		}
		$c="";
	}
	
	$a.="</div>";
	echo $a;
	
	if(isset($_POST['moderate'])){
		$moderate_id = $_POST['moderate'];
		$kitchen = $_POST['kitchen'];
		$descr = $_POST['descr'];
		$size = $_POST['size'];
		$cost = $_POST['cost'];
		$sales = $_POST['sales'];
		//заново получаем инфу, т.к. вышли из цикла
		$sqlsample2 = "SELECT images, thumbs, folder, salon_id FROM gi_samples WHERE newid='$moderate_id'";
		$resultsample2 = $wpdb->get_results($sqlsample2);
		foreach($resultsample2 as $rowsample2){
			$images = $rowsample2->images;
			$thumbs = $rowsample2->thumbs;
			$folder = $rowsample2->folder;
			$salon_id = $rowsample2->salon_id;
		}
		//ЗАМЕНЯЕМ АВАТАРКУ
		if(!empty($_FILES['newAva']) and $_FILES['newAva']['name']==true){
			if(is_uploaded_file($_FILES["newAva"]["tmp_name"])){			
				$tmp_newAva = $_FILES['newAva']['tmp_name'];
				$path_parts_newAva  = pathinfo($_FILES['newAva']['name']);
				$avaname = $_FILES['newAva']['name'];
				$avaname = str_replace($rus, $lat, $avaname);
				$extension_newAva = $path_parts_newAva['extension'];
				$newAva_link = $_SERVER['DOCUMENT_ROOT'] . '/' . $folder . "/images/" . $avaname;
				$newAva_upload_link = "$newAva_link";
				$delete_oldAva = $_SERVER['DOCUMENT_ROOT'] . "/$folder/images/$avatar";
				//РАЗМЕРЫ
				list($width, $height) = getimagesize($tmp_newAva);
				$otnosh = $width / $height;
				if ($otnosh >= 1){
					$thumb_width = 800;
				}
				else{
					$thumb_width = 400;					
				}
				$thumb_height = $thumb_width / $otnosh;
				$image_p = imagecreatetruecolor($thumb_width, $thumb_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ МИНИАТЮРЫ
				if ($extension_newAva == "png" or $extension_newAva=="jpg" or $extension_newAva=="jpeg" or $extension_newAva=="JPG"){
					unlink($delete_oldAva);
					switch($extension_newAva){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_newAva); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $newAva_upload_link, 75);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_newAva); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $newAva_upload_link, 75);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_newAva); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $newAva_upload_link, 75);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_newAva);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagepng($image_p, $newAva_upload_link);
						break; //Раньше брейка не было, ничего не поломал?????
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_newAva);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagepng($image_p, $newAva_upload_link);
						break;
					}

					$sql = "UPDATE gi_samples SET avatar = '$avaname', moderate = 'no' WHERE newid = '$moderate_id'";
					$result = $wpdb->get_results($sql);
				}	
			}
		}		
		//ДОБАВЛЯЕМ КАРТИНКИ
		$images_dir = "$folder";
		$imagesname_data = date("dmY");

		if(isset($_FILES['images']) and $_FILES['images']==TRUE and $_FILES['images']['name'][0]!=''){
			foreach($_FILES['images']['name'] as $numberAddFile=>$nameAddFile){
				$$numberAddFile = $nameAddFile; //надо 2 знака $
			}			
			for ($i=0; $i<=$numberAddFile; $i++) {
				$images_folder = "$images_dir/images/";
				$thumbs_folder = "$images_dir/thumbs/";
				$imagelink = $_SERVER['DOCUMENT_ROOT'] . '/' . $images_folder .  $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$thumblink = $_SERVER['DOCUMENT_ROOT'] . '/' . $thumbs_folder . $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];				
				$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
				$thumblink = str_replace($rus, $lat, $thumblink);
				$tmp = $_FILES['images']['tmp_name'][$i];
				$path_parts  = pathinfo($_FILES['images']['name'][$i]);
				$extension = $path_parts['extension'];

				//задаем размеры миниатюрам и большим картинкам
				list($width, $height) = getimagesize($tmp);
				$otnosh = $width / $height;
				if ($otnosh >= 1){
					$thumb_width = 600;
					$image_width = 1300;
				}
				else{
					$thumb_width = 400;
					$image_width = 800;					
				}
				$thumb_height = $thumb_width / $otnosh;
				$image_height = $image_width / $otnosh;
				$image_p = imagecreatetruecolor($thumb_width, $thumb_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ МИНИАТЮРЫ
				$image_p2 = imagecreatetruecolor($image_width, $image_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ БОЛЬШОЙ КАРТИНКИ
				switch($extension){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
						imagepng($image_p, $thumblink);
						imagepng($image_p2, $imagelink);
						break; //Раньше брейка не было, ничего не поломал?????
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
						imagepng($image_p, $thumblink);
						imagepng($image_p2, $imagelink);
						break;
				}
				//имена картинок для внесения в БД
				$image_name = $salon_id . "_" . $imagesname_data . "_" . str_replace($rus, $lat, $_FILES['images']['name'][$i]);
				$thumb_name = $salon_id . "_" . $imagesname_data . "_" . str_replace($rus, $lat, $_FILES['images']['name'][$i]);
				$images_db .="$image_name;";
				$thumbs_db .="$thumb_name;";
				
			}
		}
		$images_result = $images . $images_db;
		$thumbs_result = $thumbs . $thumbs_db;
		echo $salon_id;
		$sqlredact = "UPDATE gi_samples SET `images`='$images_result', `thumbs`='$thumbs_result', name='$kitchen', `description` = '$descr', `size` = '$size', `cost`='$cost', `sales`='$sales', `moderate` = 'no' WHERE newid='$moderate_id'";
		$resultredact = $wpdb->get_results($sqlredact);
		
		$message = "На модерацию поступил образец. Проверьте админку сайта gi.by";
		$subject = "Модерация образцов";
		mail('info@gi.by', $subject, $message);
	}
	
	//УДАЛЯЕМ ОТДЕЛЬНУЮ ФОТКУ
	if(isset($_POST['delete_image'])){
		$delete_image = $_POST['delete_image'];
		list($delete_image_id, $delete_image_name) = explode("/", $delete_image);
		//Заново получаем данные из бд
		$sql_deleteimage = "SELECT images, thumbs, folder FROM gi_samples WHERE newid='$delete_image_id'";
		$result_deleteimage = $wpdb->get_results($sql_deleteimage);
		foreach ($result_deleteimage as $row_deleteimage){
			$images = $row_deleteimage -> images;
			$thumbs = $row_deleteimage -> thumbs;
			$folder = $row_deleteimage -> folder;
			$imageunlink = "../" . $folder . "/images/" . $delete_image_name;
			$thumbunlink = "../" . $folder . "/thumbs/" . $delete_image_name;
			unlink($imageunlink);
			unlink($thumbunlink);
			$newimages = str_replace($delete_image_name .';', "", $images);
			$sqlnewimages="UPDATE `gi_samples` SET `images`='$newimages', `thumbs`='$newimages' WHERE `newid`='$delete_image_id'";
			$resultnewimages = $wpdb->get_results($sqlnewimages);
		}

	}
	
	if(isset($_POST['delete_sample']))	{
		$deleteid = $_POST['delete_sample'];
		//ПОЛУЧАЕМ ИНФУ ИЗ БД
		$sqlimagesdelete = "SELECT images, folder FROM gi_samples WHERE newid='$deleteid'";
		$resultimagesdelete = $wpdb->get_results($sqlimagesdelete);
		foreach($resultimagesdelete as $rowimagesdelete){
			$images = $rowimagesdelete->images;
			$folder = $rowimagesdelete->folder;
			$images_arr = array_filter(explode(";", $images));
			foreach ($images_arr as $image){
				$imagefordelete = "../" . $folder . "/images/" . $image;
				$thumbfordelete = "../" . $folder . "/thumbs/" . $image;
				unlink ($thumbfordelete);
				unlink ($imagefordelete);
			}
			$images_folder = "../" . $folder . "/images";
			$thumbs_folder = "../" . $folder . "/thumbs";
			rmdir($images_folder);
			rmdir($thumbs_folder);
			rmdir("../" . $folder);
		}
		
		$sqldelete = "DELETE FROM `gi_samples` WHERE `newid` = $deleteid";
		$resultdelete = $wpdb -> get_results($sqldelete);
		
	}
	
}
function all_samples(){
	global $wpdb;
	
	$rus=array('"','(',')',' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('','','','_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');

	$a ="<div style='width:calc(100% - 75px); margin-top:25px; min-height:100px; background:white; position:relative; padding:25px;'>";
	
	$user= wp_get_current_user()->user_login;
	if($user != "admin"){
		require_once("../wp-content/plugins/bp_samples/my_salons.php");
	}
	else {
		$a.="
		<form method='POST'>
		<br><br>
			<select id='countryselect' name='country'>
				<option selected disabled value=''>Выберите страну</option>
				<option value='Беларусь'>Беларусь</option>
				<option value='Россия'>Россия</option>
				<option value='Украина'>Украина</option>
				<option value='Казахстан'>Казахстан</option>
			</select>
			<select name='city' id='blr' style='display:none;' onchange='selectedcity(this);'>
				<option selected disabled value=''>Выберите город</option>";
		//ГОРОДА БЕЛАРУСИ
		$sqlblr = "SELECT DISTINCT city FROM gi_salons WHERE country='Беларусь'";	
		$resultblr = $wpdb->get_results($sqlblr);
		foreach ($resultblr as $rowblr){
			$cityblr=$rowblr->city;
			$a.="
			<option value='$cityblr'>$cityblr</option>
			";
		}
		$a.="</select>";
		//ГОРОДА РОССИИ
		$a.="<select name='city' id='rf' style='display:none;' onchange='selectedcity(this);'>
			<option selected disabled value=''>Выберите город</option>";
		$sqlrf = "SELECT DISTINCT city FROM gi_salons WHERE country='Россия'";	
		$resultrf = $wpdb->get_results($sqlrf);
		foreach ($resultrf as $rowrf){
			$cityrf=$rowrf->city;
			$a.="
			<option value='$cityrf'>$cityrf</option>
			";
		}	
		$a.="</select>";
		//ГОРОДА УКРАИНЫ
		$a.="<select name='city' id='ukr' style='display:none;' onchange='selectedcity(this);'>
			<option selected disabled value=''>Выберите город</option>";
		$sqlukr = "SELECT DISTINCT city FROM gi_salons WHERE country='Украина'";	
		$resultukr = $wpdb->get_results($sqlukr);
		foreach ($resultukr as $rowukr){
			$cityukr=$rowukr->city;
			$a.="
			<option value='$cityukr'>$cityukr</option>
			";
		}	
		$a.="</select>";
		//ГОРОДА КАЗАХСТАНА
		$a.="<select name='city' id='kz' style='display:none;' onchange='selectedcity(this);'>
			<option selected disabled value=''>Выберите город</option>";
		$sqlkz = "SELECT DISTINCT city FROM gi_salons WHERE country='Казахстан'";	
		$resultkz = $wpdb->get_results($sqlkz);
		foreach ($resultkz as $rowkz){
			$citykz=$rowkz->city;
			$a.="
			<option value='$citykz'>$citykz</option>
			";
		}	
		$a.="</select>";
		$a.="
		</form>
		";

		$a.="
		<div id='ajaxcontent' class='result'></div>	

		<script>

		var countryselect = document.getElementById('countryselect');
		jQuery(countryselect).on('change', function() {
			var mycountry = this.value;
			//ПОКАЗЫВАЕМ И СКРЫВАЕМ СЕЛЕКТЫ
			var blr = document.getElementById('blr');
			var rf = document.getElementById('rf');
			var ukr = document.getElementById('ukr');
			var kz = document.getElementById('kz');
			if (mycountry == 'Беларусь'){jQuery(blr).show(); } else {jQuery(blr).hide();}
			if (mycountry == 'Россия'){jQuery(rf).show();} else {jQuery(rf).hide();}
			if (mycountry == 'Украина'){jQuery(ukr).show(); } else { jQuery(ukr).hide(); }
			if (mycountry == 'Казахстан'){ jQuery(kz).show(); } else {jQuery(kz).hide();}

		});
			
		//ТЕПЕРЬ ГОРОД ОТПРАВЛЯЕМ АЯКСОМ
		var cityselect = document.getElementsByName('city');
		jQuery(cityselect).on('change', function() {
			var mycity = this.value;
			jQuery.ajax({
				url: '/wp-content/plugins/bp_samples/admin_salons.php',
				type: 'POST',
				data: {city:mycity},
				cache: false,
				success: function(html){  
					jQuery('#ajaxcontent').html(html);  
				} 
			});		
		});	

		</script>
		";
		echo $a;
		
		if(isset($_POST['moderate'])){
			$moderate_id = $_POST['moderate'];
			$kitchen = $_POST['kitchen'];
			$descr = $_POST['descr'];
			$size = $_POST['size'];
			$cost = $_POST['cost'];
			$sales = $_POST['sales'];
			//заново получаем инфу, т.к. вышли из цикла
			$sqlsample2 = "SELECT images, thumbs, folder, salon_id, avatar FROM gi_samples WHERE newid='$moderate_id'";
			$resultsample2 = $wpdb->get_results($sqlsample2);
			foreach($resultsample2 as $rowsample2){
				$images = $rowsample2->images;
				$thumbs = $rowsample2->thumbs;
				$folder = $rowsample2->folder;
				$salon_id = $rowsample2->salon_id;
				$avatar = $rowsample2->avatar;
			}
			//ДОБАВЛЯЕМ КАРТИНКИ
			$images_dir = "../$folder";
			$imagesname_data = date("dmY");
			
			//ЗАМЕНЯЕМ АВАТАРКУ
			if(!empty($_FILES['newAva']) and $_FILES['newAva']['name']==true){
				if(is_uploaded_file($_FILES["newAva"]["tmp_name"])){			
					$tmp_newAva = $_FILES['newAva']['tmp_name'];
					$path_parts_newAva  = pathinfo($_FILES['newAva']['name']);
					$avaname = $_FILES['newAva']['name'];
					$avaname = str_replace($rus, $lat, $avaname);
					$extension_newAva = $path_parts_newAva['extension'];
					$newAva_link = $folder . "/images/" . $avaname;
					$newAva_upload_link = "../$newAva_link";
					$delete_oldAva = "../$folder/images/$avatar";
					//РАЗМЕРЫ
					list($width, $height) = getimagesize($tmp_newAva);
					$otnosh = $width / $height;
					if ($otnosh >= 1){
						$thumb_width = 800;
					}
					else{
						$thumb_width = 400;					
					}
					$thumb_height = $thumb_width / $otnosh;
					$image_p = imagecreatetruecolor($thumb_width, $thumb_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ МИНИАТЮРЫ
					if ($extension_newAva == "png" or $extension_newAva=="jpg" or $extension_newAva=="jpeg" or $extension_newAva=="JPG"){
						unlink($delete_oldAva);
						switch($extension_newAva){
						case "jpg":	//СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp_newAva); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagejpeg($image_p, $newAva_upload_link, 75);
							break;
						case "JPG":	//СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp_newAva); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagejpeg($image_p, $newAva_upload_link, 75);
							break;
						case "jpeg": //СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp_newAva); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagejpeg($image_p, $newAva_upload_link, 75);
							break;
						case "png":	//СОЗДАНИЕ ПНГ
							$image = imagecreatefrompng($tmp_newAva);
							imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_p, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							imagepng($image_p, $newAva_upload_link);
							break; //Раньше брейка не было, ничего не поломал?????
						case "PNG":	//СОЗДАНИЕ ПНГ
							$image = imagecreatefrompng($tmp_newAva);
							imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_p, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							imagepng($image_p, $newAva_upload_link);
							break;
						}

						$sql = "UPDATE gi_samples SET avatar = '$avaname', moderate = 'no' WHERE newid = '$moderate_id'";
						$result = $wpdb->get_results($sql);
					}	
				}
			}

			if(isset($_FILES['images']) and $_FILES['images']==TRUE and $_FILES['images']['name'][0]!=''){
				//var_dump($_FILES);
				foreach($_FILES['images']['name'] as $numberAddFile=>$nameAddFile){
					$$numberAddFile = $nameAddFile; //надо 2 знака $
				}			
				for ($i=0; $i<=$numberAddFile; $i++) {
					$images_folder = "$images_dir/images/";
					$thumbs_folder = "$images_dir/thumbs/";
					$imagelink = $images_folder .  $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
					$thumblink = $thumbs_folder . $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];				
					$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
					$thumblink = str_replace($rus, $lat, $thumblink);
					$tmp = $_FILES['images']['tmp_name'][$i];
					$path_parts  = pathinfo($_FILES['images']['name'][$i]);
					$extension = $path_parts['extension'];

					//задаем размеры миниатюрам и большим картинкам
					list($width, $height) = getimagesize($tmp);
					$otnosh = $width / $height;
					if ($otnosh >= 1){
						$thumb_width = 600;
						$image_width = 1300;
					}
					else{
						$thumb_width = 400;
						$image_width = 800;					
					}
					$thumb_height = $thumb_width / $otnosh;
					$image_height = $image_width / $otnosh;
					$image_p = imagecreatetruecolor($thumb_width, $thumb_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ МИНИАТЮРЫ
					$image_p2 = imagecreatetruecolor($image_width, $image_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ БОЛЬШОЙ КАРТИНКИ
					switch($extension){
						case "jpg":	//СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagejpeg($image_p, $thumblink, 75);
							imagejpeg($image_p2, $imagelink, 75);
							break;
						case "JPG":	//СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagejpeg($image_p, $thumblink, 75);
							imagejpeg($image_p2, $imagelink, 75);
							break;
						case "jpeg": //СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagejpeg($image_p, $thumblink, 75);
							imagejpeg($image_p2, $imagelink, 75);
							break;
						case "png":	//СОЗДАНИЕ ПНГ
							$image = imagecreatefrompng($tmp);
							imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
							imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_p, true); //Включаем сохранение альфа канала
							imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
							imagepng($image_p, $thumblink);
							imagepng($image_p2, $imagelink);
							break; //Раньше брейка не было, ничего не поломал?????
						case "PNG":	//СОЗДАНИЕ ПНГ
							$image = imagecreatefrompng($tmp);
							imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
							imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_p, true); //Включаем сохранение альфа канала
							imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
							imagepng($image_p, $thumblink);
							imagepng($image_p2, $imagelink);
							break;
					}
					//имена картинок для внесения в БД
					$image_name = $salon_id . "_" . $imagesname_data . "_" . str_replace($rus, $lat, $_FILES['images']['name'][$i]);
					$thumb_name = $salon_id . "_" . $imagesname_data . "_" . str_replace($rus, $lat, $_FILES['images']['name'][$i]);
					$images_db .="$image_name;";
					$thumbs_db .="$thumb_name;";
					
				}
			}
			$images_result = $images . $images_db;
			$thumbs_result = $thumbs . $thumbs_db;
			$sqlredact = "UPDATE gi_samples SET `images`='$images_result', `thumbs`='$thumbs_result', name='$kitchen', `description` = '$descr', `size` = '$size', `cost`='$cost', `sales`='$sales', `moderate` = 'no' WHERE newid='$moderate_id'";
			$resultredact = $wpdb->get_results($sqlredact);
		}
		
		//УДАЛЯЕМ ОТДЕЛЬНУЮ ФОТКУ
		if(isset($_POST['delete_image'])){
			$delete_image = $_POST['delete_image'];
			list($delete_image_id, $delete_image_name) = explode("/", $delete_image);
			//Заново получаем данные из бд
			$sql_deleteimage = "SELECT images, thumbs, folder FROM gi_samples WHERE newid='$delete_image_id'";
			$result_deleteimage = $wpdb->get_results($sql_deleteimage);
			foreach ($result_deleteimage as $row_deleteimage){
				$images = $row_deleteimage -> images;
				$thumbs = $row_deleteimage -> thumbs;
				$folder = $row_deleteimage -> folder;
				$imageunlink = "../" . $folder . "/images/" . $delete_image_name;
				$thumbunlink = "../" . $folder . "/thumbs/" . $delete_image_name;
				unlink($imageunlink);
				unlink($thumbunlink);
				$newimages = str_replace($delete_image_name .';', "", $images);
				$sqlnewimages="UPDATE `gi_samples` SET `images`='$newimages', `thumbs`='$newimages' WHERE `newid`='$delete_image_id'";
				$resultnewimages = $wpdb->get_results($sqlnewimages);
			}

		}
		
		if(isset($_POST['delete_sample']))	{
			$deleteid = $_POST['delete_sample'];
			//ПОЛУЧАЕМ ИНФУ ИЗ БД
			$sqlimagesdelete = "SELECT images, folder FROM gi_samples WHERE newid='$deleteid'";
			$resultimagesdelete = $wpdb->get_results($sqlimagesdelete);
			foreach($resultimagesdelete as $rowimagesdelete){
				$images = $rowimagesdelete->images;
				$folder = $rowimagesdelete->folder;
				$images_arr = array_filter(explode(";", $images));
				foreach ($images_arr as $image){
					$imagefordelete = "../" . $folder . "/images/" . $image;
					$thumbfordelete = "../" . $folder . "/thumbs/" . $image;
					unlink ($thumbfordelete);
					unlink ($imagefordelete);
				}
				$images_folder = "../" . $folder . "/images";
				$thumbs_folder = "../" . $folder . "/thumbs";
				rmdir($images_folder);
				rmdir($thumbs_folder);
				rmdir("../" . $folder);
			}
			
			$sqldelete = "DELETE FROM `gi_samples` WHERE `newid` = $deleteid";
			$resultdelete = $wpdb -> get_results($sqldelete);
			
		}
		echo $z;
	}
	

}
function add_sample(){
	
	global $wpdb;
	global $lang_adm;
	
	$rus=array('"','(',')',' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('','','','_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');

	$a ="<div style='width:calc(50% - 75px); min-height:100px; margin:auto; background:white; position:relative; padding:25px; margin-top:20px;'>
	<h1>Добавить образец на продажу</h1>";
	$user= wp_get_current_user()->user_login;
	if($user != "admin"){
		$displayuserselect = "display:none;";
		$requireuserselect = "";
	}
	else {
		$requireuserselect = "required";
	}
	$a.="
	<form method='POST' enctype='multipart/form-data'>
		<select name='user' style='width:100%; margin:10px 0; $displayuserselect' $requireuserselect>
			<option selected disabled value=''>Выберите пользователя</option>
	";
	$sqluser = "SELECT user_login FROM wp_users ORDER BY user_nicename";
	$resultuser = $wpdb->get_results($sqluser);
	foreach ($resultuser as $rowuser){
		$username = $rowuser->user_login;
		$a.="<option value='$username'>$username</option>";
	}
	$a.="</select>
		<select name='kitchen' style='width:100%; margin:10px 0;' required>
			<option selected disabled value=''>Выберите кухню</option>
	";
	$sqlkitchen = "SELECT name FROM gi_kitchen ORDER BY name";
	$resultkitchen = $wpdb->get_results($sqlkitchen);
	foreach ($resultkitchen as $rowkitchen){
		$kitchen = $rowkitchen->name;
		$a.="
		<option value='$kitchen'>$kitchen</option>
		";
	}
	$a.="
		</select><br>
		<select name='salon' style='width:100%; margin:10px 0;' required>
			<option selected disabled value=''>Выберите свой салон</option>
			
	";
	//Полная выборка только для админа
	if ($user == "admin"){
		$whereuser = "";
	}
	else {
		$whereuser = "AND user = '$user'";
	}
	$sqlsalon = "SELECT newid, country, city, address FROM gi_salons WHERE moderate = 'yes' $whereuser";
	$resultsalon = $wpdb->get_results($sqlsalon);
	foreach($resultsalon as $rowsalon){
		$newid = $rowsalon->newid;
		$country = $rowsalon->country;
		$city = $rowsalon->city;
		$address = $rowsalon->address;
		$a.="<option value='$newid'>$country, $city, $address</option>";
	}
	$a.="
		</select><br>
		<input name='size' placeholder='Размер образца' style='width:100%; margin:10px 0;' required/><br>
		
		<textarea name='descr' placeholder='Описание' style='width:100%; margin:10px 0;' required></textarea><br>
		
		<input name='cost' placeholder='Цена образца' style='width:100%; margin:10px 0;' required/><br>
		
		<b>Выберите валюту:</b> 
		<select name='currency'>
			<option value='$lang_adm->cur_bel_rub'>$lang_adm->cur_bel_rub</option>
			<option value='$lang_adm->cur_ros_rub'>$lang_adm->cur_ros_rub</option>
			<option value='$lang_adm->cur_grivna'>$lang_adm->cur_grivna</option>
			<option value='$lang_adm->cur_evro'>$lang_adm->cur_evro</option>
			<option value='$lang_adm->cur_dollar'>$lang_adm->cur_dollar</option>
			<option value='$lang_adm->cur_cron'>$lang_adm->cur_cron</option>
		</select><br>
		
		<input name='sales' type='number' placeholder='Процент скидки' style='width:100%; margin:10px 0;' required/><br>

		
		Загрузите изображения: <br> 
		<input type='file' name='images[]' id='images' multiple required><br><br>
		<span id='outputMulti'></span><br>
	
		<button name='addsample' type='submit' style='padding:10px 25px; margin:20px 0; background:green; color:white; border:none; cursor:pointer;'>Добавить образец</button>
	</form>
	";
	$a.="</div>";

	$a.="
	<script>
	/*ПОДГРУЗКА ФОТОГРАФИЙ ПРИ ЗАГРУЗКЕ*/


		function handleFileSelectMulti(evt) {
		var files = evt.target.files; // FileList object
		document.getElementById('outputMulti').innerHTML = \"\";
		for (var i = 0, f; f = files[i]; i++) {
		  // Only process image files.
		  if (!f.type.match('image.*')) {
			alert('Только изображения....');
		  }
		  var reader = new FileReader();
		  // Closure to capture the file information.
		  reader.onload = (function(theFile) {
			return function(e) {
			  // Render thumbnail.
			  var span = document.createElement('span');
			  var imagename = theFile.name;
			  span.innerHTML = [\"<input type='radio' name='avatar' value='\", imagename, \"' required/>Аватар<br><img class='img-thumbnail' style='max-height:200px; margin:10px;' src='\", e.target.result,
								\"' title='\", escape(theFile.name), \"'/> <br>\"].join('');
			  document.getElementById('outputMulti').insertBefore(span, null);
			};
		  })(f);
		  // Read in the image file as a data URL.
		  reader.readAsDataURL(f);
		}
	  }
	  document.getElementById('images').addEventListener('change', handleFileSelectMulti, false);
	</script>
	";
	return $a;
	
	if(isset($_POST['addsample'])){
		$kitchen = $_POST['kitchen'];
		$kitchen_eng = str_replace($rus, $lat, $kitchen);
		$salon_id = $_POST['salon'];
		$size = $_POST['size'];
		$descr = $_POST['descr'];
		$cost = $_POST['cost'];
		$sales = $_POST['sales'];
		$currency = $_POST['currency'];
		$avatar = $_POST['avatar'];
		//Заново получаем адрес салона
		$sqladdress = "SELECT country, city, address FROM gi_salons WHERE newid = '$salon_id'";
		$resultaddress = $wpdb->get_results($sqladdress);
		foreach ($resultaddress as $rowaddress){
			$country = $rowaddress->country;
			$city = $rowaddress->city;
			$address = $rowaddress->address;
			$fulladdress = $country . ", " . $city . ", " . $address;
		}
		$images_dir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_samples/images/samples";
		$dirname = $salon_id . "_" . $kitchen_eng;
		$imagesname_data = date("dmY");

		if(!empty($_FILES['images']) and $_FILES['images']==TRUE){
			if (!file_exists($images_dir)) {
				mkdir($images_dir);
				mkdir("$images_dir/$dirname/");
				mkdir("$images_dir/$dirname/images");
				mkdir("$images_dir/$dirname/thumbs");
			}
			else {
				if(!file_exists("$images_dir/$dirname/")){
					mkdir("$images_dir/$dirname/");
					mkdir("$images_dir/$dirname/images");
					mkdir("$images_dir/$dirname/thumbs");
				}
			}
			
			foreach($_FILES['images']['name'] as $numberAddFile=>$nameAddFile){
				$$numberAddFile = $nameAddFile; //надо 2 знака $
			}			
			for ($i=0; $i<=$numberAddFile; $i++) {
				$images_folder = "$images_dir/$dirname/images/";
				$thumbs_folder = "$images_dir/$dirname/thumbs/";
				$imagelink = $images_folder .  $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$thumblink = $thumbs_folder . $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];				
				$imagelink = str_replace($rus, $lat, $imagelink); //Русскоязычные фото переименовываем в англоязычные - $rus & $lat заданы в начале дока
				$thumblink = str_replace($rus, $lat, $thumblink);
				$tmp = $_FILES['images']['tmp_name'][$i];
				$path_parts  = pathinfo($_FILES['images']['name'][$i]);
				$extension = $path_parts['extension'];

				//задаем размеры миниатюрам и большим картинкам
				list($width, $height) = getimagesize($tmp);
				$otnosh = $width / $height;
				if ($otnosh >= 1){
					$thumb_width = 600;
					$image_width = 1300;
				}
				else{
					$thumb_width = 400;
					$image_width = 800;					
				}
				$thumb_height = $thumb_width / $otnosh;
				$image_height = $image_width / $otnosh;
				$image_p = imagecreatetruecolor($thumb_width, $thumb_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ МИНИАТЮРЫ
				$image_p2 = imagecreatetruecolor($image_width, $image_height); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ БОЛЬШОЙ КАРТИНКИ
				switch($extension){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagejpeg($image_p, $thumblink, 75);
						imagejpeg($image_p2, $imagelink, 75);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
						imagepng($image_p, $thumblink);
						imagepng($image_p2, $imagelink);
						break; //Раньше брейка не было, ничего не поломал?????
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp);
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagealphablending($image_p2, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagesavealpha($image_p2, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						imagecopyresampled($image_p2, $image, 0, 0, 0, 0, $image_width, $image_height, $width, $height);
						imagepng($image_p, $thumblink);
						imagepng($image_p2, $imagelink);
						break;
				}
				//имена картинок для внесения в БД
				$image_name = $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$thumb_name = $salon_id . "_" . $imagesname_data . "_" . $_FILES['images']['name'][$i];
				$images_db .="$image_name;";
				$thumbs_db .="$thumb_name;";	
				$folder_db = "wp-content/plugins/bp_samples/images/samples/$dirname";
				$cur_user = wp_get_current_user()->user_login;
				if(isset($_POST['user']) and $cur_user == "admin"){
					$username = $_POST['user'];					
				}
				else {
					$username = wp_get_current_user()->user_login;;
				}
			}
			
			$avatar = $salon_id . "_" . $imagesname_data . "_" . $avatar;
			$avatar = str_replace($rus, $lat, $avatar);
			$images_db = str_replace($rus, $lat, $images_db);
			$thumbs_db = str_replace($rus, $lat, $thumbs_db);
			//удаляем аватар из списка картинок
			$images_db = str_replace($avatar . ";", "", $images_db);
			$thumbs_db = str_replace($avatar . ";", "", $thumbs_db);
			$sql="INSERT INTO `gi_samples` 
				(`name`, `salon_id`, `city`, `salon_address`, `description`, `size`, `cost`, `currency`, `sales`, `avatar`, `images`, `thumbs`, `folder`, `user`, `moderate`) 
			VALUES
				('$kitchen', '$salon_id', '$city', '$fulladdress', '$descr', '$size', '$cost', '$currency', '$sales', '$avatar', '$images_db', '$thumbs_db', '$folder_db', '$username', 'no');";
			$result = $wpdb->get_results($sql);	
			echo "<script>alert('$lang_adm->ao_alert_message');</script>";
		}
	}
}

function samples_print($atts){
	
	$rus=array('"','(',')',' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('','','','_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');

	//параметр к шорткоду (Покупателям, Новости)
	extract( shortcode_atts( array(
        'country' => ''
    ), $atts ) );
	
	//ЕСЛИ НЕТ АТРИБУТА - ТЯНЕМ ИЗ СЕССИИ
	if ($country ==""){
		$country = "Россия";
		$limit = "LIMIT 6";
		//$display = "display:none;";
	}
	if(!empty($_GET['city'])){
		$entered_city = $_GET['city'];
		$where_city = "AND city = '$entered_city'";
	}
	global $wpdb;

	$sqlAddress = "SELECT newid, name, country, city, address FROM gi_salons WHERE city='$entered_city'";
	$resultAdress = $wpdb->get_results($sqlAddress);
	foreach ($resultAdress as $rowAddress){
		//$salon_address = $rowAddress -> salon_address;
		//$salon_id = $rowAddress -> salon_id;
		
		
		$salon_id = $rowAddress -> newid;
		$salon_name = $rowAddress -> name;
		$salon_country = $rowAddress -> country;
		$salon_city = $rowAddress -> city;
		$salon_address = $rowAddress -> address;
		


		$sqlSamples = "SELECT * FROM gi_samples WHERE salon_id = '$salon_id' AND moderate='yes'";
		$resultSamples = $wpdb -> get_results($sqlSamples);
		if($resultSamples){
			
			$a ="<div style='font-size:32px;'>$salon_name</div>
			<div style='font-size:16px;'>$salon_address</div>";
			$a.="<div style='width:100%; text-align:center;'>";
			foreach ($resultSamples as $rowSamples){
				$newid = $rowSamples->newid;
				$sample_name = $rowSamples->name;
				$city = $rowSamples->city;
				$description = $rowSamples->description;
				$size = $rowSamples->size;
				$cost = $rowSamples->cost;
				$sales = $rowSamples->sales;
				$images = $rowSamples->images;
				$thumbs = $rowSamples->thumbs;
				$folder = $rowSamples->folder;
				$avatar = $rowSamples->avatar;
				$currency = $rowSamples->currency;
				
				$images_arr = explode(";", $images);
				$new_cost = is_integer($sales) ? round($cost - ($cost / 100 * $sales)) : '';
				$c = "";
				foreach ($images_arr as $image){
					if ($image){
						$c.="<a href='/$folder/images/$image' data-lightbox='$sample_name$newid'></a>";
					}
					
				}
				$avatar = "/" . $folder . "/images/" . $avatar;
				$a.="				
				<div class='sampleDiv'>";

				if (is_numeric($cost)) {
				$a.="<div class='salesDiv'>
						-$sales%
					</div>";
				}

				$a.="<a href='$avatar' data-lightbox='$sample_name$newid'>
						
						<div class='sampleAva' style='background-color:#dbdbdb !important; background:url($avatar) no-repeat; background-size:contain; background-position:center;'></div>
					</a>$c
				";
				if (is_numeric($cost)) {
					$a.="<div class='sampleCost'>
							<span class='sampleOldCost'>$cost</span> <span class='sampleNewCost'>$new_cost</span> $currency
						</div>";
				} else {
					$a.="<div class='sampleCost'>
							<span >$cost</span> 
						</div>";
				}

				$a.="<div class='sampleContent'> 
						<span class='sampleName'>$sample_name </span><br>
						<span><b>Размеры</b>: $size</span><br>
						<span><b>Описание</b>: $description<br>
							<p style='font-size:12px; color:#1e3350; margin:16px 0;'>*Не является публичной офертой. Актуальность предложения уточняйте в салоне.</p>
						</span><br>
						


					</div>
				</div>
				";
			}
			$a.="<div style='clear:both'></div>";
			$a.="</div>";
		}
	}

	return $a;
}

?>