<?php
/**
 * Template Name: Продажа образцов

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
					<button id="samples_city_select" class='samples_city_select'
						style='display:block; margin:auto; padding:20px 60px; margin-bottom:60px;'>Выбор города</button>

					<style>
						.selected {
							background: #666;
							color: white;
						}

						.unselected {
							background: white;
							color: #666;
						}
					</style>
					<main id="main" class="site-main">
						<?php
						while (have_posts()):
							the_post();

							get_template_part('template-parts/content', 'page');

							?>

							<button id="samples_city_select2" class='samples_city_select'
								style='display:block; margin:auto; padding:20px 60px; margin-top:60px;'>Выбор города</button>

							<?
							// If comments are open or we have at least one comment, load up the comment template.
							if (comments_open() || get_comments_number()):
								comments_template();
							endif;

						endwhile; // End of the loop.
						?>

					</main><!-- #main -->
				</div><!-- #primary -->

				<?php echo do_shortcode("[samples_cities_window]"); ?>
				<?php echo do_shortcode("[cities_window]"); ?>

				<?php
				get_sidebar();
				get_footer();
