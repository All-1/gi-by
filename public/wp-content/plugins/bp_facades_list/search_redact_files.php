<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;

$thisval = $_POST['thisval'];
$matval = $_POST['matval'];
$modelval = $_POST['modelval'];
$and_where_material = !empty($matval) ? "AND type = '$matval'" : '';
$and_where_model = !empty($modelval) ? "AND kitchens LIKE '%$modelval%'" : '';

$sql2 = "SELECT * FROM gi_facades_list WHERE (name LIKE '%$thisval%' OR type LIKE '%$thisval%' OR kitchens LIKE '%$thisval%') $and_where_material $and_where_model ORDER BY `facade_rank` ASC LIMIT 200";
$result2 = $wpdb->get_results($sql2);
$c = '';
foreach ($result2 as $row2) {
	$newid = $row2->newid;
	$type = $row2->type;
	$kitchens = $row2->kitchens;
	$thisfile = $row2->image;
	$thisname = $row2->name;
	$rank = $row2->facade_rank;
	$thisdescr = $row2->description;
	$new = $row2->newfacade;
	$antibac = $row2->antibac;
	$silver = $row2->silver;

	if ($new) {
		$checked_new = "checked";
	} else {
		$checked_new = "";
	}

	if ($silver) {
		$checked_silver = "checked";
	} else {
		$checked_silver = "";
	}

	if ($antibac) {
		$checked_antibac = "checked";
	} else {
		$checked_antibac = "";
	}

	$kitchen_cheboxes = "<div id='div_content_$newid' style='width:100%; display:inline-grid; grid-template-columns:repeat(4,1fr); grid-gap:8px;'>";
	$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen";
	$result_kitchens = $wpdb->get_results($sql_kitchens);
	foreach ($result_kitchens as $row_kitchens) {
		$kitchen = $row_kitchens->name;
		if (stristr($kitchens, $kitchen)) {
			$checked = "checked";
		} else
			$checked = "";
		$kitchen_cheboxes .= "
		<div class='kitchen_checkboxes_$newid'><input type='checkbox' name='kitchens[]' value='$kitchen' $checked> $kitchen</div>
		";
	}
	$kitchen_cheboxes .= "</div>";

	$imgurl = "/wp-content/uploads/facades_list/textures/$thisfile";
	$thumburl = "/wp-content/uploads/facades_list/thumbnails/$thisfile";
	$c .= "
	<div id='redact_$newid' style='width:calc(100% - 16px); background:#fff; box-shadow:3px 6px 18px rgba(1,1,1,0.1); display:inline-grid; grid-template-columns:1fr 2fr 1fr 5fr 1fr; grid-gap:16px; padding:8px;'>
		<div>
			<a href='$imgurl' target='_blank'>
				<img src='$thumburl' style='width:100%; max-height:150px;'/>
				<input id='file_$newid' value='$thisfile' style='display:none;'>
			</a>
		</div>
		<div>	
			<input id='thisname_$newid' name='thisname' value='$thisname' style='width:100%; height:45px; border:1px solid #dcdcdc;'><br>
			<textarea id='thisdescr_$newid' style='width:100%; height:45px; border:1px solid #dcdcdc; margin-top:8px;' placeholder='Описание'>$thisdescr</textarea>
			<div class='input-type' style='display: grid; grid-template-columns: 1fr 1fr;'>
				<div class='input-rate'>
					Рейтинг: <input name='thisrank' value='$rank' id='thisrank_$newid' style='margin-top:8px; width:45px; height:35px; text-align:center; border:1px solid #dcdcdc; margin-right:16px;' type='number' min='0'/>
				</div>
				<div>
					<div class='input-new' style='float: right; margin-bottom: 5px'>
					Новинка <input id='new_$newid' type='checkbox' name='new' value='new' style='margin-left:8px;' $checked_new/>
					</div>
					<div class='input-silver' style='float: right; margin-bottom: 5px'>
					Silver Defence <input id='silver_$newid' type='checkbox' name='silver' value='silver' style='margin-left:8px;' $checked_silver/>
					</div>
					<div class='input-antibac' style='float: right; margin-bottom: 5px'>
					Antibac <input id='antibac_$newid' type='checkbox' name='antibac' value='antibac' style='margin-left:8px;' $checked_antibac/>
					</div>
				</div>
			</div>
		</div>
		<div>
			<select id='thistype_$newid' name='thistype' style='width:100%; height:45px;'>
				<option selected disabled value=''>Выберите тип фасадов</option>";
				$c = materialFacadeSelect($c);
			$c .= "</select>
		</div>
		
		$kitchen_cheboxes
		
		
		<div>
			<button id='$newid' value='redact' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:green; border:none; padding:8px 24px;' onclick=\"redact_texture($newid);\">Изменить</button>
			<button id='$newid' value='delete' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:darkred; border:none; padding:8px 24px' onclick=\"delete_texture($newid);\">Удалить</button>
		</div>			
	</div>";

	$c .= "<script>
	jQuery('#thistype_$newid option[value=\'$type\']').attr('selected','selected');
	</script>";

}


echo $c;
?>