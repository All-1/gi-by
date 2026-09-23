<?php
/**
 * Template part for displayinng gallery post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

global $wp_query;

$current_post 			= $wp_query->current_post;
$post_id 				= get_the_ID();
$gallery                = get_post_gallery( $post_id, false );
$gallery_attachment_ids = explode( ',', $gallery['ids'] );
$layout_style           = wp_diary_is_sidebar_layout();
$thumbnail_size         = 'post-thumbnail';
$archive_style          = get_theme_mod( 'wp_diary_archive_style', 'mt-archive--masonry-style' );
$post_content_type 		= apply_filters( 'wp_diary_archive_post_content_type', 'excerpt' );

if ( ( 'no-sidebar' === $layout_style || 'no-sidebar-center' === $layout_style ) && 'mt-archive--classic-style' === $archive_style ) {
	$thumbnail_size = 'wp-diary-full-width';
}

if( $current_post < 3 && 'mt-archive--masonry-style' === $archive_style ) {
	$post_class = '';
} else {
	$post_class = 'wow fadeInUp';
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>
	
	<?php if ( ! empty( $gallery_attachment_ids ) ) { ?>

		<div class="post-format-media post-format-gallery post-format-media--gallery">
			<div class="mt-gallery-slider">
				<?php foreach ( $gallery_attachment_ids as $gallery_attachment_id ) { ?>
					<li>
						<?php echo wp_get_attachment_image( $gallery_attachment_id, $thumbnail_size ); // WPCS xss ok. ?>
					</li>
				<?php } ?>
			</div><!-- .mt-gallery-slider -->
		</div><!-- .post-format-gallery -->

	<?php } ?>

	<div class="entry-cat">
		<?php
			wp_diary_article_categories_list();
		?>
	</div><!-- .entry-meta -->

	<header class="entry-header">
		<?php the_title( '<div class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></div>' ); ?>
	</header><!-- .entry-header -->

	<div class="entry-meta">
		<?php
			wp_diary_posted_on();
			wp_diary_posted_comments();
		?>
	</div><!-- .entry-meta -->
	
	<div class="entry-content">
		<?php
			if ( 'excerpt' === $post_content_type ) {
				the_excerpt();
			} elseif ( 'content' === $post_content_type ) {
				the_content( sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'wp-diary' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				) );
			}
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php wp_diary_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
