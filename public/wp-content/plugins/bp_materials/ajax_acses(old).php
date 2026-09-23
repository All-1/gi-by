<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
$type = $_POST['type'];
echo "<script>alert($type);</script>";
$sql_links = "SELECT * FROM `gi_acsessories` WHERE `triger` = '$type'";
$result_links = $wpdb->get_results($sql_links);
foreach ($result_links as $row_links){
	$image = $row_links-> image_link;
	$name = $row_links->name;
	$scroll_link = str_replace(" ", "_", $name);
	$content_link = $row_links->content_link;
	$id = url_to_postid("$content_link");
	$post_data = get_post( $id );
	$content = $post_data->post_content;
	$images_links .= "
	<a href='#$scroll_link' onclick='newurl(this);' data-url='$content_link'>
		<div class='acsesItemDiv'>
			<div class='acsesItem' style='background:url($image) no-repeat; background-size:cover; background-position:center;'></div>
			<div>$name</div>
		</div>
	</a>
	";
	$content_div .= "<div id='$scroll_link'> $content </div> <hr class='wp-block-separator is-style-default'>";
}
$script = "
<script>
function newurl(obj){
	var urlval = jQuery(obj).attr('data-url');
	var baseUrl = 'https://ideal-kuhni.ru';
	var newUrl = baseUrl + urlval;
	history.pushState(null, null, newUrl);
	window.location.href.split('#')[0];
}
jQuery('a[href*=#]').bind('click', function(e){
      var anchor = jQuery(this);
      jQuery('html, body').stop().animate({
         scrollTop: jQuery(anchor.attr('href')).offset().top-100
      }, 1000);
      e.preventDefault();
   });
</script>
";
$all_content = $images_links . "<br>" . $content_div . $script;
echo $all_content;
?>