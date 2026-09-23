<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
$mycity = $_POST['city'];



	$a ="<div style='width:100%; height:max-content; text-align:center; margin-top:20px;'>";
	
	$sql = "SELECT * FROM gi_salons WHERE city = '$mycity' ORDER BY `rank` DESC";
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
		$moderate = $row -> moderate;
		$user = $row -> user;
		$rank = $row -> rank;
		$dorab_reason = $row -> dorab_reason;
		$oprosnik = $row -> oprosnik;
		$thumblink = "../wp-content/uploads/avatars_of_salons/thumbs/" . $avatar;
		$avatarlink = "../wp-content/uploads/avatars_of_salons/images/" . $avatar;
		list($lat,$lng)= explode  ("_", $lat_lng);
		
		switch ($moderate){
			case "yes" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:10px; right:10px; background:green; color:white; border-radius:5px;'>Опубликовано</div>";  break;
			case "no" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:10px; right:10px; background:#501db5; color:white; border-radius:5px;'>Ожидает модерации</div>";  break;
			case "dorab" : $moderate_div = "			<div style='position:absolute; top:10px; right:10px;'><div style='background:red; color:white; display:inline-block; padding:5px 10px; margin-right:10px;'>$dorab_reason</div><div style='display:inline-block; background:#fcba03; color:white; border-radius:5px; padding:10px 20px;'>На доработке</div></div>";  break;
			case "banned" : $moderate_div = "			<div style='padding:10px 20px; position:absolute; top:10px; right:10px; background:darkred; color:white; border-radius:5px;'>Салон отключен</div>";  break;
		}
			
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
		$resultObraz[] = (object)['name' => "Гардероб"];
		$rand = rand(1,100); //добавляем гет запросом в картинку (аватар), чтоб не кешировалась. Шах и мат, кеш!
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
		<div style='width:calc(98% - 20px); position:relative; padding:20px 10px; text-align:left; min-width:200px; min-height:200px; background:white; border:1px solid #dcdcdc; display:inline-block; margin:10px 1%;'>
			<br><h2>ID номер салона: №$newid. Пользователь: $user. <br><br>
			Ссылка на опросник: <textarea name='oprosnik' style='height:25px; width:300px;'>$oprosnik</textarea><br><br>
			Рейтинг: <input name='rank' value='$rank' style='width:30px;'/></h2>
			<div style='width:31%; float:left; margin:0 1%;'>
				$moderate_div
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
				<input name='vk' type='url' class='' style='height:25px; width:100%;' placeholder='https://vk.com' value='$vk'><br>
				Ссылка на фейсбук:<br>
				<input name='fb' type='url' class='' style='height:25px; width:100%;' placeholder='https://facebook.com' value='$fb'><br>
				Ссылка в инстаграм:<br>
				<input name='insta' type='url' class='' style='height:25px; width:100%;' placeholder='https://instagram.com' value='$insta'><br>
				Ссылка на сайт:<br>
				<input name='site' type='url' class='' placeholder='https://site.com' style='height:25px; width:100%;' value='$site'><br><br>
				
				<input type='checkbox' name='firm' value='yes' $checkedfirm/> Фирменный салон <br>
				<input type='checkbox' name='monobrend' value='yes' $checkedmonobrend/> Монобрендовый салон <br>
				<input type='checkbox' name='rassr' value='yes' $checkedrassr/> Рассрочка <br><br>
				Образцы в наличии: <br>
				$c
			</div>
			<div style='width:31%; float:left; margin:0 1%; height:450px;'>
				<div id='map' class='map' style='width:100%; height:230px;'>
					<iframe width='100%' height='100%' src='https://maps.google.com/maps?q=$lat,$lng&hl=ru&z=10&amp;output=embed'></iframe>
				</div>
				<br><br>
				<a target='_blank' href='https://gi.by/kd/files/%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%20%D0%BA%20%D1%84%D0%BE%D1%82%D0%BE%D0%B3%D1%80%D0%B0%D1%84%D0%B8%D1%8F%D0%BC%20%D0%BA%D0%B0%D0%B1%D0%B8%D0%BD%D0%B5%D1%82%20%D0%B4%D0%B8%D0%BB%D0%B5%D1%80%D0%B0.pdf'> Следуйте требованиям к фотографиям!</a>
				<br>Заменить изображение: <input type='file' id='newAvatar' name='newAvatar'/> 

				<a href='$avatarlink' target='_blank'>
					<div style='width:100%; height:210px; margin-top:10px; background:url($thumblink?$rand) no-repeat; background-size:cover; background-position:center;'></div>
				</a>
				<div id='nowAvatar' style='width:100%; height:0px; position:relative; background:white; z-index:999; bottom:210px;'><span id='outputMulti'></span>
				</div>
				<br>
			</div>
			
				<div style='clear:both; margin-bottom:20px;'></div>
				
				<div id='dorab_window_$newid' style='width:400px; max-width:100%; margin:0px 16px 32px 16px; padding:16px; position:relative; clear:both; box-shadow:3px 6px 12px rgba(1,1,1,0.2); display:none;'>
					Причина доработки:<br>
					<textarea name='dorab_reason' style='width:100%; height:100px;'></textarea>
					<button type='submit' name='dorab' value='$newid'  style='margin:32px 0 8px 0; border:none; color:white; background:#fcba03; padding:10px 20px; cursor:pointer;'>Отправить</button>			
				</div>
				
				
				<button type='submit' name='yes' value='$newid'  style='float:left; margin:0 10px 10px 10px; border:none; color:white; background:#2fb54c; padding:10px 20px; cursor:pointer;'>Опубликовать</button>
				<div id='dorab' onclick='opendorabwindow($newid);' style='float:left; margin:0 10px 10px 10px; border:none; color:white; background:#fcba03; padding:10px 20px; cursor:pointer;'>На доработку</div>
				<button type='submit' name='block' value='$newid'  style='float:left; margin:0 0 10px 0; margin-left:32px; border:none; color:white; background:#78591a; padding:10px 20px; cursor:pointer;'>Заблокировать</button>
				<button type='submit' name='no' value='$newid'  class='banbtn' style='float:left; margin:0 0 10px 0; margin-left:16px; border:none; color:white; background:#b5572f; padding:10px 20px; cursor:pointer;'>Удалить салон</button>

		</div>
		</form>
		";
	}

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
	</script>
	";
	echo $a;

?>
