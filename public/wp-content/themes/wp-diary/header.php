<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
		<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(94196842, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(46264947, 'init', {webvisor:true, trackHash:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/46264947" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<noscript><div><img src="https://mc.yandex.ru/watch/94196842" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<?php
	if (!is_user_logged_in()) {
		//echo "<script src='//code.jivosite.com/widget/mSsSbdvUXR' async></script>";
	} else //echo "<script src='//code.jivosite.com/widget/3ScpUY5uGn' async></script>";
	
	?>
	<!--<script src='//code.jivosite.com/widget/mSsSbdvUXR' async></script>-->

	
</head>


<?php
//По IP вносим в сессию страну юзера
//http://www.ip2nation.com/ip2nation/Download - обновление БД
/*if(empty($_SESSION['ip_country'])){
	global $wpdb;
	$ipaddr = $_SERVER['REMOTE_ADDR'];
	$sql = 'SELECT
			c.country
			FROM
			ip2nationCountries c,
			ip2nation i
			WHERE
			i.ip < INET_ATON("'.$ipaddr.'")
			AND
			c.code = i.country
			ORDER BY
			i.ip DESC
			LIMIT 0,1';
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$countryName = $row->country;
	}
	$_SESSION['ip_country'] = $countryName;
}*/

?>


