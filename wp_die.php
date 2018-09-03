<?php
/**
 * Adds the `wp_die` function found in WordPress.
 * @version 1.0.0
 */

if ( ! function_exists( 'wp_die' ) ) {
	/**
	 * Kill execution and display HTML message with error message.
	 *
	 * This function complements the `die()` PHP function. The difference is that
	 * HTML will be displayed to the user. It is recommended to use this function
	 * only when the execution should not continue any further. It is not recommended
	 * to call this function very often, and try to handle as many errors as possible
	 * silently or more gracefully.
	 *
	 * As a shorthand, the desired HTTP response code may be passed as an integer to
	 * the `$title` parameter (the default title would apply) or the `$args` parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param string|WP_Error  $message Optional. Error message. If this is a WP_Error object,
	 *                                  and not an Ajax or XML-RPC request, the error's messages are used.
	 *                                  Default empty.
	 * @param string|int       $title   Optional. Error title. If `$message` is a `WP_Error` object,
	 *                                  error data with the key 'title' may be used to specify the title.
	 *                                  If `$title` is an integer, then it is treated as the response
	 *                                  code. Default empty.
	 * @param string|array|int $args {
	 *     Optional. Arguments to control behavior. If `$args` is an integer, then it is treated
	 *     as the response code. Default empty array.
	 *
	 *     @type int    $response       The HTTP response code. Default 200 for Ajax requests, 500 otherwise.
	 *     @type bool   $back_link      Whether to include a link to go back. Default false.
	 * }
	 */
	function wp_die( $message = '', $title = 'Error', $args = array() ) {
		if ( is_int( $args ) ) {
			$args = array( 'response' => $args );
		} elseif ( is_int( $title ) ) {
			$args  = array( 'response' => $title );
			$title = 'Error';
		}

		$function = isset( $args['wp_die_handler'] ) ? $args['wp_die_handler'] : 'wp_die_handler';
		call_user_func( $function, $message, $title, $args );
	}
}

if ( ! function_exists( 'wp_die_handler' ) ) {
	/**
	 * Kills execution and display HTML message with error message.
	 *
	 * This is the default handler for wp_die if you want a custom one for your
	 * site then you can overload using the wp_die_handler argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string|WP_Error $message Error message or WP_Error object.
	 * @param string          $title   Optional. Error title. Default empty.
	 * @param string|array    $args    Optional. Arguments to control behavior. Default empty array.
	 */
	function wp_die_handler( $message, $title = 'Error', $args = array() ) {
		$defaults = array( 'response' => 500 );
		$r        = array_merge( $defaults, $args );

		if ( is_string( $message ) ) {
			$message = "<p>$message</p>";
		}

		if ( isset( $r['back_link'] ) && $r['back_link'] ) {
			$back_text = '&laquo; Back';
			$message  .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
		}

		status_header( $r['response'] );
		header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width">
	<meta name="robots" content="noindex,follow" />
	<title><?php echo $title; ?></title>
	<style type="text/css">
		html {
			background: #f1f1f1;
		}
		body {
			background: #fff;
			color: #444;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			max-width: 700px;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			box-shadow: 0 1px 3px rgba(0,0,0,0.13);
		}
		h1 {
			border-bottom: 1px solid #dadada;
			clear: both;
			color: #666;
			font-size: 24px;
			margin: 30px 0 0 0;
			padding: 0;
			padding-bottom: 7px;
		}
		#error-page {
			margin-top: 50px;
		}
		#error-page p {
			font-size: 14px;
			line-height: 1.5;
			margin: 25px 0 20px;
		}
		#error-page code {
			font-family: Consolas, Monaco, monospace;
		}
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #0073aa;
		}
		a:hover,
		a:active {
			color: #00a0d2;
		}
		a:focus {
			color: #124964;
			-webkit-box-shadow:
				0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, .8);
			box-shadow:
				0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, .8);
			outline: none;
		}
		.button {
			background: #f7f7f7;
			border: 1px solid #ccc;
			color: #555;
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 26px;
			height: 28px;
			margin: 0;
			padding: 0 10px 1px;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing:    border-box;
			box-sizing:         border-box;

			-webkit-box-shadow: 0 1px 0 #ccc;
			box-shadow: 0 1px 0 #ccc;
			 vertical-align: top;
		}

		.button.button-large {
			height: 30px;
			line-height: 28px;
			padding: 0 12px 2px;
		}

		.button:hover,
		.button:focus {
			background: #fafafa;
			border-color: #999;
			color: #23282d;
		}

		.button:focus  {
			border-color: #5b9dd9;
			-webkit-box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
			box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
			outline: none;
		}

		.button:active {
			background: #eee;
			border-color: #999;
			 -webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
			 box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
			 -webkit-transform: translateY(1px);
			 -ms-transform: translateY(1px);
			 transform: translateY(1px);
		}
	</style>
</head>
<body id="error-page">
	<?php echo $message; ?>
</body>
</html>
<?php
		die();
	}
}

if ( ! function_exists( 'status_header' ) ) {
	/**
	 * Set HTTP status header.
	 *
	 * @since 1.0.0
	 *
	 * @see get_status_header_desc()
	 *
	 * @param int    $code        HTTP status code.
	 * @param string $description Optional. A custom description for the HTTP status.
	 */
	function status_header( $code, $description = '' ) {
		if ( ! $description ) {
			$description = get_status_header_desc( $code );
		}

		if ( empty( $description ) ) {
			return;
		}

		$protocol = $_SERVER['SERVER_PROTOCOL'];
		if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ) ) ) {
			$protocol = 'HTTP/1.0';
		}

		@header( "$protocol $code $description", true, $code );
	}
}

if ( ! function_exists( 'get_status_header_desc' ) ) {
	/**
	 * Retrieve the description for the HTTP status.
	 *
	 * @since 1.0.0
	 *
	 * @param int $code HTTP status code.
	 * @return string Empty string if not found, or description if found.
	 */
	function get_status_header_desc( $code ) {
		$header_descriptions = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			103 => 'Early Hints',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			421 => 'Misdirected Request',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			451 => 'Unavailable For Legal Reasons',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			510 => 'Not Extended',
			511 => 'Network Authentication Required',
		);

		return isset( $header_descriptions[ $code ] ) ? $header_descriptions[ $code ] : '';
	}
}

