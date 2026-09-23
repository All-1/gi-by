<?php
/*
Plugin Name: bp_add_salon
Description: Добавление салона.
Version: 1.0
Author: Business Park
*/
function add_salonadd_styles() {
    wp_enqueue_style( 'add_salonadd_styles', plugins_url('/css/salonadd.css', __FILE__) ); 
}
add_action( 'admin_enqueue_scripts', 'add_salonadd_styles' );

add_action('admin_menu', 'add_salon_page');
function add_salon_page() {
	add_menu_page('Добавить салон', 'Добавить салон', 'read', __FILE__, 'add_salon', 'dashicons-location-alt');
		add_submenu_page(__FILE__, 'Модерировать', 'Модерировать', 8, 'moderate_salon', 'moderate_salon');
		add_submenu_page(__FILE__, 'Редактировать', 'Редактировать', 8, 'redact_salon', 'redact_salon');
		//add_submenu_page(__FILE__, 'Мои салоны', 'Мои салоны', 'read', 'my_salons', 'my_salons');
}
add_shortcode('my_salons', 'my_salons');
add_shortcode('add_salon_shortcode', 'add_salon_shortcode');
function add_js_scripts_add_salon(){
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_add_salon/js/scripts.js"></script>
	<?php
}

function add_salon_shortcode() {

global $user_role;
if($user_role == 'designer_architect'){
	header("Location: /designer-conditions/");
}
add_js_scripts_add_salon();

global $wpdb;
global $lang_adm;
	
$plugins_url = plugins_url();

$phpself = $_SERVER['PHP_SELF'];
$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
$current_user = wp_get_current_user()->user_login;	
echo "<h1>$lang_adm->as_h1</h1>";
echo "<p>$lang_adm->as_p_1</p>";
$a ="
	<link rel='stylesheet' href='../osm/add_place/leaflet.css'/>
	<script src='../osm/add_place/leaflet-src.js'></script>

	<link rel='stylesheet' href='../osm/add_place/Control.Geocoder.css' />
	<script src='../osm/add_place/Control.Geocoder.js'></script>
	
<form method='POST' enctype='multipart/form-data'>

<span class='fieldsHeader' style='font-size:22px; color:#e78c68;'>$lang_adm->as_p_2:</span>
<i>($lang_adm->as_p_3)</i><br>
<div id='map' class='map addsalonmap'></div>
	

<div class='rightinputs' style='width:30.5%; float:left;'>
";
//Если админ - выбор юзера
if($current_user == "admin"){
	$user_select = "
	<select name='user_select' required style='width:100%; margin:10px 0 20px 0;'>
	";
	//$sql_user_select = "SELECT user_login, ID FROM wp_users ORDER BY user_login ASC";
	$sql_user_select = "SELECT DISTINCT user_id FROM wp_usermeta WHERE meta_key = 'city' OR meta_key='nickname' ORDER BY meta_value ASC";

	$result_user_select = $wpdb->get_results($sql_user_select);
	foreach($result_user_select as $row_user_select){
		//$user_name = $row_user_select->user_login;
		$user_id = $row_user_select->user_id;
		$user_name = get_user_meta($user_id, 'nickname', true);
		$user_city = get_user_meta($user_id, 'city', true);
		if($user_name == $current_user){
			$selected_admin = "selected";
		}
		else $selected_admin ="";
		if(!empty($user_city)){
			$user_city_echo = $user_city . ", ";
		}
		$user_select .="<option value='$user_name' $selected_admin>$user_city_echo $user_name</option>";
	}
	$user_select .="
	</select>
	";
}
else $user_select = "";
$a.="
	$user_select
	<div class='autoinputdiv'>
		<input id='name' name='thisname' placeholder='$lang_adm->s_name' required/><br>
	</div>

	<div class='input_lat autoinputdiv'>
		<input id='lat' name='lat' placeholder='$lang_adm->s_lat' required/><br>
	</div>
	<div class='input_long autoinputdiv'>
		<input id='long' name='long' placeholder='$lang_adm->s_lng' required/><br>
	</div>
	<div class='input_place autoinputdiv'>
		<select id='place' name='country' required style='width:100%;'>
			<option selected disabled value=''>$lang_adm->as_set_country</option>
			<option value='$lang_adm->as_rb'>$lang_adm->as_rb</option>
			<option value='$lang_adm->as_ukr'>$lang_adm->as_ukr</option>
			<option value='$lang_adm->as_rf'>$lang_adm->as_rf</option>
			<option value='$lang_adm->as_rb'>$lang_adm->as_rb</option>
			<option value='$lang_adm->as_kz'>$lang_adm->as_kz</option>
			<option value='$lang_adm->as_deu'>$lang_adm->as_deu</option>
			<option value='$lang_adm->as_schw'>$lang_adm->as_schw</option>
			<option value='$lang_adm->as_fr'>$lang_adm->as_fr</option>
		</select>
	</div>
	<input name='city' id='city' placeholder='$lang_adm->s_city' required/><br>
	<input name='address' id='address' placeholder='$lang_adm->s_address' required/><br>
	<input name='mail' id='mail' placeholder='$lang_adm->s_mailbox' required/><br>
	<textarea name='phones' id='phones' wrap='soft' style='min-height:80px;' wrap='soft' placeholder='$lang_adm->s_phones ($lang_adm->as_new_line)' required/></textarea><br>

</div>
<div style='clear:both'></div>


<div class='bottomFields'>
<span class='fieldsHeader'>$lang_adm->s_time:<br></span>
<textarea name='worktime' id='worktime'  wrap='soft' required/>
$lang_adm->as_time1 : 
$lang_adm->as_time2 : 
</textarea><br>
</div>
<div class='bottomFields'>
<span class='fieldsHeader'>$lang_adm->s_more_info<br></span>
<textarea name='moreinfo' wrap='soft' maxlength='200' placeholder='$lang_adm->as_10_char'></textarea>
</div>
<div class='bottomFields checkboxes1'>
	<input type='checkbox' name='rasr'/> $lang_adm->s_rassrochka <br>
	<input type='checkbox' name='firm'/> $lang_adm->s_firm_salon <br>
	<input type='checkbox' name='monobrend'/> $lang_adm->s_mono_salon <br>
</div>
<div style='display:inline-block; width:100%; min-width:400px; background:white;'>
<span class='fieldsHeader'>$lang_adm->as_set_samples:<br></span>

";

// выводим кухни как образцы
$sqlKitchens = "SELECT name FROM gi_kitchen ORDER BY name";
$reultKitchens = $wpdb->get_results($sqlKitchens);
foreach ($reultKitchens as $rowKitchens){
	$name = $rowKitchens -> name;
	$a.="
	<div style='display:inline-block; vertical-align:top; width:24%;'>
	<input type='checkbox' name='obrazec[]' value='$name' style='margin-left:5px;'/> $name 
	</div>
	";
}

$a.="
</div>
	<span class='fieldsHeader linksHeader'>$lang_adm->as_links:</span>
	<div class='salonaddlinks'>
	<div class='link_divs'>
	<input name='vk' type='url' placeholder='https://vk.com'/> 
	</div>
	<div class='link_divs'>
	<input name='fb' type='url' placeholder='https://facebook.com'/> 
	</div>
	<div class='link_divs'>
	<input name='insta' type='url' placeholder='https://instagram.com'/> 
	</div>
	<div class='link_divs'>
	<input name='site' type='url' placeholder='https://site.com'/> 
	</div>
</div>

<span class='fieldsHeader photoHeader'>$lang_adm->as_photo:</span>
<a target='_blank' class='photo_requirement' href='https://gi.by/kd/files/%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%20%D0%BA%20%D1%84%D0%BE%D1%82%D0%BE%D0%B3%D1%80%D0%B0%D1%84%D0%B8%D1%8F%D0%BC%20%D0%BA%D0%B0%D0%B1%D0%B8%D0%BD%D0%B5%D1%82%20%D0%B4%D0%B8%D0%BB%D0%B5%D1%80%D0%B0%201%20%D1%84%D0%BE%D1%82%D0%BE.pdf'> $lang_adm->s_photos_requirement</a>
<br><br><input type='file' id='avatar' name='avatar' required/><br><br>
<span id='outputMulti'></span><br>
<br>
<input type='submit' class='addsalonbtn' value='$lang_adm->as_add'/>
<script>

/*ГРУЗИМ КАРТУ*/
var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	maxZoom: 18,
	attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
}),
latlng = L.latLng(54.82, 27.24);
var map = L.map('map', {center: latlng, zoom: 5, layers: [tiles]});

var myIcon = L.icon({
    iconUrl: '/wp-content/themes/wp-diary/images/mymarker.png',
    iconSize: [28, 37],
	iconAnchor: [18, 50]
});

var geocoder = L.Control.geocoder({
  defaultMarkGeocode: false
})
.on('markgeocode', function(e) {
	var center = e.geocode.center;
	map.setView(new L.LatLng(center.lat, center.lng), 17);	
})
.addTo(map);

var mymarker = L.marker([54.82, 27.24], {icon: myIcon});
map.on('click', function(clicked){
	var mylatlng = clicked.latlng;
	var markerlat = mylatlng.lat;
	var markerlng = mylatlng.lng;
	var newLatLng = new L.LatLng(markerlat, markerlng);
    map.addLayer(mymarker);
	mymarker.setLatLng(newLatLng);
	document.getElementById('lat').value = markerlat; 
	document.getElementById('long').value = markerlng;
});	
	
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
          span.innerHTML = [\"<img style='max-height:150px;' class='img-thumbnail' src='\", e.target.result,
                            \"' title='\", escape(theFile.name), \"'/>\"].join('');
          document.getElementById('outputMulti').insertBefore(span, null);
        };
      })(f);
      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
  }
  document.getElementById('avatar').addEventListener('change', handleFileSelectMulti, false);
</script>

";


$a.="</form>";

	/*
if(isset($_POST['lat']) and isset($_POST['long']) and isset($_POST['country']) and isset($_POST['city']) and isset($_POST['address']) and isset($_POST['mail']) and isset($_POST['phones']) and isset($_POST['worktime']))
*/
if(!empty($_POST)){
	if(isset($_POST['thisname'])) $name = $_POST['thisname'];
	$name_eng = str_replace($rus, $lat, $name);
	if(isset($_POST['vk'])) $vk = $_POST['vk'];
	if(isset($_POST['fb'])) $fb = $_POST['fb'];
	if(isset($_POST['insta'])) $insta = $_POST['insta'];
	if(isset($_POST['site'])) $site = $_POST['site'];
	if(isset($_POST['rasr'])) $rasr = "yes"; else $rasr = "no";
	if(isset($_POST['monobrend'])) $monobrend = "yes"; else $monobrend = "no";
	if(isset($_POST['firm'])) $firm = "yes"; else $firm = "no";
	$lat = $_POST['lat'];
	$lat = round($lat, 6);
	$long = $_POST['long'];
	$long = round($long, 6);
	$lat_lng = $lat . "_" . $long;
	$country = $_POST['country'];
	$city = $_POST['city'];
	$address = $_POST['address'];
	$mail = $_POST['mail'];
	//$phones = $_POST['phones'];
	$phones = str_replace("\r\n", "<br>", $_POST['phones']);
	$worktime = str_replace("\r\n", "<br>", $_POST['worktime']);
	$moreinfo = $_POST['moreinfo'];
	if(!empty($_POST['obrazec'])){
		$samples = implode(", ", $_POST['obrazec']);
	}
	$images_dir = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/avatars_of_salons";
	if (!file_exists($images_dir)) {
				mkdir($images_dir);
				mkdir($images_dir . "/images/");
				mkdir($images_dir . "/thumbs/");
	}
	
	//Если при всем при этом еще и картинка загружена, то...
	if(!empty($_FILES['avatar']) and $_FILES['avatar']['name']==true){
		
		if(is_uploaded_file($_FILES["avatar"]["tmp_name"])){					
			$tmp_avatar = $_FILES['avatar']['tmp_name'];
			$path_parts_avatar  = pathinfo($_FILES['avatar']['name']);
			$extension_avatar = $path_parts_avatar['extension'];
			$avatar = $lat . "_" . $long . "." . $extension_avatar;
			$avatarlink = $images_dir . "/images/" . $lat . "_" . $long . "." . $extension_avatar;			
			$thumblink = $images_dir . "/thumbs/" . $lat . "_" . $long . "." . $extension_avatar;			
			$avatarlink = str_replace($rus, $lat, $avatarlink);
			$thumblink = str_replace($rus, $lat, $thumblink);
			
			
			if ($extension_avatar == "png" or $extension_avatar=="jpg" or $extension_avatar=="jpeg" or $extension_avatar=="JPG" or $extension_avatar=="PNG"){
				//задаем размер аватару
				list($width, $height) = getimagesize($tmp_avatar);
				$otnosh = $width / $height;
				
				//размеры основной картинки
				if ($width >= 1024) {$newWidth = 1024;}
				else {$newWidth = $width;}
				$newHeight = $newWidth / $otnosh;
				$newHeight = round($newHeight);
				//размеры миниатюры
				$thunmbWidth = 400;
				$thunmbHeight = $thunmbWidth / $otnosh;
				$thunmbHeight = round($thunmbHeight);
						
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				$image_th = imagecreatetruecolor($thunmbWidth, $thunmbHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ ДЛЯ МИНИАТЮРЫ
				switch($extension_avatar){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
				}
				//Владелец салона
				if($current_user == "admin" and isset($_POST['user_select'])){
					$username = $_POST['user_select'];
				}
				else $username = $current_user;
				//Вносим изменения в БД
				$sql = "INSERT INTO `gi_salons` 
						(`name`, `name_eng`, `country`, `city`, `address`, `lat_lng`, `phones`, `mailbox`, `worktime`, `samples`, `avatar`, `firm`, `monobrend`, `rassrochka`, `moreinfo`, `vk`, `fb`, `insta`, `site`, `moderate`, `user`) 
						VALUES 
						('$name', '$name_eng', '$country', '$city', '$address', '$lat_lng', '$phones', '$mail', '$worktime', '$samples', '$avatar', '$firm', '$monobrend', '$rasr', '$moreinfo', '$vk', '$fb', '$insta', '$site', 'no', '$username');";
				$result = $wpdb->get_results($sql);
						
				echo "<h1>Салон добавлен!</h1>";
				
				$message = "На модерацию поступил салон от $username! Проверьте админку сайта gi.by";
				$subject = "Новый салон";
				// mail('info@gi.by', $subject, $message);
				$script = "
					<script type='text/javascript' src='/wp-content/plugins/bp_add_salon/js/scripts.js'></script>
					<script>
							let message = '$message';
							let subject = '$subject';
							
							console.log(message);
							console.log(subject);
							if (message && subject) {
								sendAddSalon(message, subject);
								alert('Правки внесены');
								window.location.replace('/my-salons/');
							}
				</script>
				";
				echo $script;
				if ($result) {
					echo $script;
				}
				
			}
		}
	}	
			
			
			
}
echo $a;
}

function add_salon() {
global $wpdb;
	
$plugins_url = plugins_url();

$phpself = $_SERVER['PHP_SELF'];
add_js_scripts_add_salon();
$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
$current_user = wp_get_current_user()->user_login;	
echo "<h1>Добавить новый салон на карту</h1>";
echo "После заполнения всех полей салон будет отправлен на модерацию<br><br>";
$a ="
	<link rel='stylesheet' href='../osm/add_place/leaflet.css'/>
	<script src='../osm/add_place/leaflet-src.js'></script>

	<link rel='stylesheet' href='../osm/add_place/Control.Geocoder.css' />
	<script src='../osm/add_place/Control.Geocoder.js'></script>
	
<form method='POST' enctype='multipart/form-data'>

<span class='fieldsHeader' style='font-size:22px; color:#3faf4e;'>Поставьте метку на карте:</span>
<i>(Кликните на карту - появится маркер)</i><br>
<div id='map' class='map' style='height: 510px; width:68%; float:left; margin:10px 1%;'></div>
	

<div class='rightinputs' style='width:29%; float:left;'>
";
//Если админ - выбор юзера
if($current_user == "admin"){
	$user_select = "
	<select name='user_select' required style='width:100%; margin:10px 0 20px 0;'>
	";
	//$sql_user_select = "SELECT user_login, ID FROM wp_users ORDER BY user_login ASC";
	$sql_user_select = "SELECT DISTINCT user_id FROM wp_usermeta WHERE meta_key = 'city' OR meta_key='nickname' ORDER BY meta_value ASC";

	$result_user_select = $wpdb->get_results($sql_user_select);
	foreach($result_user_select as $row_user_select){
		//$user_name = $row_user_select->user_login;
		$user_id = $row_user_select->user_id;
		$user_name = get_user_meta($user_id, 'nickname', true);
		$user_city = get_user_meta($user_id, 'city', true);
		if($user_name == $current_user){
			$selected_admin = "selected";
		}
		else {
			$selected_admin ="";
		} 
		
		$user_city_echo = !empty($user_city) ? $user_city . ", " : '';
		$user_select .="<option value='$user_name' $selected_admin>$user_city_echo $user_name</option>";
	}
	$user_select .="
	</select>
	";
}
else $user_select = "";
$a.="
	$user_select
	<div class='autoinputdiv'>
		<span class='fieldsHeader'>НАЗВАНИЕ САЛОНА:<br></span>
		<input id='name' name='name' required/><br>
	</div>

	<div class='input_lat autoinputdiv'>
		<span class='fieldsHeader'>Широта:<br></span>
		<input id='lat' name='lat' required/><br>
	</div>
	<div class='input_long autoinputdiv'>
		<span class='fieldsHeader'>Долгота:<br></span>
		<input id='long' name='long' required/><br>
	</div>
	<div class='input_place autoinputdiv'>
		<span class='fieldsHeader'>Страна:<br></span>
		<select id='place' name='country' required style='width:100%;'>
			<option selected disabled value=''>Выберите страну</option>
			<option value='Беларусь'>Беларусь</option>
			<option value='Украина'>Украина</option>
			<option value='Россия'>Россия</option>
			<option value='Казахстан'>Казахстан</option>
			<option value='Германия'>Германия</option>
			<option value='Польша'>Польша</option>
			<option value='Швеция'>Швеция</option>
			<option value='Франция'>Франция</option>
			<option value='ОАЭ'>ОАЭ</option>
		</select><br><br>
	</div>
	<span class='fieldsHeader'>Город:<br></span>
	<input name='city' id='city' required/><br>
	<span class='fieldsHeader'>Адрес:<br></span>
	<input name='address' id='address' required/><br>
	<span class='fieldsHeader'>Email адрес:<br></span>
	<input name='mail' id='mail' required/><br>
	<span class='fieldsHeader'>Телефоны (с новой строки):<br></span>
	<textarea name='phones' id='phones' wrap='soft' style='min-height:80px;' wrap='soft' required/></textarea><br>

</div>
<div style='clear:both'></div>


<div class='bottomFields' style='float:left; width:calc(19% - 20px); margin-right:1%; background:white; padding:10px; height:130px;'>
<span class='fieldsHeader'>Время работы:<br></span>
<textarea name='worktime' id='worktime'  wrap='soft' required/>
пн. - пт. : 
cб., вс. : 
</textarea><br>
</div>
<div class='bottomFields' style='float:left; width:calc(19% - 20px); margin-right:1%; background:white; padding:10px; height:130px;'>
<span class='fieldsHeader'>Доп. информация (макс 100 знаков):<br></span>
<textarea name='moreinfo' wrap='soft' maxlength='200'></textarea>
</div>
<div style='float:left; width:calc(19% - 20px); margin-right:1%; background:white; padding:10px; height:130px;'>
	<span class='fieldsHeader'>Установите галочки:</span>
	<input type='checkbox' name='rasr'/> Рассрочка <br>
	<input type='checkbox' name='firm'/> Фирменный салон <br>
	<input type='checkbox' name='monobrend'/> Монобрендовый салон <br>
</div>
<div style='float:left; width:calc(39% - 20px); min-width:400px; background:white; padding:10px; height:130px;'>
<span class='fieldsHeader'>Выберите образцы салона:<br></span>

";

// выводим кухни как образцы
$sqlKitchens = "SELECT name FROM gi_kitchen ORDER BY name";
$reultKitchens = $wpdb->get_results($sqlKitchens);
foreach ($reultKitchens as $rowKitchens){
	$name = $rowKitchens -> name;
	$a.="
	<div style='float:left; width:25%;'>
	<input type='checkbox' name='obrazec[]' value='$name' style='margin-left:5px;'/> $name 
	</div>
	";
}

$a.="
</div>
<div style='clear:both;'></div>
<div style='float:left; width:25%; margin-top:10px;'>
<span class='fieldsHeader'>Ссылка вконтакте:</span>
<input name='vk' type='url' placeholder='https://vk.com' style='width:95%;'/> 
</div>
<div style='float:left; width:25%; margin-top:10px;'>
<span class='fieldsHeader'>Ссылка facebook:</span>
<input name='fb' type='url' placeholder='https://facebook.com' style='width:95%;'/> 
</div>
<div style='float:left; width:25%; margin-top:10px;'>
<span class='fieldsHeader'>Ссылка instagram:</span>
<input name='insta' type='url' placeholder='https://instagram.com' style='width:95%;'/> 
</div>
<div style='float:left; width:25%; margin-top:10px;'>
<span class='fieldsHeader'>Ссылка на сайт:</span>
<input name='site' type='url' placeholder='https://site.com' style='width:95%;'/> 
</div>
<div style='clear:both;'></div><br><br>
<span class='fieldsHeader'>Загрузите фото салона:</span><br>
<a target='_blank' href='https://gi.by/kd/files/%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%20%D0%BA%20%D1%84%D0%BE%D1%82%D0%BE%D0%B3%D1%80%D0%B0%D1%84%D0%B8%D1%8F%D0%BC%20%D0%BA%D0%B0%D0%B1%D0%B8%D0%BD%D0%B5%D1%82%20%D0%B4%D0%B8%D0%BB%D0%B5%D1%80%D0%B0%201%20%D1%84%D0%BE%D1%82%D0%BE.pdf'> Следуйте требованиям к фотографиям!</a>
<br><br><input type='file' id='avatar' name='avatar' required/><br><br>
<span id='outputMulti'></span><br>
<br><br>
<input type='submit' class='okBtn' value='Добавить салон'/>
<script>

/*ГРУЗИМ КАРТУ*/
var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	maxZoom: 18,
	attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
}),
latlng = L.latLng(54.82, 27.24);
var map = L.map('map', {center: latlng, zoom: 5, layers: [tiles]});

var myIcon = L.icon({
    iconUrl: '/wp-content/themes/wp-diary/images/mymarker.png',
    iconSize: [28, 37],
	iconAnchor: [18, 50]
});

var geocoder = L.Control.geocoder({
  defaultMarkGeocode: false
})
.on('markgeocode', function(e) {
	var center = e.geocode.center;
	map.setView(new L.LatLng(center.lat, center.lng), 17);	
})
.addTo(map);

var mymarker = L.marker([54.82, 27.24], {icon: myIcon});
map.on('click', function(clicked){
	var mylatlng = clicked.latlng;
	var markerlat = mylatlng.lat;
	var markerlng = mylatlng.lng;
	var newLatLng = new L.LatLng(markerlat, markerlng);
    map.addLayer(mymarker);
	mymarker.setLatLng(newLatLng);
	document.getElementById('lat').value = markerlat; 
	document.getElementById('long').value = markerlng;
});	
	
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
          span.innerHTML = [\"<img style='max-height:150px;' class='img-thumbnail' src='\", e.target.result,
                            \"' title='\", escape(theFile.name), \"'/>\"].join('');
          document.getElementById('outputMulti').insertBefore(span, null);
        };
      })(f);
      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
  }
  document.getElementById('avatar').addEventListener('change', handleFileSelectMulti, false);
</script>

";


$a.="</form>";

	/*
if(isset($_POST['lat']) and isset($_POST['long']) and isset($_POST['country']) and isset($_POST['city']) and isset($_POST['address']) and isset($_POST['mail']) and isset($_POST['phones']) and isset($_POST['worktime']))
*/
if(!empty($_POST)){
	if(isset($_POST['name'])) $name = $_POST['name'];
	$name_eng = str_replace($rus, $lat, $name);
	if(isset($_POST['vk'])) $vk = $_POST['vk'];
	if(isset($_POST['fb'])) $fb = $_POST['fb'];
	if(isset($_POST['insta'])) $insta = $_POST['insta'];
	if(isset($_POST['site'])) $site = $_POST['site'];
	if(isset($_POST['rasr'])) $rasr = "yes"; else $rasr = "no";
	if(isset($_POST['monobrend'])) $monobrend = "yes"; else $monobrend = "no";
	if(isset($_POST['firm'])) $firm = "yes"; else $firm = "no";
	$lat = $_POST['lat'];
	$lat = round($lat, 6);
	$long = $_POST['long'];
	$long = round($long, 6);
	$lat_lng = $lat . "_" . $long;
	$country = $_POST['country'];
	$city = $_POST['city'];
	$address = $_POST['address'];
	$mail = $_POST['mail'];
	//$phones = $_POST['phones'];
	$phones = str_replace("\r\n", "<br>", $_POST['phones']);
	$worktime = str_replace("\r\n", "<br>", $_POST['worktime']);
	$moreinfo = $_POST['moreinfo'];
	if(!empty($_POST['obrazec'])){
		$samples = implode(", ", $_POST['obrazec']);
	}
	$images_dir = "../wp-content/uploads/avatars_of_salons";
	if (!file_exists($images_dir)) {
				mkdir($images_dir);
				mkdir($images_dir . "/images/");
				mkdir($images_dir . "/thumbs/");
	}
	
	//Если при всем при этом еще и картинка загружена, то...
	if(!empty($_FILES['avatar']) and $_FILES['avatar']['name']==true){
		
		if(is_uploaded_file($_FILES["avatar"]["tmp_name"])){					
			$tmp_avatar = $_FILES['avatar']['tmp_name'];
			$path_parts_avatar  = pathinfo($_FILES['avatar']['name']);
			$extension_avatar = $path_parts_avatar['extension'];
			$avatar = $lat . "_" . $long . "." . $extension_avatar;
			$avatarlink = $images_dir . "/images/" . $lat . "_" . $long . "." . $extension_avatar;			
			$thumblink = $images_dir . "/thumbs/" . $lat . "_" . $long . "." . $extension_avatar;			
			$avatarlink = str_replace($rus, $lat, $avatarlink);
			$thumblink = str_replace($rus, $lat, $thumblink);
			
			if ($extension_avatar == "png" or $extension_avatar=="jpg" or $extension_avatar=="jpeg" or $extension_avatar=="JPG" or $extension_avatar=="PNG"){
				//задаем размер аватару
				list($width, $height) = getimagesize($tmp_avatar);
				$otnosh = $width / $height;
				
				//размеры основной картинки
				if ($width >= 1024) {$newWidth = 1024;}
				else {$newWidth = $width;}
				$newHeight = $newWidth / $otnosh;
				$newHeight = round($newHeight);
				//размеры миниатюры
				$thunmbWidth = 400;
				$thunmbHeight = $thunmbWidth / $otnosh;
				$thunmbHeight = round($thunmbHeight);
						
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				$image_th = imagecreatetruecolor($thunmbWidth, $thunmbHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ ДЛЯ МИНИАТЮРЫ
				switch($extension_avatar){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
				}
				//Владелец салона
				if($current_user == "admin" and isset($_POST['user_select'])){
					$username = $_POST['user_select'];
				}
				else $username = $current_user;
				//Вносим изменения в БД
				$sql = "INSERT INTO `gi_salons` 
						(`name`, `name_eng`, `country`, `city`, `address`, `lat_lng`, `phones`, `mailbox`, `worktime`, `samples`, `avatar`, `firm`, `monobrend`, `rassrochka`, `moreinfo`, `vk`, `fb`, `insta`, `site`, `moderate`, `user`) 
						VALUES 
						('$name', '$name_eng', '$country', '$city', '$address', '$lat_lng', '$phones', '$mail', '$worktime', '$samples', '$avatar', '$firm', '$monobrend', '$rasr', '$moreinfo', '$vk', '$fb', '$insta', '$site', 'no', '$username');";
				$result = $wpdb->get_results($sql);
						
				echo "<h1>Салон добавлен!</h1>";
				
				$message = "На модерацию поступил салон от $username! Проверьте админку сайта gi.by";
				$subject = "Модерация салонов";
				// mail('info@gi.by', $subject, $message);
				?>
				<script>
					let okBtn = document.querySelector('.okBtn');
					okBtn.addEventListener('click', function () {
						let message = <?php echo $message; ?>
						let subject = <?php echo $subject; ?>
						if (message && subject) {
							sendAddSalon(message, subject);
							// alert('Правки внесены');
							// window.location.reload();
						}
					})
				</script>	
				<?php
			}
		}
	}	
			
			
			
}
echo $a;
}


function moderate_salon(){
	add_js_scripts_add_salon();
	global $wpdb;
	$a = '';
	$a.="<div style='width:100%; height:max-content; text-align:center; margin-top:20px;'>";
	$sql = "SELECT * FROM gi_salons WHERE moderate = 'no'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$newid = $row -> newid;
		$name = $row -> name;
		$country = $row -> country;
		$city = $row -> city;
		$address = $row -> address;
		$lat_lng = $row -> lat_lng;
		$phones = $row -> phones;
		$mailbox = $row -> mailbox;
		$worktime = $row -> worktime;
		$moreinfo = $row -> moreinfo;
		$vk = $row -> vk;
		$fb = $row -> fb;
		$insta = $row -> insta;
		$site = $row -> site;
		$avatar = $row -> avatar;
		$user = $row -> user;
		$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/" . $avatar;
		$avatarlink = "../wp-content/uploads/avatars_of_salons/images/" . $avatar;
		list($lat,$lng)= explode ("_", $lat_lng);
		
		$firm = $row -> firm;
		$monobrend = $row -> monobrend;
		
		$rassr = $row -> rassrochka;
		if ($firm == "yes"){$checkedfirm = "checked";} else $checkedfirm = "";
		if ($monobrend == "yes"){$checkedmonobrend = "checked";} else $checkedmonobrend = "";
		if ($rassr == "yes"){$checkedrassr = "checked";} else $checkedrassr = "";
		
		$samples = $row -> samples;
		//Образцы в наличии
		$sqlObraz = "SELECT name FROM gi_kitchen";
		$resultObraz = $wpdb->get_results($sqlObraz);
		$c="";
		foreach($resultObraz as $rowObraz){
			$allKitchens = $rowObraz->name;
			$findSample = stripos($samples, $allKitchens);
			//проверяем содержится ли в базе кухонь список материалов со значением материала из базы материалов - как-то так :)
			if ($findSample !== FALSE){
				$checked =  "checked";
			}
			else {$checked = "";}
			$c.="<div style='width:30%; float:left;'><input type='checkbox' name='newSamples[]' value='$allKitchens' $checked> $allKitchens</div> ";
		}
		$c.="<div style='clear:both;'> </div>";
		$a.="
		<form method='POST' enctype='multipart/form-data'>
		<div style='width:calc(98% - 20px); padding:20px 10px; text-align:left; min-width:200px; min-height:200px; background:white; border:1px solid #dcdcdc; display:inline-block; margin:10px 1%;'>
			<h2>ID номер салона: №$newid. Пользователь: $user</h2>
			<div style='width:31%; float:left; margin:0 1%;'>
				Название салона:<br>
				<textarea name='thisname' class='' style='height:25px; width:100%;'>$name</textarea><br>
				Страна:<br>
				<textarea name='country' class='' style='height:25px; width:100%;'>$country</textarea><br>
				Город:<br>
				<textarea name='city' class='' style='height:25px; width:100%;'>$city</textarea><br>
				Адрес салона:<br>
				<textarea name='address' class='' style='height:25px; width:100%;'>$address</textarea><br>
				Широта / долгота:<br>
				<textarea name='lat_lng' class='' style='height:25px; width:100%;'>$lat_lng</textarea><br>
				Телефоны:<br>
				<textarea name='phones' class='' style='height:25px; width:100%;'>$phones</textarea><br>
				Почтовый ящик:<br>
				<textarea name='mailbox' class='' style='height:25px; width:100%;'>$mailbox</textarea><br>
				Рабочее время:<br>	
				<textarea name='worktime' class='' style='height:50px; width:100%;'>$worktime</textarea><br>
				Доп. информация (макс 100 знаков):<br>	
				<textarea name='moreinfo' class='' style='height:50px; width:100%;' maxlength='200'>$moreinfo</textarea><br>
			</div>
			<div style='width:31%; float:left; margin:0 1%;'>
				Ссылка в ВК:<br>
				
				<input name='vk' type='url' class='' style='height:25px; width:100%;' value='$vk'>
				<br>
				Ссылка на фейсбук:<br>
				<input name='fb' type='url' class='' style='height:25px; width:100%;' value='$fb'><br>
				
				Ссылка в инстаграм:<br>
				<input name='insta' type='url' class='' style='height:25px; width:100%;' value='$insta'><br>
				Ссылка на сайт:<br>
				<input name='site' type='url' class='' style='height:25px; width:100%;' value='$site'><br><br>
				
				<input type='checkbox' name='firm' $checkedfirm/> Фирменный салон <br>
				<input type='checkbox' name='monobrend' $checkedmonobrend/> Монобрендовый салон <br>
				<input type='checkbox' name='rassr' $checkedrassr/> Рассрочка <br><br>
				Образцы в наличии: <br>
				$c
			</div>
			<div style='width:31%; float:left; margin:0 1%; min-height:450px;'>
				<div id='map' class='map' style='width:100%; height:230px;'>
					<iframe width='100%' height='100%' src='https://maps.google.com/maps?q=$lat,$lng&hl=ru&z=10&amp;output=embed'></iframe>
				</div>
				<br><br>
				<a target='_blank' href='https://gi.by/kd/files/%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%20%D0%BA%20%D1%84%D0%BE%D1%82%D0%BE%D0%B3%D1%80%D0%B0%D1%84%D0%B8%D1%8F%D0%BC%20%D0%BA%D0%B0%D0%B1%D0%B8%D0%BD%D0%B5%D1%82%20%D0%B4%D0%B8%D0%BB%D0%B5%D1%80%D0%B0%201%20%D1%84%D0%BE%D1%82%D0%BE.pdf'> Следуйте требованиям к фотографиям!</a>
				<br>Заменить изображение: <br>
				
				<input type='file' id='newAvatar' name='newAvatar'/> 

				<a href='$avatarlink' target='_blank'>
					<div class='myimagediv' style='width:100%; height:210px; margin-top:10px; background:url($thumblink) no-repeat; background-size:cover; background-position:center;'></div>
				</a>
				<div id='nowAvatar' style='width:100%; height:0px; position:relative; background:white; z-index:999; bottom:210px;'><span id='outputMulti'></span>
				</div>
				<br>
			</div>
				
				<div style='clear:both; margin-bottom:20px;'></div>
				
				<div id='dorab_window_$newid' style='width:400px; max-width:100%; margin:-32px 16px 32px 16px; padding:16px; position:relative; clear:both; box-shadow:3px 6px 12px rgba(1,1,1,0.2); display:none;'>
					Причина доработки:<br>
					<textarea name='dorab_reason' style='width:100%; height:100px;'></textarea>
					<button type='submit' name='dorab' value='$newid'  style='margin:32px 0 8px 0; border:none; color:white; background:#fcba03; padding:10px 20px; cursor:pointer;'>Отправить</button>			
				</div>
				
				
				<button type='submit' name='yes' value='$newid'  style='float:left; margin:0 10px 10px 10px; border:none; color:white; background:#2fb54c; padding:10px 20px; cursor:pointer;'>Принять</button>
				<div id='dorab' onclick='opendorabwindow($newid);' style='float:left; margin:0 10px 10px 10px; border:none; color:white; background:#fcba03; padding:10px 20px; cursor:pointer;'>На доработку</div>
				<button type='submit' name='no' value='$newid'  style='float:left; margin:0 0 10px 32px; border:none; color:white; background:#b5572f; padding:10px 20px; cursor:pointer;'>Отклонить</button>

		</div>
		</form>
		";
	}
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	if (isset($_POST['yes'])){
		$moderate_id = $_POST['yes'];
		if(isset($_POST['country'])  and isset($_POST['city']) and isset($_POST['address']) and isset($_POST['lat_lng']) and isset($_POST['phones']) and isset($_POST['mailbox']) and isset($_POST['worktime'])){
			$name = $_POST['thisname'];
			$name_eng = str_replace($rus, $lat, $name);
			$country = $_POST['country'];
			$city = $_POST['city'];
			$address = $_POST['address'];
			$lat_lng = $_POST['lat_lng'];
			$phones = $_POST['phones'];
			$mailbox = $_POST['mailbox'];
			$worktime = $_POST['worktime'];
			$moreinfo = $_POST['moreinfo'];
			$fb = $_POST['fb'];
			$vk = $_POST['vk'];
			$insta = $_POST['insta'];
			$site = $_POST['site'];
			if(!empty($_POST['firm'])){$firm = 'yes';} else {$firm = 'no';}
			if(!empty($_POST['monobrend'])){$monobrend = 'yes';} else {$monobrend = 'no';}
			if(!empty($_POST['rassr'])){$rassr = 'yes';} else {$rassr = 'no';}
			$newSamples = implode(", ", $_POST['newSamples']);
			$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/";
			$avatarlink = "../wp-content/uploads/avatars_of_salons/images/";
			

			//СОЗДАЕМ СТРАНИЧКУ В WP
			$post_title = $city;
			$post_content = "<!-- wp:paragraph --><p>[salons_print city='$post_title']</p><!-- /wp:paragraph -->";
			$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
			$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
			$post_name_eng = str_replace($rus, $lat, $post_title);
			$sql_page = "SELECT DISTINCT post_title FROM wp_posts WHERE post_title = '$post_title'";
			$result_page = $wpdb -> get_results($sql_page);
			$count_pages = count($result_page);

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
					'post_parent'	=> 46,
				);
				// Вставляем запись в базу данных
				$post_id = wp_insert_post( $post_data );
			}

				
			//SQL снова получить аватар
			$sqlAvatarAgain = "SELECT avatar FROM gi_salons WHERE newid = '$moderate_id'";
			$resultAvatarAgain = $wpdb->get_results($sqlAvatarAgain);
			foreach ($resultAvatarAgain as $rowAvatarAgain){
				$avatar = $rowAvatarAgain->avatar;
			}
			
			$oldavatar = $avatarlink . $avatar;
			$oldthumb = $thumblink . $avatar;
			
			//ГРУЗИМ НОВЫЙ АВАТАР
			//Если при всем при этом еще и картинка загружена, то...
			if(!empty($_FILES['newAvatar']) and $_FILES['newAvatar']['name']==true){

			if(is_uploaded_file($_FILES["newAvatar"]["tmp_name"])){					
				$tmp_avatar = $_FILES['newAvatar']['tmp_name'];
				$path_parts_avatar  = pathinfo($_FILES['newAvatar']['name']);
				$extension_avatar = $path_parts_avatar['extension'];
				$newAvatar = $lat_lng . "." . $extension_avatar;
				$avatarlink = $avatarlink . $newAvatar;			
				$thumblink = $thumblink . $newAvatar;			
				
			
				if ($extension_avatar == "png" or $extension_avatar=="jpg" or $extension_avatar=="jpeg" or $extension_avatar=="JPG" or $extension_avatar=="PNG"){
					//Удаляем старые
					unlink($oldavatar);
					unlink($oldthumb);
					
					//задаем размер аватару
					list($width, $height) = getimagesize($tmp_avatar);
					$otnosh = $width / $height;
					
					//размеры основной картинки
					if ($width >= 1024) {$newWidth = 1024;}
					else {$newWidth = $width;}
					$newHeight = $newWidth / $otnosh;
					$newHeight = round($newHeight);
					//размеры миниатюры
					$thunmbWidth = 400;
					$thunmbHeight = $thunmbWidth / $otnosh;
					$thunmbHeight = round($thunmbHeight);
						
					$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
					$image_th = imagecreatetruecolor($thunmbWidth, $thunmbHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ ДЛЯ МИНИАТЮРЫ
					switch($extension_avatar){
						case "jpg":	//СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
							imagejpeg($image_p, $avatarlink, 70);
							imagejpeg($image_th, $thumblink, 70);
							break;
						case "JPG":	//СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
							imagejpeg($image_p, $avatarlink, 70);
							imagejpeg($image_th, $thumblink, 70);
							break;
						case "jpeg": //СОЗДАНИЕ JPG
							$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
							imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
							imagejpeg($image_p, $avatarlink, 70);
							imagejpeg($image_th, $thumblink, 70);
							break;
						case "png":	//СОЗДАНИЕ ПНГ
							$image = imagecreatefrompng($tmp_avatar);
							//fot gallery
							imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_p, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
							imagepng($image_p, $avatarlink);
							//for thumb
							imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_th, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
							imagepng($image_th, $thumblink);
							break;
						case "PNG":	//СОЗДАНИЕ ПНГ
							$image = imagecreatefrompng($tmp_avatar);
							//fot gallery
							imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_p, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
							imagepng($image_p, $avatarlink);
							//for thumb
							imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
							imagesavealpha($image_th, true); //Включаем сохранение альфа канала
							imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
							imagepng($image_th, $thumblink);
							break;
						}
					
					
					}
				}
			$newAvatarSql = " avatar='$newAvatar',";
			}
			else $newAvatarSql = "";		
			$sql =  "UPDATE gi_salons SET 
			name = '$name', name_eng = '$name_eng', country='$country', city='$city', address='$address', lat_lng='$lat_lng', 
			phones='$phones', mailbox='$mailbox', worktime='$worktime', samples='$newSamples', $newAvatarSql firm='$firm',
			monobrend='$monobrend', rassrochka='$rassr', moreinfo='$moreinfo', vk='$vk', fb='$fb', insta='$insta', site='$site',
			moderate='yes' WHERE `newid` = '$moderate_id'";
			$result = $wpdb -> get_results($sql);
			
		}
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['dorab'])){
		$dorabid = $_POST['dorab'];
		$dorab_reason = $_POST['dorab_reason'];
		$sql = "UPDATE gi_salons SET moderate = 'dorab', dorab_reason='$dorab_reason' WHERE newid = '$dorabid'";
		$result = $wpdb -> get_results($sql);
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['no'])){
		$deleteid = $_POST['no'];
		//SQL снова получить аватар
		$sql = "SELECT avatar FROM gi_salons WHERE newid='$deleteid'";
		$result = $wpdb -> get_results($sql);
		foreach ($result as $row){
			$avatar = $row->avatar;
			$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/$avatar";
			$avatarlink = "../wp-content/uploads/avatars_of_salons/images/$avatar";
			unlink ($thumblink);
			unlink ($avatarlink);
		}
		$sqldelete = "DELETE FROM gi_salons WHERE newid='$deleteid'";
		$resultdelete = $wpdb->get_results($sqldelete);
		$sqldelete2 = "DELETE FROM gi_samples WHERE salon_id = '$deleteid'";
		$resultdelete2 = $wpdb->get_results($sqldelete2);
		echo "Салон удален";
		echo "<script>window.location.reload();</script>";
	}
	$a.="</div>";
	
	$a.="
	<script>	
	//РАЗМЕР КАРТИНКИ
	var myimagewidth = jQuery('.myimagediv').width();
	var myimageheight = myimagewidth / 1.6;
	jQuery('.myimagediv').css('height', myimageheight);
	
	
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
			  span.innerHTML = [\"<img style='max-height:210px; max-width:100%; position:relative; top:10px; z-index:999;' class='img-thumbnail' src='\", e.target.result,
								\"' title='\", escape(theFile.name), \"'/>\"].join('');
			  document.getElementById('outputMulti').insertBefore(span, null);
			  document.getElementById('nowAvatar').style.height ='210px';
			};
		  })(f);
		  // Read in the image file as a data URL.
		  reader.readAsDataURL(f);
		}
	}
	document.getElementById('newAvatar').addEventListener('change', handleFileSelectMulti, false);

	function opendorabwindow(windowid){
		jQuery('#dorab_window_' + windowid).show();
		//alert(windowid);
	}
  
</script>
	";
	
	echo $a;
}

function redact_salon(){
	add_js_scripts_add_salon();
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
		global $wpdb;
//Выводим страны из БД
	$a ="
	<form method='POST'>
	<br><br>
		<select id='countryselect' name='country'>
			<option selected disabled value=''>Выберите страну</option>";
	$sqlCountry = "SELECT DISTINCT country FROM gi_salons ORDER BY country";
	$resultCountry = $wpdb->get_results($sqlCountry);

	foreach ($resultCountry as $rowCountry) {
		$country = $rowCountry->country;
		$a.="
				<option value='$country'>$country</option>
				";
	}

	$a.="</select>";
//Выводим города которые принадлежат той или иной стране
	foreach ($resultCountry as $rowCountry) {
			$country = $rowCountry->country;
			$a.="<select name='city' id='$country' style='display:none;' onchange='selectedcity(this);'>
							<option selected disabled value=''>Выберите город</option>";
			$sqlCity = "SELECT DISTINCT city FROM gi_salons WHERE country='$country' ORDER BY city";	
			$resultCity = $wpdb->get_results($sqlCity);

			foreach ($resultCity as $rowCity){
					$city=$rowCity->city;
					$a.="<option value='$city'>$city</option>";
			}

			$a.="</select>";				
	}
	$a.="
	</form>
	";

	$a.="
	<div id='ajaxcontent' class='result'></div>
	";
	//Оформляем вывод городов в соответствии со страной
	foreach ($resultCountry as $rowCountry) {
			$country = $rowCountry->country;
			$a.="
			<script>
					var countryselect = document.getElementById('countryselect');
					jQuery(countryselect).on('change', function() {
					var mycountry = this.value;
					//ПОКАЗЫВАЕМ И СКРЫВАЕМ СЕЛЕКТЫ
					if (mycountry == '$country'){jQuery($country).show(); } else {jQuery($country).hide();}
					});
			</script>";
	}

	$a.="
	<script>
	//ТЕПЕРЬ ГОРОД ОТПРАВЛЯЕМ АЯКСОМ
	var cityselect = document.getElementsByName('city');
	jQuery(cityselect).on('change', function() {
		var mycity = this.value;
		jQuery.ajax({
			url: '/wp-content/plugins/bp_add_salon/ajax_redact.php',
			type: 'POST',
			data: {city:mycity},
			cache: false,
			success: function(html){  
				jQuery('#ajaxcontent').html(html);  
			} 
		});		
	});	


	function opendorabwindow(windowid){
		jQuery('#dorab_window_' + windowid).show();
		//alert(windowid);
	}
	
	</script>
	";
	echo $a;
	
	if (isset($_POST['yes'])){
		$moderate_id = $_POST['yes'];
		
		$name = $_POST['thisname'];
		
		$rank = $_POST['rank'];
		$oprosnik = $_POST['oprosnik'];

		$name_eng = str_replace($rus, $lat, $name);
		$country = $_POST['country'];
		$city = $_POST['city'];
		$address = $_POST['address'];
		$lat_lng = trim($_POST['lat_lng']);
		//$phones = $_POST['phones'];
		$mailbox = $_POST['mailbox'];
		//$worktime = $_POST['worktime'];
		
		$phones = str_replace("\r\n", "<br>", $_POST['phones']);
		$worktime = str_replace("\r\n", "<br>", $_POST['worktime']);


		$moreinfo = $_POST['moreinfo'];
		$fb = $_POST['fb'];
		$vk = $_POST['vk'];
		$insta = $_POST['insta'];
		$site = $_POST['site'];
		if(!empty($_POST['firm'])){$firm = 'yes';} else {$firm = 'no';}
		if(!empty($_POST['monobrend'])){$monobrend = 'yes';} else {$monobrend = 'no';}
		if(!empty($_POST['rassr'])){$rassr = 'yes';} else {$rassr = 'no';}
		$newSamples = implode(", ", $_POST['newSamples']);
		$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/";
		$avatarlink = "../wp-content/uploads/avatars_of_salons/images/";
		
		//SQL снова получить аватар
		$sqlAvatarAgain = "SELECT avatar FROM gi_salons WHERE newid = '$moderate_id'";
		$resultAvatarAgain = $wpdb->get_results($sqlAvatarAgain);
		foreach ($resultAvatarAgain as $rowAvatarAgain){
			$avatar = $rowAvatarAgain->avatar;
		}
		
		$oldavatar = $avatarlink . $avatar;
		$oldthumb = $thumblink . $avatar;
		
		//ГРУЗИМ НОВЫЙ АВАТАР
		//Если при всем при этом еще и картинка загружена, то...
		if(!empty($_FILES['newAvatar']) and $_FILES['newAvatar']['name']==true){

		if(is_uploaded_file($_FILES["newAvatar"]["tmp_name"])){					
			$tmp_avatar = $_FILES['newAvatar']['tmp_name'];
			$path_parts_avatar  = pathinfo($_FILES['newAvatar']['name']);
			$extension_avatar = $path_parts_avatar['extension'];
			$newAvatar = $lat_lng . "." . $extension_avatar;
			$avatarlink = $avatarlink . $newAvatar;			
			$thumblink = $thumblink . $newAvatar;			
			
		
			if ($extension_avatar == "png" or $extension_avatar=="jpg" or $extension_avatar=="jpeg" or $extension_avatar=="JPG" or $extension_avatar=="PNG"){
				//Удаляем старые
				unlink($oldavatar);
				unlink($oldthumb);
				
				//задаем размер аватару
				list($width, $height) = getimagesize($tmp_avatar);
				$otnosh = $width / $height;
				
				//размеры основной картинки
				if ($width >= 1024) {$newWidth = 1024;}
				else {$newWidth = $width;}
				$newHeight = $newWidth / $otnosh;
				$newHeight = round($newHeight);
				//размеры миниатюры
				$thunmbWidth = 400;
				$thunmbHeight = $thunmbWidth / $otnosh;
				$thunmbHeight = round($thunmbHeight);
					
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				$image_th = imagecreatetruecolor($thunmbWidth, $thunmbHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ ДЛЯ МИНИАТЮРЫ
				switch($extension_avatar){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
					}
				
				
				}
			}
		$newAvatarSql = " avatar='$newAvatar',";
		}
		else $newAvatarSql = "";		
		$sql =  "UPDATE gi_salons SET 
		name = '$name', name_eng = '$name_eng', country='$country', city='$city', address='$address', lat_lng='$lat_lng', 
		phones='$phones', mailbox='$mailbox', worktime='$worktime', samples='$newSamples', $newAvatarSql firm='$firm',
		monobrend='$monobrend', rassrochka='$rassr', moreinfo='$moreinfo', vk='$vk', fb='$fb', insta='$insta', site='$site', moderate='yes', `rank`='$rank', oprosnik='$oprosnik'
		WHERE `newid` = '$moderate_id'";
		$result = $wpdb -> get_results($sql);
		echo "<h2>Салон отредактирован</h2>
		Страница сейчас перезагрузится.";
		// echo "<script>window.location.reload();</script>";
		
	}
	if (isset($_POST['no'])){
		$deleteid = $_POST['no'];
		//SQL снова получить аватар
		$sql = "SELECT avatar FROM gi_salons WHERE newid='$deleteid'";
		$result = $wpdb -> get_results($sql);
		foreach ($result as $row){
			$avatar = $row->avatar;
			$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/$avatar";
			$avatarlink = "../wp-content/uploads/avatars_of_salons/images/$avatar";
			unlink ($thumblink);
			unlink ($avatarlink);
		}
		$sqldelete = "DELETE FROM gi_salons WHERE newid='$deleteid'";
		$resultdelete = $wpdb->get_results($sqldelete);
		$sqldelete2 = "DELETE FROM gi_samples WHERE salon_id = '$deleteid'";
		$resultdelete2 = $wpdb->get_results($sqldelete2);
		echo "<h2>Салон удален</h2>
		Страница сейчас перезагрузится.";
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['dorab'])){
		$dorabid = $_POST['dorab'];
		$dorab_reason = $_POST['dorab_reason'];
		$sql = "UPDATE gi_salons SET moderate = 'dorab', dorab_reason='$dorab_reason' WHERE newid = '$dorabid'";
		$result = $wpdb -> get_results($sql);
		echo "<script>window.location.reload();</script>";
	}
	if (isset($_POST['block'])){
		$blockid = $_POST['block'];
		$sql = "UPDATE gi_salons SET moderate = 'banned' WHERE newid = '$blockid'";
		$result = $wpdb -> get_results($sql);
		echo "<script>window.location.reload();</script>";
	}
}



function my_salons(){
	
	global $wpdb;
	global $user_role;
	global $lang_adm;
	if($user_role == 'designer_architect'){
		header("Location: /designer-conditions/");
	}
	
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	
	$current_user = wp_get_current_user()->user_login;
	$a ="
	<div style='float:left;'>
		<h1>$lang_adm->ms_my_salons</h1>
	</div>
	<div class='addsalonandsample'>
		<a href='/add-salon/'><button class='positive_btn'>$lang_adm->ms_add_salon</button></a>
	</div>
	<div style='clear:both'></div>
	";
	$a.="
	<div style='width:100%; height:max-content; text-align:center;'>";
	
	$sql = "SELECT * FROM gi_salons WHERE user='$current_user'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$newid = $row -> newid;
		$moderate = $row -> moderate;
		$name = $row -> name;
		$country = $row -> country;
		$city = $row -> city;
		$address = $row -> address;
		$lat_lng = $row -> lat_lng;
		$phones = $row -> phones;
		$phones = str_replace('<br>', '&#13;&#10;', $phones);
		$mailbox = $row -> mailbox;
		$worktime = $row -> worktime;
		$worktime = str_replace('<br>', '&#13;&#10;', $worktime);
		$moreinfo = $row -> moreinfo;
		$vk = $row -> vk;
		$fb = $row -> fb;
		$insta = $row -> insta;
		$site = $row -> site;
		$avatar = $row -> avatar;
		$user = $row -> user;
		$dorab_reason = $row -> dorab_reason;
		$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/" . $avatar;
		$avatarlink = "../wp-content/uploads/avatars_of_salons/images/" . $avatar;
		list($lat,$lng)= explode ("_", $lat_lng);
		
		$firm = $row -> firm;
		$monobrend = $row -> monobrend;
		$rassr = $row -> rassrochka;
		if ($firm == "yes"){$checkedfirm = "checked";} else $checkedfirm = "";
		if ($monobrend == "yes"){$checkedmonobrend = "checked";} else $checkedmonobrend = "";
		if ($rassr == "yes"){$checkedrassr = "checked";} else $checkedrassr = "";
		
		$samples = $row -> samples;
		//Образцы в наличии
		$sqlObraz = "SELECT name FROM gi_kitchen";
		$resultObraz = $wpdb->get_results($sqlObraz);
		$rand = rand(1,100000); //добавляем гет запросом в картинку (аватар), чтоб не кешировалась. Шах и мат, кеш!
		$c="<div class='mysalons_obr_checkboxes'>";
		foreach($resultObraz as $rowObraz){
			$allKitchens = $rowObraz->name;
			$findSample = stripos($samples, $allKitchens);
			//проверяем содержится ли в базе кухонь список материалов со значением материала из базы материалов - как-то так :)
			if ($findSample !== FALSE){
				$checked =  "checked";
			}
			else {$checked = "";}
			$c.="<div><input type='checkbox' name='newSamples[]' value='$allKitchens' $checked> $allKitchens</div> ";
		}
		$c.="</div>";
		
		//ПРОШЕЛ МОДЕРАЦИЮ ИЛИ НЕТ
		switch ($moderate){
			case "yes" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:0; right:0; background:#52D172; color:white;'>$lang_adm->ms_public</div>";  break;
			case "no" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:0; right:0; background:#6694B7; color:white;'>$lang_adm->ms_moder</div>";  break;
			case "dorab" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:0; right:0; background:#EAC766; color:white;'><div class='dorab_info'>$dorab_reason<div></div></div>$lang_adm->ms_dorab</div>";  break;
			case "banned" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:0; right:0; background:#BB655D; color:white;'>$lang_adm->ms_blocked</div>";  break;
		}

		$a.="
		<form method='POST' enctype='multipart/form-data'>
		<div class='mysalondiv'>
			$moderate_div
			<h4>$lang_adm->ms_id_salon: $newid. $lang_adm->ms_user: $user</h4><br>
			<div class='mysalondiv_column column1'>
				<div id='map' class='mysalonmap' style='width:100%; height:260px;'>
					<iframe width='100%' height='100%' src='https://maps.google.com/maps?q=$lat,$lng&hl=ru&z=10&amp;output=embed'></iframe>
				</div><br><br>
				<a target='_blank' style='color:#e78c68' href='https://gi.by/kd/files/%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%20%D0%BA%20%D1%84%D0%BE%D1%82%D0%BE%D0%B3%D1%80%D0%B0%D1%84%D0%B8%D1%8F%D0%BC%20%D0%BA%D0%B0%D0%B1%D0%B8%D0%BD%D0%B5%D1%82%20%D0%B4%D0%B8%D0%BB%D0%B5%D1%80%D0%B0%201%20%D1%84%D0%BE%D1%82%D0%BE.pdf'> $lang_adm->s_photos_requirement</a>
				<br>$lang_adm->ms_photo_replace: 
				<br>
				
				<input type='file' id='newAvatar' name='newAvatar'/> 

				<a href='$avatarlink' target='_blank'>
					<div class='myimagediv' style='background:url($thumblink?v=$rand) no-repeat; background-size:cover; background-position:center;'></div>
				</a>
				<div id='nowAvatar' style='width:100%; height:0px; position:relative; background:white; z-index:999; bottom:210px;'><span id='outputMulti'></span>
				</div>
				<br>
			</div>
			<div class='mysalondiv_column column2'>
				<b>$lang_adm->s_name:</b><br>
				<textarea name='thisname' class='' style='height:25px; width:100%;'>$name</textarea><br>
				<div style='display:none;'>
					<b>$lang_adm->s_country:</b><br>
					<textarea name='country' class='' style='height:25px; width:100%;'>$country</textarea><br>
					<b>$lang_adm->s_city:</b><br>
					<textarea name='city' class='' style='height:25px; width:100%;'>$city</textarea><br>
				</div>
				<b>$lang_adm->s_address:</b><br>
				<textarea name='address' class='' style='height:25px; width:100%;'>$address</textarea><br>
				<b>$lang_adm->s_coords:</b><br>
				<textarea name='lat_lng' class='' style='height:25px; width:100%;'>$lat_lng</textarea><br>
				<b>$lang_adm->s_phones:</b><br>
				<textarea name='phones' class='' style='height:25px; width:100%;'>$phones</textarea><br>
				<b>$lang_adm->s_mailbox:</b><br>
				<textarea name='mailbox' class='' style='height:25px; width:100%;'>$mailbox</textarea><br>
				<b>$lang_adm->s_time:</b><br>	
				<textarea name='worktime' class='' style='height:50px; width:100%;'>$worktime</textarea><br>
				<b>$lang_adm->s_more_info:</b><br>	
				<textarea name='moreinfo' class='' style='height:50px; width:100%;'>$moreinfo</textarea><br>
				<input type='checkbox' name='firm' value='yes' $checkedfirm/> $lang_adm->s_firm_salon <br>
				<input type='checkbox' name='monobrend' value='yes' $checkedmonobrend/> $lang_adm->s_mono_salon <br>
				<input type='checkbox' name='rassr' value='yes' $checkedrassr/> $lang_adm->s_rassrochka <br>
			</div>
			<div class='mysalondiv_column column3'>
				<b>$lang_adm->s_vk:</b><br>
				<input name='vk' type='url' class='' style='height:25px; width:100%;' placeholder='https://vk.com' value='$vk'><br>
				<b>$lang_adm->s_fb:</b><br>
				<input name='fb' type='url' class='' style='height:25px; width:100%;' placeholder='https://facebook.com' value='$fb'><br>
				<b>$lang_adm->s_insta:</b><br>
				<input name='insta' type='url' class='' style='height:25px; width:100%;' placeholder='https://instagram.com' value='$insta'><br>
				<b>$lang_adm->s_site:</b><br>
				<input name='site' type='url' class='' style='height:25px; width:100%;' placeholder='https://site.com' value='$site'><br><br>
				
				<b>$lang_adm->s_samples:</b> <br><br>
				$c
			</div>
			<div style='margin-top:64px;'>
				<button type='submit' name='yes' value='$newid'  class='applybtn' style='float:left; margin:0 32px 10px 10px; border:none; color:white; background:#083c54; padding:16px 32px; cursor:pointer;'>$lang_adm->ms_make_edits</button>
				<button type='submit' name='no' value='$newid'  class='negative_btn' style='float:left; margin:0 0 10px 0;'>$lang_adm->ms_delete</button>
			</div>
		</div>
		</form>
		";
	}

	$a.="</div>";
	
	$a.="
	<style>
	.dorab_info{position:absolute; right:150px; top:0px; min-width:100px; min-height:20px; width:max-content; height:max-content; max-width:600px; max-height:60px; overflow-y:auto; background:red; padding:8px 16px;}
	</style>
	
	<script>	
	//РАЗМЕР КАРТИНКИ
	var myimagewidth = jQuery('.myimagediv').width();
	var myimageheight = myimagewidth / 1.5;
	jQuery('.myimagediv').css('height', myimageheight);
	
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
          span.innerHTML = [\"<img style='max-height:210px; max-width:100%; position:relative; top:0px; z-index:999;' class='img-thumbnail' src='\", e.target.result,
                            \"' title='\", escape(theFile.name), \"'/>\"].join('');
          document.getElementById('outputMulti').insertBefore(span, null);
          document.getElementById('nowAvatar').style.height ='210px';
        };
      })(f);
      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
	  jQuery('.myimagediv').css('background', 'white');
    }
  }
  document.getElementById('newAvatar').addEventListener('change', handleFileSelectMulti, false);
  console.log('На модерацию поступил салон! Проверьте админку сайта gi.by');
	</script>";
	

	$a .=  "
	<script type='text/javascript' src='/wp-content/plugins/bp_add_salon/js/scripts.js'></script>
	<script>
		// alert('Правки внесены');
		let salonForm = document.querySelector('.mysalondiv');
		let applyBtn = document.querySelector('.applybtn');
		applyBtn.addEventListener('click', function () {
			let message = 'На модерацию поступил салон! Проверьте админку сайта gi.by';
			let subject = 'Модерация салонов';
			if (message && subject) {
				sendAddSalon(message, subject);
				alert('Правки внесены');
			}
		})
	</script>";

	echo $a;
		?>
	
	<?php	
	//$array_trimmed = array('\n', '\r', '<br>', ' ');
	if(isset($_POST['yes'])){
		$moderate_id = $_POST['yes'];
		$name = $_POST['thisname'];
		$name_eng = str_replace($rus, $lat, $name);
		$country = $_POST['country'];
		$city = $_POST['city'];
		$address = $_POST['address'];
		$lat_lng = $_POST['lat_lng'];
		//$lat_lng = str_replace($array_trimmed, '', $lat_lng);
		$lat_lng = str_replace(', ', '_', trim($lat_lng));
		$phones = $_POST['phones'];
		$mailbox = $_POST['mailbox'];
		$worktime = $_POST['worktime'];
		$worktime = str_replace("\r\n", '<br>', $worktime);
		$worktime = str_replace("\r\n", '<br>', $worktime);
		$phones = str_replace('&#13;&#10;', '<br>', $phones);
		$phones = str_replace('&#13;&#10;', '<br>', $phones);
		$moreinfo = $_POST['moreinfo'];
		$fb = $_POST['fb'];
		$vk = $_POST['vk'];
		$insta = $_POST['insta'];
		$site = $_POST['site'];
		if($_POST['firm']){$firm = 'yes';} else {$firm = 'no';}
		if($_POST['monobrend']){$monobrend = 'yes';} else {$monobrend = 'no';}
		if($_POST['rassr']){$rassr = 'yes';} else {$rassr = 'no';}
		$newSamples = implode(", ", $_POST['newSamples']);
		$thumblink = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/avatars_of_salons/thumbs/";
		$avatarlink = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/avatars_of_salons/images/";
		
		//SQL снова получить аватар
		$sqlAvatarAgain = "SELECT avatar FROM gi_salons WHERE newid = '$moderate_id'";
		$resultAvatarAgain = $wpdb->get_results($sqlAvatarAgain);
		foreach ($resultAvatarAgain as $rowAvatarAgain){
			$avatar = $rowAvatarAgain->avatar;
		}
		
		$oldavatar = $avatarlink . $avatar;
		$oldthumb = $thumblink . $avatar;
		
		//ГРУЗИМ НОВЫЙ АВАТАР
		//Если при всем при этом еще и картинка загружена, то...
		if(!empty($_FILES['newAvatar']) and $_FILES['newAvatar']['name']==true){

		if(is_uploaded_file($_FILES["newAvatar"]["tmp_name"])){					
			$tmp_avatar = $_FILES['newAvatar']['tmp_name'];
			$path_parts_avatar  = pathinfo($_FILES['newAvatar']['name']);
			$extension_avatar = $path_parts_avatar['extension'];
			$newAvatar = $lat_lng . "." . $extension_avatar;
			$avatarlink = $avatarlink . $newAvatar;			
			$thumblink = $thumblink . $newAvatar;			
			
		
			if ($extension_avatar == "png" or $extension_avatar=="jpg" or $extension_avatar=="jpeg" or $extension_avatar=="JPG" or $extension_avatar=="PNG"){
				//Удаляем старые
				unlink($oldavatar);
				unlink($oldthumb);
				
				//задаем размер аватару
				list($width, $height) = getimagesize($tmp_avatar);
				$otnosh = $width / $height;
				
				//размеры основной картинки
				if ($width >= 1024) {$newWidth = 1024;}
				else {$newWidth = $width;}
				$newHeight = $newWidth / $otnosh;
				$newHeight = round($newHeight);
				//размеры миниатюры
				$thunmbWidth = 400;
				$thunmbHeight = $thunmbWidth / $otnosh;
				$thunmbHeight = round($thunmbHeight);
					
				$image_p = imagecreatetruecolor($newWidth, $newHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ
				$image_th = imagecreatetruecolor($thunmbWidth, $thunmbHeight); //СОЗДАТЬ ПУСТОЕ ИЗОБРАЖЕНИЕ ДЛЯ МИНИАТЮРЫ
				switch($extension_avatar){
					case "jpg":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "JPG":	//СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "jpeg": //СОЗДАНИЕ JPG
						$image = imagecreatefromjpeg($tmp_avatar); //СОЗДАЕМ ОБЪЕКТ ПО ССЫЛКЕ НА КАРТИНКУ
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); //КОПИРУЕМ КАРТИНКУ С ИЗМЕНЕНИЕМ РАЗМЕРОВ
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height); //Делаем миниатюру
						imagejpeg($image_p, $avatarlink, 70);
						imagejpeg($image_th, $thumblink, 70);
						break;
					case "png":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
					case "PNG":	//СОЗДАНИЕ ПНГ
						$image = imagecreatefrompng($tmp_avatar);
						//fot gallery
						imagealphablending($image_p, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_p, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($image_p, $avatarlink);
						//for thumb
						imagealphablending($image_th, false); //Отключаем режим сопряжения цветов
						imagesavealpha($image_th, true); //Включаем сохранение альфа канала
						imagecopyresampled($image_th, $image, 0, 0, 0, 0, $thunmbWidth, $thunmbHeight, $width, $height);
						imagepng($image_th, $thumblink);
						break;
					}
				
				
				}
			}
		$newAvatarSql = " avatar='$newAvatar',";
		}
		else $newAvatarSql = "";	

		if($current_user == 'admin'){
			$moder = "yes";
		}
		else $moder = "no";			
		$sql =  "UPDATE gi_salons SET 
		name = '$name', name_eng = '$name_eng', country='$country', city='$city', address='$address', lat_lng='$lat_lng', 
		phones='$phones', mailbox='$mailbox', worktime='$worktime', samples='$newSamples', $newAvatarSql firm='$firm',
		monobrend='$monobrend', rassrochka='$rassr', moreinfo='$moreinfo', vk='$vk', fb='$fb', insta='$insta', site='$site', moderate='$moder'
		WHERE `newid` = '$moderate_id'";
		$result = $wpdb -> get_results($sql);
		// echo "<h2>Салон отредактирован</h2>
		// Страница сейчас перезагрузится.";
		
		// $message = "На модерацию поступил салон! Проверьте админку сайта gi.by";
		// $subject = "Модерация салонов";
		// mail('info@gi.by', $subject, $message);
		
		// echo "<script>alert('Правки внесены');</script>";
	
	}
	if (isset($_POST['no'])){
		$deleteid = $_POST['no'];
		//SQL снова получить аватар
		$sql = "SELECT avatar FROM gi_salons WHERE newid='$deleteid'";
		$result = $wpdb -> get_results($sql);
		foreach ($result as $row){
			$avatar = $row->avatar;
			$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/$avatar";
			$avatarlink = "../wp-content/uploads/avatars_of_salons/images/$avatar";
			unlink ($thumblink);
			unlink ($avatarlink);
		}
		$sqldelete = "DELETE FROM gi_salons WHERE newid='$deleteid'";
		$resultdelete = $wpdb->get_results($sqldelete);
		$sqldelete2 = "DELETE FROM gi_samples WHERE salon_id = '$deleteid'";
		$resultdelete2 = $wpdb->get_results($sqldelete2);
		echo "<script>alert('Салон удален');</script>";
	}	
}
?>