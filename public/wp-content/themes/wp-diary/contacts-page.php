<?php
/**
 * Template Name: Страница контактов

 * @package    bp
 * @author     averta (c) 2014-2018
 * @link       none
 */

get_header();
?>

<body <?php body_class(); ?>>

	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WCDGGVW" height="0" width="0"
			style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<div id="page" class="site">

		<?php require_once (dirname(__FILE__) . '/header-for-webpage.php');  ?>
		<div id="content" class="site-content">
			<div class="mt-container">
				<div id="primary" class="content-area">
					<!-----КНОПКИ ПЕРЕКЛЮЧЕНИЯ-------->
					<?php
					global $wp_query;
					$post_id = $wp_query->post->ID;
					if ($post_id == 40) {
						$fabr_cont = "selected";
					} else
						$fabr_cont = "unselected";
					if ($post_id == 127) {
						$distr = "selected";
					} else
						$distr = "unselected";
					if ($post_id == 132) {
						$otziv = "selected";
					} else
						$otziv = "unselected";
					?>
					<div style=''>
						<a href='/contacts/'><button class='<?php echo $fabr_cont; ?> myBtn'>Контакты фабрики</button></a>
						<a href='/contacts/distribyutory/'><button class='<?php echo $distr; ?> myBtn'>Дистрибьюторы</button></a>
						<!-----<a href='/contacts/ostavit-otzyv/'><button class='<? echo $otziv; ?>'>Оставить отзыв</button></a>---->
					</div>

					<style>
						.selected {
							background: #1e3350;
							color: white;
							margin-right: 32px;
							margin-top: 32px;
						}

						.unselected {
							background: white;
							color: #666;
							margin-right: 32px;
							margin-top: 32px;
						}
					</style>
					<main id="main" class="site-main">
						<?php
						while (have_posts()):
							the_post();

							get_template_part('template-parts/content', 'page');

						endwhile; // End of the loop.
						?>

					</main><!-- #main -->
				</div><!-- #primary -->
				<?php echo do_shortcode("[cities_window]"); ?>

				<?php
				get_sidebar();
				get_footer();
