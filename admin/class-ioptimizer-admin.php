<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://i-optimizer.com/
 * @since      1.0.0
 *
 * @package    Ioptimizer
 * @subpackage Ioptimizer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ioptimizer
 * @subpackage Ioptimizer/admin
 * @author     Angel Daniel Mainerici <mainerici.angel@gmail.com>
 */
class Ioptimizer_Admin {

	private $statusStrings =
		[
			'1' => 'Open',
			'2' => 'In progress',
			'3' => 'Optimized'
		];
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ioptimizer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ioptimizer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( get_current_screen()->base == 'toplevel_page_ioptimizer' ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ioptimizer-admin.css', array(), $this->version );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ioptimizer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ioptimizer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( get_current_screen()->base == 'toplevel_page_ioptimizer' ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ioptimizer-admin.js', array(
				'jquery',
				'wp-util'
			), $this->version, false );
			wp_localize_script( $this->plugin_name, 'ioptimizer_globals', [
				'get_tokens_nounce'   => wp_create_nonce( 'get_tokens' ),
				'bulk_process_nounce' => wp_create_nonce( 'bulk_process' ),
				'get_image_nounce'    => wp_create_nonce( 'get_image' ),
			] );
		}
	}

	public function ioptimizer_menu_item() {
		add_menu_page( 'Ioptimizer', 'IOptimizer', 'manage_options', 'ioptimizer', 'ioptimizer_options_page_html' );
	}

	public function ioptimizer_register_settings() {
		register_setting( 'ioptimizer_auth_group', 'ioptimizer_token' );
		register_setting( 'ioptimizer_auth_group', 'ioptimizer_lazy_load' );
		register_setting( 'ioptimizer_auth_group', 'ioptimizer_status' );
	}

	public function ajax_bulk_process() {
		check_ajax_referer('bulk_process');
		$data  = [];
		$posts = array_map( 'sanitize_text_field', $_POST['posts'] );
		foreach ( $posts as $post ) {
			$token  = bin2hex( openssl_random_pseudo_bytes( 20 ) );
			$data[] = [
				'token' => $token,
				'id'    => $post
			];
			update_post_meta( $post, 'ioptimizer_optimized', 'on' );
			update_post_meta( $post, 'ioptimizer_status', 2 );
			add_post_meta( $post, 'ioptimizer_token', $token );
		}
		$response = wp_remote_post( sprintf( '%sapi/image/process', get_option( 'ioptimizer_host' ) ), array(
			'headers' => [
				'token'     => get_option( 'ioptimizer_token' ),
				'platform'  => 'wordpress',
				'host-name' => get_option( 'siteurl' ),
			],
			'body'    => [
				'images' => $data,
			]
		) );
		if ( $response['response']['code'] == 200 ) {
			wp_send_json_success( 'ok' );
		} else {
			foreach ( $data as $post ) {
				update_post_meta( $post['id'], 'ioptimizer_optimized', '' );
				update_post_meta( $post['id'], 'ioptimizer_status', 1 );
				delete_post_meta( $post['id'], 'ioptimizer_token', $post['token'] );
			}
			wp_send_json_error( $response['body'] );
		}
	}

	function formatBytes( $bytes ) {
		$bytes   = floatval( $bytes );
		$arBytes = array(
			0 => array(
				"UNIT"  => "TB",
				"VALUE" => pow( 1024, 4 )
			),
			1 => array(
				"UNIT"  => "GB",
				"VALUE" => pow( 1024, 3 )
			),
			2 => array(
				"UNIT"  => "MB",
				"VALUE" => pow( 1024, 2 )
			),
			3 => array(
				"UNIT"  => "KB",
				"VALUE" => 1024
			),
			4 => array(
				"UNIT"  => "B",
				"VALUE" => 1
			),
		);

		foreach ( $arBytes as $arItem ) {
			if ( $bytes >= $arItem["VALUE"] ) {
				$result = $bytes / $arItem["VALUE"];
				$result = str_replace( ".", ",", strval( round( $result, 2 ) ) ) . " " . $arItem["UNIT"];
				break;
			}
		}

		return $result;
	}


	public function ajax_get_image() {
		check_ajax_referer('get_image');
		$query_images_args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 100,
			'order'          => 'DESC',
			'post_mime_type' => [
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/gif',
				'image/x-icon',
			],
			'offset'         => (int) sanitize_text_field( $_POST['existing_rows'] )
		);

		$query_images = new WP_Query( $query_images_args );
		if ( $query_images->found_posts ) {
			$continue = true;
			$html     = '';
			foreach ( $query_images->posts as $image ) {
				$optimized   = (bool) get_post_meta( $image->ID, 'ioptimizer_optimized', true );
				$status      = get_post_meta( $image->ID, 'ioptimizer_status', true );
				$initialSize = get_post_meta( $image->ID, 'ioptimizer_initial_size', true );
				if ( ! $initialSize ) {
					update_post_meta( $image->ID, 'ioptimizer_status', 1 );
					$initialSize = filesize( get_attached_file( $image->ID ) );
					add_post_meta( $image->ID, 'ioptimizer_initial_size', $initialSize );
				}
				$compressedSize = get_post_meta( $image->ID, 'ioptimizer_compressed_size', true );
				$html           .= '<tr>';

				if ( $optimized ) {
					$html .= '<td><input type="checkbox" class="ioptcheck" checked disabled></td>';
					$html .= sprintf( '<td><p>%s</p></td>', $image->post_name );
					$html .= sprintf( '<td><p>%s</p></td>', $this->statusStrings[ $status ] );
					$html .= sprintf( '<td>%s</td>', $this->formatBytes( $initialSize ) );
					if ( $status == '3' ) {
						$html    .= sprintf( '<td>%s</td>', $this->formatBytes( $compressedSize ) );
						$percent = 100 - ( ( get_post_meta( $image->ID, 'ioptimizer_compressed_size' )[0] * 100 ) / get_post_meta( $image->ID, 'ioptimizer_initial_size' )[0] );
						$html    .= sprintf( '<td>%s%%</td>', round( $percent, 2 ) );
					} else {
						$html .= '<td>---</td>';
						$html .= '<td>---</td>';
					}
				} else {
					$html .= sprintf( '<td><input type="checkbox" class="ioptcheck" data-image_id="%s"></td>', $image->ID );
					$html .= sprintf( '<td><p>%s</p></td>', $image->post_name );
					$html .= sprintf( '<td><p>%s</p></td>', $this->statusStrings[ $status ] );
					$html .= sprintf( '<td>%s</td>', $this->formatBytes( $initialSize ) );
					$html .= '<td>---</td>';
					$html .= '<td>---</td>';
				}
				$html .= sprintf( '<td class="ioptimizer-image-column"><img src="%s"/> </td>', wp_get_attachment_image_src( $image->ID )[0] );
				$html .= '</tr>';
			}
		} else {
			$continue = false;
		}
		wp_send_json_success( [ 'continue' => $continue, 'html' => $html ] );
	}

	function ajax_get_tokens() {
		check_ajax_referer('get_tokens');
		$response = wp_remote_get( sprintf( '%stoken/api/%s', get_option( 'ioptimizer_host' ), get_option( 'ioptimizer_token' ) ), [
			'headers' => [
				'platform'  => 'wordpress',
				'host-name' => get_option( 'siteurl' ),
			]
		] );

		if ( $response['response']['code'] == 200 ) {
			$response = json_decode( wp_remote_retrieve_body( $response ) );
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response );
		}
	}

}
