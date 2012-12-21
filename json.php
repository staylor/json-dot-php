<?php
/**
 * JSON API support for WordPress
 *
 * @package WordPress
 *
 * @author Scott Taylor
 */

/*
Plugin Name: JSON dot PHP
Plugin URI: http://emusic.com
Description: Because XML-RPC sucks, and WordPress is backwards-compatible
Author: Scott Taylor
Author URI: http://scotty-t.com
Version: 1.0
*/

if ( ! defined( 'WP_CACHE' ) )
	define( 'WP_CACHE', false );

$protocol = $_SERVER["SERVER_PROTOCOL"];
if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
	$protocol = 'HTTP/1.0';

$params = $_GET + $_POST;

if ( empty( $params ) || empty( $params['method'] ) ) {
	header( "$protocol 400 Bad Request" );
	exit();
}

/**
 * Let's guess where WordPress is!
 */
if ( file_exists( 'wp-load.php' ) )
	require_once( './wordpress/wp-load.php' );
elseif ( file_exists( 'wordpress/wp-load.php' ) )
	require_once( './wordpress/wp-load.php' );

include_once( ABSPATH . 'wp-admin/includes/admin.php' );

$headers = headers_list();
if ( ! empty( $headers ) )
	header_remove( 'Set-Cookie' );

$_COOKIE = array();

$server_params = $params;
$method = $server_params['method'];

if ( defined( 'XMLRPC_USES_JSON' ) && XMLRPC_USES_JSON ):

	/**
	 * Load this bad boy to check the methods it contains
	 *
	 */
	$wp_xmlrpc_server_class = apply_filters( 'wp_xmlrpc_server_class', 'wp_xmlrpc_server' );
	$wp_xmlrpc_server = new $wp_xmlrpc_server_class;

	if ( array_key_exists( $method, $wp_xmlrpc_server->methods ) ):

		unset( $server_params['method'] );

		/**
		 * XML-RPC callbacks expect a weirdly-arranged
		 * set of parameters
		 *
		 */
		$params = null;
		if ( ! empty( $server_params ) ) {
			if ( 1 === count( $server_params ) )
				$params = reset( $server_params );
			else
				$params = array_values( $server_params );
		}

		$unthis = str_replace( 'this:', '', $wp_xmlrpc_server->methods[$method] );

		if ( $params )
			$response = call_user_func( array( $wp_xmlrpc_server, $unthis ), $params );
		else
			$response = call_user_func( array( $wp_xmlrpc_server, $unthis ) );

		header( 'Connection: close' );
		header( 'Content-Type: application/json' );
		header( 'Date: ' . date( 'r' ) );

		echo json_encode( $response );
		exit();

	endif;

endif;

/**
 * Example callback methods.
 * Return whatever you want, it'll get passed to json_encode
 *
 * @return mixed
 */
function json_hello() {
	return array( 'hello' => 'world' );
}

function json_hello_object() {
	$obj = new stdClass;
	$obj->name = 'wonderboymusic';
	return $obj;
}

function json_hello_int() {
	return 33;
}

function json_hello_string() {
	return 'I haz text only.';
}

$methods = apply_filters( 'json_methods', array(
	'hello.world' => 'json_hello',
	'hello.object' => 'json_hello_object',
	'hello.int' => 'json_hello_int',
	'hello.string' => 'json_hello_string',
) );

/**
 * You're a big boy now, you can handle $_GET, $_POST, whatever on your own
 */
if ( ! array_key_exists( $method, $methods ) ) {
	header( "$protocol 405 Method Not Allowed" );
	exit();
}

$response = call_user_func( $methods[$method] );

header( 'Connection: close' );
header( 'Content-Type: application/json' );
header( 'Date: ' . date( 'r' ) );

echo json_encode( $response );
exit();