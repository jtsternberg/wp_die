<?php
/**
 * Adds the `wp_die` function found in WordPress.
 * @version 1.0.1
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
		$wpdie = new WpDie( $message, $title, $args );
		$wpdie->execute();
	}
}

/**
 * Handles the WP Die functionality.
 * @since 1.0.1
 */
class WpDie {

	/**
	 * The message to display in the document.
	 *
	 * @var string
	 */
	public $message = '';

	/**
	 * The document title.
	 *
	 * @var string
	 */
	public $title = 'Error';

	/**
	 * The args for the view.
	 *
	 * @var array
	 */
	public $args = array(
		'wp_die_handler' => 'WpDie::output',
	);

	/**
	 * Descriptions for the various HTTP statii.
	 *
	 * @var array
	 */
	public static $headerDescriptions = array(
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

	/**
	 * The constructor.
	 *
	 * As a shorthand, the desired HTTP response code may be passed as an integer to
	 * the `$title` parameter (the default title would apply) or the `$args` parameter.
	 *
	 * @since 1.0.1
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
	public function __construct( $message = '', $title = 'Error', $args = array() ) {
		$this->message = $message;
		$this->title   = $title;
		$this->args    = array_merge( $this->args, (array) $args );
	}

	/**
	 * Kill execution and display HTML message with error message.
	 *
	 * This function complements the `die()` PHP function. The difference is that
	 * HTML will be displayed to the user. It is recommended to use this function
	 * only when the execution should not continue any further. It is not recommended
	 * to call this function very often, and try to handle as many errors as possible
	 * silently or more gracefully.
	 *
	 * @since  1.0.1
	 *
	 * @return void
	 */
	public function execute() {
		if ( is_int( $this->args ) ) {
			$this->args = array( 'response' => $this->args );
		} elseif ( is_int( $this->title ) ) {
			$this->args  = array( 'response' => $this->title );
			$this->title = 'Error';
		}

		call_user_func( $this->args['wp_die_handler'], $this->message, $this->title, $this->args );
	}

	/**
	 * Kills execution and display HTML message with error message.
	 *
	 * This is the default handler for wp_die if you want a custom one for your
	 * site then you can overload using the wp_die_handler argument.
	 *
	 * @since 1.0.1
	 *
	 * @param string|WP_Error $message Error message or WP_Error object.
	 * @param string          $title   Optional. Error title. Default empty.
	 * @param string|array    $args    Optional. Arguments to control behavior. Default empty array.
	 */
	public static function output( $message, $title = 'Error', $args = array() ) {
		$defaults = array( 'response' => 500 );
		$r        = array_merge( $defaults, $args );

		if ( is_string( $message ) ) {
			$message = "<p>$message</p>";
		}

		if ( isset( $r['back_link'] ) && $r['back_link'] ) {
			$back_text = '&laquo; Back';
			$message  .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
		}

		self::statusHeader( $r['response'] );
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


	/**
	 * Set HTTP status header.
	 *
	 * @since 1.0.1
	 *
	 * @see   WpDie::getStatusHeaderDesc()
	 *
	 * @param int    $code        HTTP status code.
	 * @param string $description Optional. A custom description for the HTTP status.
	 */
	public static function statusHeader( $code, $description = '' ) {
		if ( ! $description ) {
			$description = self::getStatusHeaderDesc( $code );
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


	/**
	 * Retrieve the description for the HTTP status.
	 *
	 * @since 1.0.1
	 *
	 * @param int $code HTTP status code.
	 * @return string Empty string if not found, or description if found.
	 */
	public static function getStatusHeaderDesc( $code ) {
		return isset( self::$headerDescriptions[ $code ] ) ? self::$headerDescriptions[ $code ] : '';
	}
}
