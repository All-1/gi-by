<?php
	$rus=array('"','(',')',' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	$lat=array('','','','_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');

	global $wpdb;
	$a.="<div style='width:calc(100% - 75px); margin-top:25px; min-height:100px; background:white; position:relative; padding:25px;'>";


		$sqlsamples = "SELECT * FROM gi_samples WHERE moderate = 'no' OR moderate = 'dorab'";	
		$resultsamples = $wpdb->get_results($sqlsamples);
		foreach($resultsamples as $rowsamples){
			$newid = $rowsamples->newid;
			$salon_id = $rowsamples->salon_id;
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
			$avatar = $rowsamples->avatar;
			$images_arr = explode (';', $images);
			$delete_icon = get_template_directory_uri() . '/images/delete.png';
			
			$c="";
			foreach($images_arr as $number => $imagename){
				if($imagename !=''){
					$c.="
					<div style='text-align:center; width:max-content; margin:10px 0; display:inline-block; position:relative;'>
						<img src='../$folder/images/$imagename' style='max-height:200px;'><br>
						<input type='submit' value='$newid/$imagename' name='delete_image' style='color:transparent; cursor:pointer; width:30px; height:30px; margin:auto; position:relative;  
						background-image:url($delete_icon); background-repeat: no-repeat; background-size:contain; background-position:center; border:none;'/>
					</div>
					";

				}
			}
			$sqlsalons = "SELECT newid, name, country, city, address FROM gi_salons WHERE newid = '$salon_id'";
			$resultsalons = $wpdb->get_results($sqlsalons);
			foreach($resultsalons as $rowsalons){
				$salon_id = $rowsalons -> newid;
				$salon_name = $rowsalons -> name;
				$salon_country = $rowsalons -> country;
				$salon_city = $rowsalons -> city;
				$salon_address = $rowsalons -> address;
				$b="
				<div class='header_samples' style='margin:10px 0;'>
					<h1>$salon_name</h1>
					<span><i>$salon_country, $salon_city, $salon_address</i></span>
					<hr style='margin-bottom:20px;'>
				</div>
				";
			}
			$a.="
			<div style='width:calc(100% - 40px); padding:20px; min-height:100px; border:1px solid #dcdcdc; box-shadow: 2px 2px 2px rgba(1,1,1,0.2); margin:20px 0;'>
				$b
				<form method='post' enctype='multipart/form-data'>
					<div style='float:left;'>
						<div style='width:320px; height:260px; background:url(../$folder/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
						<br>Новая обложка:<br>
						<input type='file' name='newAva'/>
					</div>
					<div style='float:left; width:calc(100% - 340px); margin-left:20px;'>
						<b>Кухня</b><br>
						<textarea name='kitchen' style='width:100%; height:30px;' required>$kitchen</textarea><br>
						<b>Описание</b><br>
						<textarea name='descr' style='width:100%; height:30px;' required>$descr</textarea><br>
						<b>Размеры</b><br>
						<textarea name='size' style='width:100%; height:30px;' required>$size</textarea><br>
						<b>Стоимость образца</b><br>
						<!------<input name='cost' type='number' style='width:100%; height:30px;' value='$cost' required><br>------>
						<textarea name='cost' type='' style='width:100%; height:30px;' required>$cost</textarea><br>
						<b>Процент скидки:</b><br>
						<textarea name='sales' style='width:100%; height:30px;'>$sales</textarea><br>
					</div>
					<div style='clear:both;'></div>
					<div style='width:100%; text-align:left; margin:10px 0;'>
						$c
					</div>
					<b>Добавить изображения:</b><br>
					<input type='file' name='images[]' id='images' multiple><br><br>
					<textarea name='dorab_reason' placeholder='Причина отправки на доработку (может быть пустым)' style='width:600px; height:100px;'></textarea><br><br>
					<button name='moderate' value='$newid' type='submit' style='background:green; border:none; padding:10px 25px; color:white; cursor:pointer;'>Внести правки</button>
					<button name='dorab_sample' value='$newid' type='submit' style='background:orange; border:none; padding:10px 25px; margin-left:10px; color:white; cursor:pointer;'>На доработку</button>
					<button name='delete_sample' value='$newid' type='submit' style='background:darkred; border:none; padding:10px 25px; margin-left:10px; color:white; cursor:pointer;'>Убрать с продажи</button>
				</form>
				
			</div>
			";

		}
		$c="";
	
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
		$sqlsample2 = "SELECT images, thumbs, folder, salon_id, avatar FROM gi_samples WHERE newid='$moderate_id'";
		$resultsample2 = $wpdb->get_results($sqlsample2);
		foreach($resultsample2 as $rowsample2){
			$images = $rowsample2->images;
			$thumbs = $rowsample2->thumbs;
			$folder = $rowsample2->folder;
			$salon_id = $rowsample2->salon_id;
			$avatar = $rowsample2->avatar;
		}
		
		
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
					
					$message = "На модерацию поступил образец. Проверьте админку сайта gi.by";
					$subject = "Модерация образцов";
					mail('info@gi.by', $subject, $message);
				}	
			}
		}		
		//ДОБАВЛЯЕМ КАРТИНКИ
		$images_dir = "../$folder";
		$imagesname_data = date("dmY");
		

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
				$image_name = $salon_id . "_" . $imagesname_data . "_" . str_replace($_FILES['images']['name'][$i]);
				$thumb_name = $salon_id . "_" . $imagesname_data . "_" . str_replace($_FILES['images']['name'][$i]);
				$images_db .="$image_name;";
				$thumbs_db .="$thumb_name;";
				
			}
		}
		$images_result = $images . $images_db;
		$thumbs_result = $thumbs . $thumbs_db;
		//echo $salon_id;
		$sqlredact = "UPDATE gi_samples SET `dorab_reason`='',`images`='$images_result', `thumbs`='$thumbs_result', name='$kitchen', `description` = '$descr', `size` = '$size', `cost`='$cost', `sales`='$sales', `moderate` = 'yes' WHERE newid='$moderate_id'";
		$resultredact = $wpdb->get_results($sqlredact);
		echo "<script>window.location.reload();</script>";

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
		echo "<script>window.location.reload();</script>";
		
	}
	
	if(isset($_POST['dorab_sample']))	{
		$dorabid = $_POST['dorab_sample'];
		$dorab_reason = $_POST['dorab_reason'];
		$sqldorab = "UPDATE gi_samples SET moderate='dorab', dorab_reason='$dorab_reason' WHERE newid='$dorabid'";
		$resultdorab = $wpdb -> get_results($sqldorab);
		echo "<script>window.location.reload();</script>";
		
	}	

?>