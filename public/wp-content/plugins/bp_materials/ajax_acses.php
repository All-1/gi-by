<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
$type = $_POST['type'];
echo "$type <br>";
$sql_links = "SELECT * FROM `gi_acsessories` WHERE `triger` = '$type'";
$result_links = $wpdb->get_results($sql_links);
foreach ($result_links as $row_links){
	$content_link = $row_links->content_link;
	$id = url_to_postid("$content_link");
	$post_data = get_post( $id );
	//$title = $post_data->post_title;
	$content = $post_data->post_content;
	$content_div .= "<div style='margin:64px 0 32px 0;'> $content </div>";
	$image = $row_links-> image_link;
	$name = $row_links->name;
	$images_links .= "
	<div class='acsesItemDiv'>
		<div class='acsesItem' style='background:url($image) no-repeat; background-size:cover; background-position:center;'></div>
		<div>$name</div>
	</div>
	";
}
$script = "
<script>
jQuery('a[href*=#]').bind('click', function(e){
      var anchor = jQuery(this);
      jQuery('html, body').stop().animate({
         scrollTop: jQuery(anchor.attr('href')).offset().top-100
      }, 1000);
      e.preventDefault();
   });
</script>
";
$all_content = $images_links . $script;
echo $all_content;
?>