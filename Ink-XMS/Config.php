<?php
error_reporting(-1);
ini_set('display_errors', 1);
set_error_handler('InkXMS_Config::handleError');

class InkXMS_Config {
	public static $space;

	public static function init($space) {
		self::$space = $space;
	}

	public static function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
		// error was suppressed with the @-operator
		if(0 === error_reporting()) {
			return false;
		}

		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}