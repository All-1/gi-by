<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
$type = $_POST['type'];
$header = $_POST['header'];
$sql_links = "SELECT * FROM `gi_acsessories` WHERE `triger` = '$type'";
$result_links = $wpdb->get_results($sql_links);
$images_links .="
<div id='subcats_div' class='subcats_div'>
	
	<span class='subcats_header'>$header</span>
	<hr class='subcats_hr'>
";
foreach ($result_links as $row_links){
	$content_link = $row_links->content_link;
	$id = url_to_postid("$content_link");
	$post_data = get_post( $id );
	$title = $post_data->post_title;
	$content = $post_data->post_content;
	$content_div .= "<div style='margin:64px 0 32px 0;'> $content </div>";
	$image = $row_links-> image_link;
	$name = $row_links->name;
	$images_links .= "
	<div class='one_subcat_div' data-id='$id' onclick=\"getPostId(this);\">
		<div class='acsess_subcat_image' style='background:url($image) no-repeat; background-size:cover; background-position:center;'></div>
		<div class='one_subcat_name'>$name</div>
	</div>
	";
}
$images_links.="</div>";
$script = "
<script>
var subcats_div = document.getElementById('subcats_div');
var subcat_image = document.getElementsByClassName('acsess_subcat_image');
var subcat_image_width = subcat_image[0].offsetWidth;
jQuery('.acsess_subcat_image').css('height', subcat_image_width);

//ПОЛУЧАЕМ ID ПОСТА и аяксом контент вытягиваем
function getPostId(obj){
	jQuery('.page_content_div').css('display', 'block');
	var target = document.getElementById ('post_content');
	jQuery('html, body').animate({scrollTop: jQuery(target).offset().top - 150}, 500);
	postid = jQuery(obj).data('id');
	jQuery.ajax({
		url: '/wp-content/plugins/bp_materials/ajax_subcats_content.php',
		type: 'POST',
		data: {postid:postid},
		cache: false,
		success: function(html){  
			jQuery('#post_content').html(html);  
		} 
	});
}
</script>
";
$all_content = $images_links . $script;
echo $all_content;
?>