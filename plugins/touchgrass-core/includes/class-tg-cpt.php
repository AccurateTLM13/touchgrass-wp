<?php
/**
 * Custom post types: tg_faq, tg_testimonial, tg_subscriber.
 * Contract: ~/workspace/repos/touchgrass-wp/CONTRACT.md
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_CPT {

	public static function init() {
		add_action( 'init', [ __CLASS__, 'register' ] );
		add_action( 'add_meta_boxes', [ __CLASS__, 'meta_boxes' ] );
		add_action( 'save_post_tg_faq', [ __CLASS__, 'save_faq' ] );
		add_action( 'save_post_tg_testimonial', [ __CLASS__, 'save_testimonial' ] );
	}

	public static function register() {
		register_post_type( 'tg_faq', [
			'labels' => [
				'name'          => __( 'FAQs', 'touchgrass-core' ),
				'singular_name' => __( 'FAQ', 'touchgrass-core' ),
				'add_new_item'  => __( 'Add FAQ', 'touchgrass-core' ),
				'edit_item'     => __( 'Edit FAQ', 'touchgrass-core' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'editor' ],
			'menu_icon'    => 'dashicons-editor-help',
			'rewrite'      => false,
		] );

		register_post_type( 'tg_testimonial', [
			'labels' => [
				'name'          => __( 'Testimonials', 'touchgrass-core' ),
				'singular_name' => __( 'Testimonial', 'touchgrass-core' ),
				'add_new_item'  => __( 'Add testimonial', 'touchgrass-core' ),
				'edit_item'     => __( 'Edit testimonial', 'touchgrass-core' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'editor' ],
			'menu_icon'    => 'dashicons-format-quote',
			'rewrite'      => false,
		] );

		/* Subscribers: never public, visible in WP admin only. */
		register_post_type( 'tg_subscriber', [
			'labels' => [
				'name'          => __( 'Subscribers', 'touchgrass-core' ),
				'singular_name' => __( 'Subscriber', 'touchgrass-core' ),
			],
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'touchgrass-core',
			'supports'     => [ 'title' ],
			'menu_icon'    => 'dashicons-email',
		] );

		register_post_meta( 'tg_faq', '_tg_faq_order', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'integer',
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'tg_testimonial', '_tg_role', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'tg_testimonial', '_tg_rating', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'integer',
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}

	public static function meta_boxes() {
		add_meta_box( 'tg_faq_order', __( 'Display order', 'touchgrass-core' ), [ __CLASS__, 'faq_box' ], 'tg_faq', 'side' );
		add_meta_box( 'tg_testimonial_details', __( 'Reviewer details', 'touchgrass-core' ), [ __CLASS__, 'testimonial_box' ], 'tg_testimonial', 'side' );
	}

	public static function faq_box( $post ) {
		wp_nonce_field( 'tg_faq_meta', 'tg_faq_meta_nonce' );
		$order = (int) get_post_meta( $post->ID, '_tg_faq_order', true );
		echo '<label for="tg_faq_order_field">' . esc_html__( 'Lower numbers show first.', 'touchgrass-core' ) . '</label><br>';
		echo '<input type="number" id="tg_faq_order_field" name="tg_faq_order" value="' . esc_attr( $order ) . '" min="0" style="width:100%;margin-top:6px">';
	}

	public static function testimonial_box( $post ) {
		wp_nonce_field( 'tg_testimonial_meta', 'tg_testimonial_meta_nonce' );
		$role   = get_post_meta( $post->ID, '_tg_role', true );
		$rating = (int) get_post_meta( $post->ID, '_tg_rating', true );
		if ( ! $rating ) { $rating = 5; }
		echo '<label for="tg_role_field">' . esc_html__( 'Role / product', 'touchgrass-core' ) . '</label><br>';
		echo '<input type="text" id="tg_role_field" name="tg_role" value="' . esc_attr( $role ) . '" style="width:100%;margin:6px 0 12px" placeholder="' . esc_attr__( 'Backend dev · The Daily Driver', 'touchgrass-core' ) . '"><br>';
		echo '<label for="tg_rating_field">' . esc_html__( 'Rating (1–5)', 'touchgrass-core' ) . '</label><br>';
		echo '<select id="tg_rating_field" name="tg_rating" style="width:100%;margin-top:6px">';
		for ( $i = 1; $i <= 5; $i++ ) {
			echo '<option value="' . esc_attr( $i ) . '"' . selected( $rating, $i, false ) . '>' . esc_html( $i ) . '</option>';
		}
		echo '</select>';
	}

	public static function save_faq( $post_id ) {
		if ( ! isset( $_POST['tg_faq_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tg_faq_meta_nonce'] ), 'tg_faq_meta' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		$order = isset( $_POST['tg_faq_order'] ) ? (int) $_POST['tg_faq_order'] : 0;
		update_post_meta( $post_id, '_tg_faq_order', $order );
	}

	public static function save_testimonial( $post_id ) {
		if ( ! isset( $_POST['tg_testimonial_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tg_testimonial_meta_nonce'] ), 'tg_testimonial_meta' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( isset( $_POST['tg_role'] ) ) {
			update_post_meta( $post_id, '_tg_role', sanitize_text_field( wp_unslash( $_POST['tg_role'] ) ) );
		}
		if ( isset( $_POST['tg_rating'] ) ) {
			$rating = (int) $_POST['tg_rating'];
			update_post_meta( $post_id, '_tg_rating', max( 1, min( 5, $rating ) ) );
		}
	}
}

TG_CPT::init();
