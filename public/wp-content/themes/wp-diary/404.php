<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

get_header();
?>

<body <?php body_class(); ?>>

	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WCDGGVW" height="0" width="0"
			style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<div id="page" class="site">

		<?php require_once (dirname(__FILE__) . '/header-for-webpage.php'); ?>
		<div id="content" class="site-content">
			<div class="mt-container">
				<div id="primary" class="content-area">
					<main id="main" class="site-main">

						<section class="error-404 not-found">
							<div class="error-num">
								<?php esc_html_e('404', 'wp-diary'); ?> <span>
									<?php esc_html_e('error', 'wp-diary'); ?>
								</span>
							</div>
							<header class="page-header">
								<h1 class="page-title">
									<?php esc_html_e('Oops! That page can&rsquo;t be found.', 'wp-diary'); ?>
								</h1>
							</header><!-- .page-header -->
							<div class="page-content">
								<p>
									<?php esc_html_e('It looks like nothing was found at this location.', 'wp-diary'); ?>
								</p>
						</section><!-- .error-404 -->

					</main><!-- #main -->
				</div><!-- #primary -->

				<?php
				get_footer();
