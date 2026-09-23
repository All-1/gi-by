<?php
/**
 * Template part for displaying related post
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

if( has_post_thumbnail() ) {
    $post_class = 'has-thumbnail wow fadeInUp';
} else {
    $post_class = 'no-thumbnail wow fadeInUp';
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>

	<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"> <?php wp_diary_post_thumbnail(); ?> </a>

	<div class="mt-cats-list">

		<?php //wp_diary_article_categories_list(); ?>

	</div>

	<header class="entry-header">

		<?php the_title( '<div class="entry-title" style="font-size:18px; line-height:20px; margin:20px 0;"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></div>' ); ?>

	</header><!-- .entry-header -->

	<div class="entry-meta">
		<?php
			//wp_diary_posted_on();
			//wp_diary_posted_comments();
		?>
		<a href="<?php the_permalink(); ?>"><button class='myBtn'>Подробнее</button></a>
	</div><!-- .entry-meta -->

	<footer class="entry-footer">

		<?php wp_diary_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
