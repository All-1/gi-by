<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$mycity = $_POST['city'];
$mywhere = "WHERE city='$mycity'";


	$sqlsalons = "SELECT newid, name, country, city, address FROM gi_salons $mywhere";
	$resultsalons = $wpdb->get_results($sqlsalons);
	foreach($resultsalons as $rowsalons){
		$salon_id = $rowsalons -> newid;
		
		$salon_name = $rowsalons -> name;
		$salon_country = $rowsalons -> country;
		$salon_city = $rowsalons -> city;
		$salon_address = $rowsalons -> address;
		$sqlsamples = "SELECT * FROM gi_samples WHERE salon_id = '$salon_id' AND moderate = 'yes'";
		$resultsamples = $wpdb->get_results($sqlsamples);
		
		if($resultsamples){
			
			$b="
			<div class='header_samples' style='margin:20px 0;'>
				<h1>$salon_name</h1>
				<span><i>$salon_country, $salon_address</i></span>
				<hr>
			</div>
			";
		
			foreach($resultsamples as $rowsamples){
				$newid = $rowsamples->newid;
				$salonid = $rowsamples->salon_id;				
				$kitchen = $rowsamples->name;
				$address = $rowsamples->salon_address;
				$descr = $rowsamples->description;
				$size = $rowsamples->size;
				$cost = $rowsamples->cost;
				$sales = $rowsamples->sales;
				$avatar = $rowsamples->avatar;
				$images = $rowsamples->images;
				$thumbs = $rowsamples->thumbs;
				$folder = $rowsamples->folder;
				$moderate = $rowsamples->moderate;
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
							<textarea name='cost' style='width:100%; height:30px;' required>$cost</textarea><br>
							<b>Процент скидки:</b><br>
							<textarea name='sales' style='width:100%; height:30px;'>$sales</textarea><br>
						</div>
						<div style='clear:both;'></div>
						<div style='width:100%; text-align:left; margin:10px 0;'>
							$c
						</div>

						<b>Добавить изображения:</b><br>
						<input type='file' name='images[]' id='images' multiple><br><br>
						<button name='moderate' value='$newid' type='submit' style='background:green; border:none; padding:10px 25px; color:white; cursor:pointer;'>Внести правки</button>
						<button name='delete_sample' value='$newid' type='submit' style='background:darkred; border:none; padding:10px 25px; margin-left:10px; color:white; cursor:pointer;'>Убрать с продажи</button>
					</form>

				</div>
				";

			}			
		}

	}
	
	$a.="</div>";
	echo $a;

?>