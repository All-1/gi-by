<?php
/**
 * Template part for displaying single post
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>


	<header class="entry-header">


	</header><!-- .entry-header -->


	<div class="entry-content">

		<?php the_content(); ?>

	</div> <!-- .entry-content -->

	<footer class="entry-footer">

		<?php wp_diary_entry_footer(); ?>

	</footer><!-- .entry-footer -->

	<?php get_template_part( 'template-parts/author/author', 'box' ); ?>

</article><!-- #post-<?php the_ID(); ?> -->
