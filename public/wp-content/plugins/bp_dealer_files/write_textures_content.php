<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$type = $_POST['type'];
$kitchen = $_POST['kitchen'];

$sql = "SELECT * FROM gi_designer_textures WHERE type = '$type' AND kitchens LIKE '%$kitchen%'";
$result = $wpdb->get_results($sql);
foreach($result as $row){
	$name = $row->name;
	$file = $row->thisfile;
	$image = "/wp-content/uploads/designers_files/textures/$file";
	$thumb = "/wp-content/uploads/designers_files/thimbnails/$file";
	$a.="
	<a href='$image' target='_blank'>
		<div class='des_texture_img' style='border:1px solid #f5f5f5;'>
			<img src='$thumb' style='width:100%; height:100%; object-fit: cover; transform:scale(2);'/>
		</div>
		<div style='color:#3d3d3d; margin-top:8px; margin-left:8px; font-size:18px; line-height:1.3; width:calc(100% - 48px); margin-right:8px; display:inline-block; vertical-align:top;'>$name</div>
		<div style='display:inline-block; width:20px; vertical-align:top; margin-right:8px;'>
			<img src='/wp-content/plugins/bp_dealer_files/images/loup.png' style='width:100%; margin-top:8px;'/>
		</div>
	</a>
	";
}

echo $a;
?>