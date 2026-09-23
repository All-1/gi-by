<?php
/**
 * Template Name: Работа в компании

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
					if ($post_id == 38) {
						$work = "selected";
					} else
						$work = "unselected";
					if ($post_id == 117) {
						$staj = "selected";
					} else
						$staj = "unselected";
					if ($post_id == 119) {
						$karera = "selected";
					} else
						$karera = "unselected";
					if ($post_id == 121) {
						$korpor = "selected";
					} else
						$korpor = "unselected";
					?>
					<a href='/rabota-v-kompanii/'><button class='<? echo $work; ?>'>Работа в компании</button></a>
					<a href='/stazhirovka/'><button class='<? echo $staj; ?>'>Стажировака</button></a>
					<a href='/karera/'><button class='<? echo $karera; ?>'>Карьера</button></a>
					<a href='/korporativnaya-kultura/'><button class='<? echo $korpor; ?>'>Корпоративная культура</button></a>

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

							// If comments are open or we have at least one comment, load up the comment template.
							if (comments_open() || get_comments_number()):
								comments_template();
							endif;

						endwhile; // End of the loop.
						?>

					</main><!-- #main -->
				</div><!-- #primary -->

				<?php echo do_shortcode("[cities_window]"); ?>

				<?php
				get_sidebar();
				get_footer();
