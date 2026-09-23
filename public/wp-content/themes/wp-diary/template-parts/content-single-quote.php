<?php
/**
 * Template part for displaying single quote post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">

		<?php the_content(); ?>

	</div> <!-- .entry-content -->
	
	<?php wp_diary_post_thumbnail(); ?>

	<div class="mt-cats-list">

		<?php wp_diary_article_categories_list(); ?>

	</div>

	<header class="entry-header">

		<?php the_title( '<div class="entry-title">', '</div>' ); ?>

	</header><!-- .entry-header -->

	<div class="entry-meta">
		<?php
			wp_diary_posted_on();
			wp_diary_posted_by();
			wp_diary_posted_comments();
		?>
	</div><!-- .entry-meta -->

	<footer class="entry-footer">

		<?php wp_diary_entry_footer(); ?>

	</footer><!-- .entry-footer -->

	<?php get_template_part( 'template-parts/author/author', 'box' ); ?>

</article><!-- #post-<?php the_ID(); ?> -->
