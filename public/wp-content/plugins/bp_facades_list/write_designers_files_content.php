<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$name = $_POST['name'];
$datatype = $_POST['type'];

function write_kitchen_content($kitchen){	
	
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	$alias = str_replace($rus, $lat, $kitchen);

	$b = "<div style='margin-bottom:64px; cursor:pointer;' onclick='closemodalwindow();'>
			<img src='/wp-content/plugins/bp_dealer_files/images/left.png' style='height:20px; margin-right:8px;'/> <span style='line-height:20px;'>В начало</span>
		</div>";
	$b .="<h1 style='margin-bottom:8px !important;'>Файлы для кухни $kitchen</h1> <a href='/kuhni/$alias/' target='_blank' style='margin-bottom:64px; display:block; color:#DEA993; text-decoration:underline;'>Смотреть модель $kitchen в каталоге</a>";
	
	$b.="<div class='dis_obsch_files'>";
	global $datatype;
	global $wpdb;
	
	$sql = "SELECT * FROM gi_designer_architect WHERE kitchen LIKE '%$kitchen%' ORDER BY type";
	$result = $wpdb->get_results($sql);
	//$all = array();

	foreach($result as $row){
		
		$newid = $row->newid;
		$type = $row->type;
		$setfile = $row->setfile;
		$descr = $row->descr;
		$filelink = "/wp-content/uploads/designers_files/data_dir/$setfile";
		if(!empty($descr)){
			$descrtext = "<span style='font-size:12px;'>$descr</span><br>";
		}
		else {$descrtext = "";}
		$ext = "<b>" . mb_strtoupper(end(explode(".", $setfile))) . "</b>";
		$file = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/data_dir/$setfile";
		$filesize = round(filesize($file) / 1024) . " Kb";
		
		
		if($type == '3d_model'){
			$b.="
			<a href='$filelink' target='_blank' download>
				<div style='display:inline-grid; grid-template-columns:0fr 1fr; grid-gap:32px; text-align:left; align-items: center; height:100px; line-height:1.3; cursor:pointer;'>
					<div style='width:70px; height:70px; background:url(/wp-content/plugins/bp_dealer_files/images/3d2.png) no-repeat; background-size:contain; background-position:center;'></div>
					<div>
						<span style='font-size:22px;'>Скачать 3D модели</span><br>
						$descrtext
						<span style='font-size:12px; color:#DEA993;'>$ext ($filesize)</span><br>
					</div>
				</div>
			</a>
			";
		}
		elseif($type == 'techn_descr'){
			$b.="
			<a href='$filelink' target='_blank'>
				<div style='display:inline-grid; grid-template-columns:0fr 1fr; grid-gap:32px; text-align:left; align-items: center; height:100px; line-height:1.3; cursor:pointer;'>
					<div style='width:70px; height:70px; background:url(/wp-content/plugins/bp_dealer_files/images/load.png) no-repeat; background-size:contain; background-position:center;'></div>
					<div>
						<span style='font-size:22px;'>Техническое описание</span><br>
						<span style='font-size:12px; color:#DEA993;'>$ext ($filesize)</span><br>
					</div>
				</div>
			</a>
			";
		}
		else {
			$b.="Пусто...";
		}
		
	}
	$b.="</div>";
	
	
	$texture_btns = "<div style='margin:32px 0 0 0;'><br>";
	//Достаем все текстуры шо есть
	$sql_texture = "SELECT DISTINCT type FROM gi_designer_textures WHERE kitchens LIKE '%$kitchen%' ORDER BY FIELD (type, 'Массив, шпон', 'МДФ', 'Skin', 'Syncron', 'Egger', 'Постформинг', 'Столешница')";
	$result_texture = $wpdb->get_results($sql_texture);
	if($result_texture) $texture_btns .="<h3 style='font-size:30px;'>Текстуры к кухне</h3>";
	foreach ($result_texture as $row_texture){
		$type = $row_texture->type;
		if($type == 'Постформинг') $typetext = 'Столешницы';
		else $typetext = $type;
		
		$texture_btns .="<div class='textures_btn' data-type='$type'>$typetext</div>";
		
		
	}
	$texture_btns .="</div>";
	
	$textures_content = "
	<div class='texture_btns'>
		$texture_btns
	</div>
	<div id='textures_content' class='textures_grid'>
		<img src='/wp-content/themes/wp-diary/images/loading.gif' style='width:130px; margin:auto; position:absolute; left:calc(50% - 65px); top:-40px;'/>
	</div>
	";
	

	$b.="
	<script>
	function draw_textures_content(type, kitchen){
		jQuery.ajax({
			url: '/wp-content/plugins/bp_dealer_files/write_textures_content.php',
			type: 'POST',
			data: {type:type, kitchen:kitchen},
			success: function(data){ 
				jQuery('#textures_content').html(data);
				//alert('Изменения внесены');
			} 
		});
	}
	
	
	//отрисовываем первые текстуры
	var firsttexturesdiv = jQuery('.textures_btn');
	var firstdata = jQuery(firsttexturesdiv[0]).data('type');
	draw_textures_content(firstdata, '$kitchen');
	
	jQuery(firsttexturesdiv[0]).addClass('selected');
	jQuery('.textures_btn').click(function(){
		jQuery('.textures_btn').removeClass('selected');
		jQuery(this).addClass('selected');
		var datatype = jQuery(this).data('type');
		//console.log(datatype);
		draw_textures_content(datatype, '$kitchen');
	});
	</script>
	";
	$b.= $textures_content;
	
		// foreach ($all as $type => $val){
		
		// switch ($type){
			// case "3d_model": $rustype = "3D Модели"; break;
			// case "texture": $rustype = "Текстуры"; break;
			// case "techn_descr": $rustype = "Техническое описание"; break;
			// case "handle": $rustype = "Ручки"; break;
		// }
		// if($type == 'texture' AND $datatype = 'kitchen'){
			// $textureBtn = "<div style='margin-top:0px; margin-bottom:8px; font-size:12px; color:#dea993; cursor:pointer;' onclick='loadinfo(this);' data-type='texture' data-name='$setname'>Модели кухонь</div>";
		// }
		
		
		
		// if($type !== '3d_model' AND $type !== 'techn_descr'){
			// $a.="<h2 style='margin-bottom:32px;'>$rustype</h2>";
			// $a.="<div style='width:100%; display:inline-grid; grid-template-columns:repeat(5, 1fr); grid-gap:16px; margin-bottom:84px; align-items:start; line-height:1.4'>";
		// }
		
		
		// foreach($val as $item){
			
			// $thisname = $item['name'];
			// $thisfile = $item['file'];
			// $thispreview = $item['preview'];
			
			// $filelink = "/wp-content/uploads/designers_files/data_dir/$thisfile";
			
			// $ext = "<b>" . mb_strtoupper(end(explode(".", $thisfile))) . "</b>";
			// $file = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/designers_files/data_dir/$thisfile";
			// $filesize = round(filesize($file) / 1024) . " Kb";
		
			
			// else{
				// $a.="
				// <div>
					// <a href='$filelink' target='_blank'>
						// <img src='$thispreview'>
					// </a>
					// <div style='font-size:20px; font-weight:400; margin-top:16px;'>$thisname</div>
					// $textureBtn
				// </div>
				// ";
			// }
		// }
		
		// if($type !== '3d_model' AND $type !== 'techn_descr'){
			// $a.="</div>";
		// }

		//}
	


	return $b;	

}

function write_texture_content($texture){

	global $wpdb;
	
	$kitchens_str = "";
	$sql = "SELECT DISTINCT kitchen FROM gi_designer_architect WHERE setname = '$texture' AND type='texture'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$kitchens = $row->kitchen;
		$kitchen_str.= $kitchens . ", ";
	}
	
	$kitchens_array = explode(', ', $kitchen_str);
	$kitchens_array = array_filter(array_unique($kitchens_array));
	$a="
	<div id='modal_window' style='width:100%; height:100%; position:fixed; left:0; top:0; z-index:999999; background:#fff; display:none; overflow-y:auto;'>
		<div id='close_modal_content' style='position:fixed; top:0; right:0; padding:16px; cursor:pointer;'>
			<i class='fa fa-times fa-3x'></i>
		</div>
		<img id='loadingicon' src='/wp-content/themes/wp-diary/images/loading.gif' style='width:150px; height:150px; position:absolute; top:calc(50% - 75px); left:calc(50% - 75px);'>
		<div id='modal_content' style='width:1100px; margin:32px auto; padding:32px;'></div>
	</div>";
	$a.="<div style='width:100%; display:inline-grid; grid-template-columns:repeat(3,1fr); grid-gap:32px;'>";
	foreach($kitchens_array as $kitchen_name){
		
		$sql2 = "SELECT * FROM gi_kitchen WHERE name = '$kitchen_name'";
		$result2 = $wpdb->get_results($sql2);
		foreach($result2 as $row2){
			$name = $row2-> name;
			$avatar = $row2->avatar;
			$material = $row2->material;
			$a.="
			<div style='cursor:pointer;' data-type='kitchen' data-name='$name' onclick='loadinfo(this);'>
				<div class='kitchen_ava' style='width:100%; height:200px; background:url($avatar) no-repeat; background-size:cover; background-position:center;'></div>
				<div style='font-size:24px; font-weight:600;'>$name</div>
				<div style='font-size:12px; display:block; margin-top:-4px;'>$material</div>
			</div>
			";
		}
	}
	$a.="</div>";
	
	return $a;
}

switch($datatype){
	case 'kitchen': echo write_kitchen_content($name); break;
	case 'texture': echo write_texture_content($name); break;
}
?>