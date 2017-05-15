<?php
class Json {

	private static $pos = 0;

	private static $len;

	private static $token, $tokenValue;
	
	const EOF = 0;
	const DATUM = 1;
	const LBRACE = 2;
	const LBRACKET = 3;
	const RBRACE = 4;
	const RBRACKET = 5;
	const COMMA = 6;
	const COLON = 7;

	private static $error;

	public static function lastError() {
		return self::$error;
	}

	public static function encode($arr) {
		if (is_bool($arr)) {
			return $arr ? "true" : "false";
		}
		if (is_int($arr) || is_float($arr)) {
			return floatval($arr);
		}
		if (is_null($arr)) {
			return '""';
		}
		if (is_string($arr)) {
			return '"' . str_replace(array('\\', '"', "\n", "\r", "\t"), array('\\\\', '\\"', '\n', '\r', '\t'), $arr) . '"';
		}
		if (is_object($arr)) {
			$arr = get_object_vars($arr);
		}
		
		$c = 0;
		$is_assoc = FALSE;
		foreach ($arr as $k => $v) {
			if ($k !== $c) {
				$is_assoc = TRUE;
				break;
			}
			
			$c++;
		}
		
		if ($is_assoc) {
			$a = array();
			foreach ($arr as $k => $v) {
				$a[] = '"' . str_replace(array('\\', '"', "\n", "\r", "\t"), array('\\\\', '\\"', '\n', "\r", "\t"), $k) . '":' . self::encode($v);
			}
			return '{' . implode(',', $a) . '}';
		} else {
			$a = array();
			foreach ($arr as $v) {
				$a[] = self::encode($v);
			}
			return '[' . implode(',', $a) . ']';
		}
	}

	public static function decode($str) {
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $json->decode($str);
	}
}