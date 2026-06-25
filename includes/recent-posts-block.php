<?php
/**
 * PHP-only "Recent Posts" block (webinar-wp70/recent-posts).
 *
 * A WordPress 7.0 PHP-only block: `register_block_type()` with
 * `supports.autoRegister` and a `render_callback` — no block.json, no JS build.
 * It server-renders a list of recent posts (site-global data, so it renders
 * live in the editor preview too), and its three attributes map to the
 * auto-generated select / number / checkbox controls in the Inspector.
 *
 * @package WebinarWpcomesNewfordevsWp70
 */

/**
 * Register the block in PHP only.
 *
 * @return void
 */
function wp_ai_workshop_register_recent_posts_block() {
	register_block_type(
		'webinar-wp70/recent-posts',
		array(
			'title'           => __( 'Recent Posts (PHP)', 'wp-ai-workshop' ),
			'description'     => __( 'A list of recent posts, server-rendered and registered entirely in PHP — no block.json, no build step.', 'wp-ai-workshop' ),
			'category'        => 'widgets',
			'icon'            => 'list-view',
			'attributes'      => array(
				'numberOfPosts' => array(
					'label'   => __( 'Number of posts', 'wp-ai-workshop' ),
					'type'    => 'integer',
					'default' => 5,
				),
				'order'         => array(
					'label'   => __( 'Order', 'wp-ai-workshop' ),
					'type'    => 'string',
					'enum'    => array( 'newest', 'oldest', 'alphabetical' ),
					'default' => 'newest',
				),
				'showDate'      => array(
					'label'   => __( 'Show date', 'wp-ai-workshop' ),
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => 'wp_ai_workshop_render_recent_posts_block',
			'supports'        => array( 'autoRegister' => true ),
		)
	);
}
add_action( 'init', 'wp_ai_workshop_register_recent_posts_block' );

/**
 * Enqueue the block stylesheet (front end + editor canvas).
 *
 * @return void
 */
function wp_ai_workshop_enqueue_recent_posts_assets() {
	$path = plugin_dir_path( __DIR__ ) . 'assets/recent-posts.css';

	wp_enqueue_style(
		'wp-ai-workshop-recent-posts',
		plugins_url( 'assets/recent-posts.css', __DIR__ ),
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '1'
	);
}
add_action( 'enqueue_block_assets', 'wp_ai_workshop_enqueue_recent_posts_assets' );

/**
 * Render the block: a list of recent posts.
 *
 * @param array $attributes Block attributes: numberOfPosts, order, showDate.
 * @return string Escaped HTML.
 */
function wp_ai_workshop_render_recent_posts_block( $attributes ) {
	$number    = max( 1, min( 20, (int) ( $attributes['numberOfPosts'] ?? 5 ) ) );
	$order     = $attributes['order'] ?? 'newest';
	$show_date = ! empty( $attributes['showDate'] );

	$sorts                 = array(
		'newest'       => array( 'date', 'DESC' ),
		'oldest'       => array( 'date', 'ASC' ),
		'alphabetical' => array( 'title', 'ASC' ),
	);
	list( $orderby, $dir ) = $sorts[ $order ] ?? $sorts['newest'];

	$posts = get_posts(
		array(
			'numberposts' => $number,
			'post_status' => 'publish',
			'orderby'     => $orderby,
			'order'       => $dir,
		)
	);

	$wrapper = get_block_wrapper_attributes( array( 'class' => 'wp-recent-posts' ) );

	if ( empty( $posts ) ) {
		return sprintf( '<p %s>%s</p>', $wrapper, esc_html__( 'No posts published yet.', 'wp-ai-workshop' ) );
	}

	$items = '';
	foreach ( $posts as $post ) {
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( get_permalink( $post ) ),
			esc_html( get_the_title( $post ) )
		);

		if ( $show_date ) {
			$link .= sprintf(
				' <time class="wp-recent-posts__date">%s</time>',
				esc_html( get_the_date( '', $post ) )
			);
		}

		$items .= sprintf( '<li class="wp-recent-posts__item">%s</li>', $link );
	}

	return sprintf( '<ul %s>%s</ul>', $wrapper, $items );
}
