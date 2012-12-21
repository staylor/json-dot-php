## JSON dot PHP
Contributors: wonderboymusic
Tags: json, api, external, data
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.0

Because XML-RPC sucks, and WordPress is backwards-compatible.

## Description

Brings WordPress into the future by allowing an easy way to expose your data as
JSON to the outside world. All you do is filter an array of `method_name => method`

If you have previously exposed methods via XML-RPC, they will work out of the box here.

Example call:

`http://www.pizza.com/json.php?method=order&topping=pepperoni`

Example method registration:


    function order() {
        do_something( $_GET['topping'] );
    }

    function add_methods( $methods ) {
	    $methods['order'] = 'order';

	    return $methods;
    }
    add_filter( 'json_methods', 'add_methods' );

Back-compat for XML-RPC:

    /**
     * Any params not named 'method' are passed in order with a numerical index
     */

    /*
     * One param:
     * http://www.pizza.com/json.php?method=order&topping=pepperoni
     */

    function order() {
        $args = func_get_args();
        // $args = array( 0 => 'pepperoni' )
    }

    /*
     * Multiple params:
     * http://www.pizza.com/json.php?method=order&topping=pepperoni&sauce=extra
     */

    function order() {
        $args = func_get_args();
        // $args = array( 0 => array( 0 => 'pepperoni', 1 => 'extra' )
    }


You many think the above is bizarre, guess what? It is! That's why I don't like XML-RPC. Too
weird, too old, too embarassing to ask other people to use.


## Installation

Move json.php wherever you want it to receive requests. The same directory as WordPress is ideal,
or in your site's root if you have .htaccess set up to look for WordPress in a subdirectory. Like this:

`RewriteRule  ^([_0-9a-zA-Z-]+/)?(.*\.php)$ /wordpress/$2 [L]`

Your PHP needs to be able to run the `json_encode()` function, that's it.

## Changelog

### 1.0
* Initial release
