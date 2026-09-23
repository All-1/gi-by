<?php
/*Template Name: My login page

 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */
if(is_user_logged_in()){
	$redirect_url = redirectUrl();
	wp_redirect($redirect_url, 307); 
}
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
		
	<?php echo do_shortcode("[cities_window]"); ?>
	<?php echo do_shortcode("[samples_cities_window]"); ?>
		
		
	<div id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

		</main><!-- #main -->
	</div><!-- #primary -->
	

<?php
get_sidebar();
get_footer();
