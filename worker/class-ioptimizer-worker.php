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
class Ioptimizer_Admin_Worker {


	public function ioptimizer_attachment( $metadata, $attchmentId ) {
		$post = get_post( $attchmentId );
		if ( ! in_array( $post->post_mime_type,
			[
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/gif',
				'image/x-icon'
			]
		) ) {
			return true;
		}
		add_post_meta( $attchmentId, 'ioptimizer_initial_size', filesize( get_attached_file( $post->ID ) ) );
		if ( ! get_option( 'ioptimizer_status' ) ) {
			add_post_meta( $attchmentId, 'ioptimizer_status', 1 );
			add_post_meta( $attchmentId, 'ioptimizer_optimized', '' );

			return $metadata;
		}
		$token = bin2hex( openssl_random_pseudo_bytes( 20 ) );
		add_post_meta( $attchmentId, 'ioptimizer_optimized', 'on' );
		add_post_meta( $attchmentId, 'ioptimizer_status', 2 );
		add_post_meta( $attchmentId, 'ioptimizer_token', $token );
		$response = wp_remote_post( sprintf( '%sapi/image/process', get_option( 'ioptimizer_host' ) ), array(
			'headers' => [
				'token'     => get_option( 'ioptimizer_token' ),
				'platform'  => 'wordpress',
				'host-name' => get_option( 'siteurl' )
			],
			'body'    => [
				'images' => [
					[
						'token' => $token,
						'id'    => $attchmentId
					]
				]
			]
		) );
		if ( $response['response']['code'] != 200 ) {
			add_post_meta( $attchmentId, 'ioptimizer_optimized', '' );
			add_post_meta( $attchmentId, 'ioptimizer_status', 1 );
		}

		return $metadata;
	}

	public function init_api_endpoints() {
		register_rest_route(
			'v1/ioptimizer',
			'image/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'api_get_attachment' ),
			)
		);
		register_rest_route(
			'v1/ioptimizer',
			'image/(?P<id>\d+)/(?P<size>[\S]+)/(?P<path>[\S]+)/(?P<token>[\S]+)',
			array(
				'methods'  => 'PUT',
				'callback' => array( $this, 'api_replace_attachment' ),
			)
		);
		register_rest_route(
			'v1/ioptimizer',
			'image/(?P<id>\d+)/(?P<token>[\S]+)',
			array(
				'methods'  => 'PATCH',
				'callback' => array( $this, 'api_complet_process' ),
			)
		);
	}

	public function api_get_attachment( WP_REST_Request $request ) {
		$post = get_post( sanitize_text_field( $request['id'] ) );

		if ( $post ) {
			$response                      = [
				'mime_type' => $post->post_mime_type,
				'sizes'     => []
			];
			$response['sizes']['original'] = $post->guid;
			$datas                         = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
			$explode                       = explode( '/', $datas['file'] );
			$basePath                      = get_option( 'siteurl' ) . '/wp-content/uploads/' . $explode[0] . '/' . $explode[1] . '/';
			$response['sizes']['scaled']   = get_option( 'siteurl' ) . '/wp-content/uploads/' . $datas['file'];
			if ( ! empty( $datas['sizes'] ) ) {
				foreach ( $datas['sizes'] as $size => $url ) {
					$response['sizes'][ $size ] = $basePath . $url['file'];
				}
			}
			$response = new WP_REST_Response( $response );
		} else {
			$response = new WP_REST_Response();
			$response->set_status( 404 );
		}

		return $response;
	}

	public function api_replace_attachment( WP_REST_Request $request ) {
		$post = get_post( sanitize_text_field( $request['id'] ) );
		if ( ! $post || $request['token'] != get_post_meta( $post->ID, 'ioptimizer_token', true ) ) {
			$response = new  WP_REST_Response( 'post not Found' );
			$response->set_status( 404 );

			return $response;
		}
		$size = base64_decode( sanitize_text_field( $request['size'] ) );
		if ( $size == 'original' ) {
			$explode     = str_replace( '-scaled.', '.', explode( '/', get_post_meta( $post->ID, '_wp_attached_file', true ) ) );
			$destination = wp_get_upload_dir()['basedir'] . '/' . $explode[0] . '/' . $explode[1] . '/' . $explode['2'];
			update_post_meta( $post->ID, 'ioptimizer_optimized', 'on' );
		} elseif ( $size == 'scaled' ) {
			$explode     = explode( '/', get_post_meta( $post->ID, '_wp_attached_file', true ) );
			$destination = wp_get_upload_dir()['basedir'] . '/' . $explode[0] . '/' . $explode[1] . '/' . $explode['2'];
		} else {
			$meta = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
			if ( isset( $meta['sizes'][ $size ] ) ) {
				$explode     = explode( '/', get_post_meta( $post->ID, '_wp_attached_file', true ) );
				$destination = wp_get_upload_dir()['basedir'] . '/' . $explode[0] . '/' . $explode[1] . '/' . $meta['sizes'][ base64_decode( $request['size'] ) ]['file'];
			}
		}
		copy( base64_decode( $request['path'] ), $destination );


		return new WP_REST_Response( 'OK' );
	}

	public function api_complet_process( WP_REST_Request $request ) {
		$post = get_post( sanitize_text_field( $request['id'] ) );
		if ( ! $post || $request['token'] != get_post_meta( $post->ID, 'ioptimizer_token', true ) ) {
			$response = new  WP_REST_Response( 'post not Found' );
			$response->set_status( 404 );

			return $response;
		}

		add_post_meta( $post->ID, 'ioptimizer_compressed_size', filesize( get_attached_file( $post->ID ) ) );
		update_post_meta( $post->ID, 'ioptimizer_status', 3 );

		return ( new WP_REST_Response( 'OK' ) );
	}
}
