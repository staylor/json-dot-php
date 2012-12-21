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

if ( empty( $_GET ) || empty( $_GET['method'] ) ) {
	header( "$protocol 400 Bad Request" );
	exit();
}

include_once( './wordpress/wp-load.php' );

$headers = headers_list();
if ( ! empty( $headers ) )
	header_remove( 'Set-Cookie' );

$_COOKIE = array();

$server_get = $_GET;
$method = $server_get['method'];

if ( defined( 'XMLRPC_USES_JSON' ) && XMLRPC_USES_JSON ):

	/**
	 * Load this bad boy to check the methods it contains
	 *
	 */
	$wp_xmlrpc_server_class = apply_filters( 'wp_xmlrpc_server_class', 'wp_xmlrpc_server' );
	$wp_xmlrpc_server = new $wp_xmlrpc_server_class;

	if ( array_key_exists( $method, $wp_xmlrpc_server->methods ) ):

		unset( $server_get['method'] );

		/**
		 * XML-RPC callbacks expect a weirdly-arranged
		 * set of parameters
		 *
		 */
		$params = null;
		if ( ! empty( $server_get ) ) {
			if ( 1 === count( $server_get ) )
				$params = reset( $server_get );
			else
				$params = array_values( $server_get );
		}

		if ( $params )
			$response = call_user_func( $wp_xmlrpc_server->methods[$method], $params );
		else
			$response = call_user_func( $wp_xmlrpc_server->methods[$method] );

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
	'hello.world'	=> 'json_hello',
	'hello.object'	=> 'json_hello_object',
	'hello.int'		=> 'json_hello_int',
	'hello.string'	=> 'json_hello_string',
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