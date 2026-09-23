<?php
/*Plugin Name: bp_materials
Description: Материалы кухонь.
Version: 1.0
Author: Business Park*/

add_action('admin_menu', 'add_materials_page');
function add_materials_page() {
    add_menu_page('Материал', 'Материал кухонь', 8, __FILE__, 'my_materials', 'dashicons-tagcloud');
		add_submenu_page(__FILE__, 'Фурнитура', 'Фурнитура', 8, 'add_acsessorie', 'add_acsessorie');
}
add_shortcode('print_acsessories_buttons', 'print_acsessories_buttons');


function add_acsessorie(){
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');

	global $wpdb;
	
	$a ="<h1>Фурнитура в наличии</h1>";
	$sql = "SELECT * FROM gi_acsessories";
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$type = $row->type;
		$name = $row->name;
		$triger = $row->triger;
		$content_link = $row->content_link;
		$image_link = $row->image_link;
		$newid = $row->newid;
		$a.="
		<form method='POST'>
			<div>
				<input value='$type' name='newtype'>
				<input value='$name' name='newname'/>
				<input value='$triger' name='triger'/>
				<input value='$content_link' name='newcontentlink'/>
				<input value='$image_link' name='newimagelink'/>
				<button type='submit' name='redact' value='$newid'>Редактировать</button>
				<button type='submit' name='delete' value='$newid'>Удалить</button>
			</div>
		</form>";
	}
	$a.="<h1>Добавить фурнитуру</h1>";
	$a.="<form method='POST'>
		<select name='furn_type' required>
			<option selected disabled value=''>Выберите тип фурнитуры </option>
			<option value='Петли'>Петли</option>
			<option value='Системы выдвижения'>Системы выдвижения</option>
			<option value='Подъемные механизмы'>Подъемные механизмы</option>
			<option value='Подсветка'>Подсветка</option>
			<option value='Наполнение'>Наполнение</option>
			<option value='Открывание'>Открывание</option>
		</select><br>
		Название фурнитуры<br>
		<input name='furn_name' required/><br>
		Ссылка на запись <br>
		<input name='furn_content' required/><br>
		Ссылка на картинку <br>
		<input name='furn_image' required/><br><br>
		
		<input type='submit' value='Добавить'/>
	</form>";
	
	if(isset($_POST['furn_type']) and isset($_POST['furn_name']) and isset($_POST['furn_content']) and isset($_POST['furn_image'])){
		$furn_type = $_POST['furn_type'];
		switch($furn_type){
			case 'Петли': $triger = 'petli'; break;
			case 'Системы выдвижения': $triger = 'vidvijnie'; break;
			case 'Подъемные механизмы': $triger = 'podjemnie'; break;
			case 'Подсветка': $triger = 'podsvetka'; break;
			case 'Наполнение': $triger = 'napolnenie'; break;
			case 'Открывание': $triger = 'otkrivanie'; break;
		}
		$furn_name = $_POST['furn_name'];
		$furn_content = $_POST['furn_content'];
		$furn_image = $_POST['furn_image'];
		$acs_sql = "INSERT INTO `gi_acsessories` (`type`, `triger`, `name`, `content_link`, `image_link`) VALUES ('$furn_type', '$triger', '$furn_name', '$furn_content', '$furn_image');";
		$result_acs = $wpdb->get_results($acs_sql);
		echo "Добавлено!";
	}
	if (isset($_POST['redact']) and !empty($_POST['redact'])){
		$id = $_POST['redact'];
		$newtype = $_POST['newtype'];
		$newname = $_POST['newname'];
		$newcontentlink = $_POST['newcontentlink'];
		$newimagelink = $_POST['newimagelink'];
		$sqlredact = "UPDATE gi_acsessories SET name = '$newname', type = '$newtype', content_link = '$newcontentlink', image_link = '$newimagelink' WHERE newid='$id'";
		$resultredact = $wpdb->get_results($sqlredact);
		echo "Удалить";
	}
	if (isset($_POST['delete']) and !empty($_POST['delete'])){
		$id = $_POST['delete'];
		$sqldelete = "DELETE FROM gi_acsessories WHERE newid = '$id'";
		$resultdelete = $wpdb->get_results($sqldelete);
		echo "Удалить";
	}
	
	echo $a;
}

function my_materials (){
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	
	global $wpdb;
	echo "<h1>Материал кухонь</h1>";
	$sql = "SELECT * FROM gi_kitchen_material";
	$result = $wpdb->get_results($sql);

	$a ="
	<form method='POST'>
	
	Добавить материал: <br>
	<input name='addMaterial'/>
	<input type='submit' name='add' value='Добавить'/>
	<br><br>
	
	<div style='width:50px; background:#dcdcdc; float:left; margin:2px; padding-left:5px;'>
		ID
	</div>
	<div style='width:250px; background: #dcdcdc; float:left; margin:2px; padding-left:5px;'>
		Название материала
	</div>
	<div style='width:100px; background: #dcdcdc; float:left; margin:2px; padding-left:5px; text-align:center;'> Удаление </div>

	<div style='clear:both'></div>
	";
	foreach ($result as $row){
		$material = $row->material;
		$newid = $row->newid;
		$a.="
		<div style='width:50px; background:white; float:left; margin:2px; padding-left:5px;'> $newid </div>
		<div style='width:250px; background: white; float:left; margin:2px; padding-left:5px;'> $material </div>
		<div style='width:100px; background: white; float:left; margin:2px; padding-left:5px; text-align:center;'>
			<input type='checkbox' name='$newid'/>
		</div>
		<div style='clear:both;'></div>
		";
		if (isset($_POST[$newid]) and isset($_POST['delete'])){
			$sqlDelete = "DELETE FROM gi_kitchen_material WHERE newid='$newid'";
			$resultDelete = $wpdb->get_results($sqlDelete);
			echo "Материалы удалены";
			echo "<script>window.location.reload();</script>";
		}
	}
	if (isset($_POST['addMaterial']) and isset($_POST['add'])){
		$material_ru = $_POST['addMaterial'];
		$material_eng = str_replace($rus, $lat, $material_ru);
		$sqlAdd = "INSERT INTO gi_kitchen_material (`material`, `material_eng`) VALUES ('$material_ru', '$material_eng');";
		$resultAdd = $wpdb->get_results($sqlAdd);
		echo "Материал добавлен";
		echo "<script>window.location.reload();</script>";
	}
	
	$a.="<br>
	<input type='submit' name='delete' value='Удалить'/>
	</form>
	";
	echo $a;
}

function print_acsessories_buttons (){
	global $wpdb;
	$sql = "SELECT DISTINCT type, triger FROM gi_acsessories";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$type = $row->type;
		$triger = $row->triger;
		$a.="<button class='myBtn acsesBtn' data-id='$triger' onclick='get_acs_content(this);'>$type</button>";
		
	}
	$a ="<div id='ajaxcontent' class='result' style='margin-top:40px;'></div>";
	$c ="
	<script>
		function get_acs_content(obj){
			var mytype = jQuery(obj).attr('data-id');
			alert(mytype);
			jQuery('.acsesBtn').removeClass('selectedBtn');
			jQuery(obj).addClass('selectedBtn');
			//jQuery('.' + mytype).addClass('showContent');
			
			
			jQuery.ajax({
				url: '/wp-content/plugins/bp_materials/ajax_acses.php',
				type: 'POST',
				data: {type:mytype},
				cache: false,
				success: function(html){  
					jQuery('#ajaxcontent').html(html);  
				} 
			});	
		}
	</script>
	<style>
		.selectedBtn{background:#a1a1a1; color:white;}
		.hidden_content{display:none;}
		.showContent {display:none;}
	</style>
	";
	
	return $a . $c;
}