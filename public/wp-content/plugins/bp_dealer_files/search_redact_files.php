<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$thisval = $_POST['thisval'];
$matval = $_POST['matval'];
$modelval = $_POST['modelval'];
if(!empty($matval)){
	$and_where_material = "AND type = '$matval'";
}
if(!empty($modelval)){
	$and_where_model = "AND kitchens LIKE '%$modelval%'";
}

$sql2 = "SELECT * FROM gi_designer_textures WHERE (name LIKE '%$thisval%' OR type LIKE '%$thisval%' OR kitchens LIKE '%$thisval%') $and_where_material $and_where_model ORDER BY newid ASC LIMIT 200";
$result2 = $wpdb->get_results($sql2);
foreach($result2 as $row2){
	$newid = $row2->newid;
	$type = $row2->type;
	$kitchens = $row2->kitchens;
	$thisfile = $row2->thisfile;
	$thisname = $row2->name;
	
	$kitchen_cheboxes = "<div id='div_content_$newid' style='width:100%; display:inline-grid; grid-template-columns:repeat(4,1fr); grid-gap:8px;'>";
	$sql_kitchens = "SELECT DISTINCT name FROM gi_kitchen";
	$result_kitchens = $wpdb->get_results($sql_kitchens);
	foreach($result_kitchens as $row_kitchens){
		$kitchen = $row_kitchens->name;
		if(stristr($kitchens, $kitchen)){
			$checked = "checked";
		}
		else $checked = "";
		$kitchen_cheboxes .="
		<div class='kitchen_checkboxes_$newid'><input type='checkbox' name='kitchens[]' value='$kitchen' $checked> $kitchen</div>
		";
	}
	$kitchen_cheboxes .= "</div>";
	
	$imgurl = "/wp-content/uploads/designers_files/textures/$thisfile";
	$thumburl = "/wp-content/uploads/designers_files/thimbnails/$thisfile";
	$c.="
	<div id='redact_$newid' style='width:calc(100% - 16px); background:#fff; box-shadow:3px 6px 18px rgba(1,1,1,0.1); display:inline-grid; grid-template-columns:1fr 2fr 1fr 5fr 1fr; grid-gap:16px; padding:8px;'>
		<div>
			<a href='$imgurl' target='_blank'>
				<img src='$thumburl' style='width:100%; max-height:150px;'/>
				<input id='file_$newid' value='$thisfile' style='display:none;'>
			</a>
		</div>
		<div><input id='thisname_$newid' name='thisname' value='$thisname' style='width:100%; height:45px; border:1px solid #dcdcdc;'></div>
		<div>
			<select id='thistype_$newid' name='thistype' style='width:100%; height:45px;'>
				<option value='Массив, Шпон'>Массив, Шпон</option>
				<option value='Мдф'>Мдф</option>
				<option value='Skin'>Skin</option>
				<option value='Syncron'>Syncron</option>
				<option value='Egger'>Egger</option>
				<option value='FENIX'>FENIX</option>
				<option value='Senosan матовый'>Senosan матовый</option>
				<option value='Senosan глянец'>Senosan глянец</option>
				<option value='Постформинг'>Постформинг</option>
			</select>
		</div>
		
		$kitchen_cheboxes
		
		
		<div>
			<button id='$newid' value='redact' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:green; border:none; padding:8px 24px;' onclick=\"redact_texture($newid);\">Изменить</button>
			<button id='$newid' value='delete' style='cursor:pointer; width:100%; margin:0 0 16px 0; color:#fff; background:darkred; border:none; padding:8px 24px' onclick=\"delete_texture($newid);\">Удалить</button>
		</div>			
	</div>";
	
	$c.="<script>
	jQuery('#thistype_$newid option[value=$type]').attr('selected','selected');
	</script>";
	
}


$c.="
<script>
function redact_texture(thisid){
	var thisname = jQuery('#thisname_'+thisid).val();
	var thistype = jQuery('#thistype_'+thisid).val();
	var checkedkitchens = [];
	jQuery('.kitchen_checkboxes_'+thisid+ ' input:checkbox:checked').each(function() {
		checkedkitchens.push(this.value);
	});
	var kitchensstring = checkedkitchens.toString();
	kitchensstring = kitchensstring.replace(/,/g, \", \");
	
	jQuery.ajax({
		url: '/wp-content/plugins/bp_dealer_files/redact_textures_data.php',
		type: 'POST',
		data: {thisid:thisid, thisname:thisname, thistype:thistype, kitchensstring:kitchensstring},
		success: function(data){ 
			alert('Правки внесены');
		} 
	});
	
}

function delete_texture(thisid){
	var thisfile = jQuery('#file_'+thisid).val();
	
	jQuery.ajax({
		url: '/wp-content/plugins/bp_dealer_files/delete_textures_data.php',
		type: 'POST',
		data: {thisid:thisid, thisfile:thisfile},
		success: function(data){ 
			alert('Удалено');
			jQuery('#redact_'+thisid).css('display','none');
		} 
	});
}
</script>
";

echo $c;
?>