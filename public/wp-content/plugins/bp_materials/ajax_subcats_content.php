<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
$postid = $_POST['postid'];
$postarray = get_post( $postid );
$content .= $postarray ->post_content;

$content.= "
<script>
//прописываем размеры iframe
var iframe = document.getElementById('post_content');
var iframe_width = iframe.offsetWidth;
var iframe_height = iframe_width /2;
jQuery('#post_content iframe').css('height', iframe_height);
</script>
";
echo $content; 
?>

