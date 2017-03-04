<?php
function fileext($filename) {
	return trim(substr(strrchr($filename, '.'), 1, 10));
}

function random($length, $numeric = 0) {
	mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, true) . microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$hash .= $seed{$rand};
	}
	return $hash;
}

function rhtmlspecialchars($string) {
	$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	return $string;
}
function log_err($msg = '', $tag = '') {
	if (atomcode\Core::$config['log']) {
		if ($tag) $tag .= '-';
		$file = atomcode\Core::$config['logdir'] . '/' . $tag . date("Y-m-d") . '.log';
		if (!file_exists($file)) {
			touch($file);
			chmod($file, 0777);
		}
		file_put_contents($file, '[' . date("Y-m-d H:i:s") . '] [' . atomcode\Core::$route->getModuleDir() . atomcode\Core::$route->getController() . '->' . atomcode\Core::$route->getAction() . "] " . $msg . "\r\n", FILE_APPEND);
	}
}

function _error_handler($level, $error, $file = '', $line = 0, $context = array()) {
	if (in_array($level, array(E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE, E_RECOVERABLE_ERROR, E_STRICT, E_USER_ERROR))) {
		log_err("$error in $file line $line. " . ($context ? print_r($context, true) : ""));
	}
}

/**
 * @param Exception $e
 */
function _exception_handler($e) {
	log_err($e->getTraceAsString());
}

function get_ip() {
	if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}

	if ($onlineip == '::1')
		$onlineip = '127.0.0.1';

	preg_match('/[\d\.]{7,15}/', $onlineip, $onlineipmatches);
	$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	return $onlineip;
}

function path_join() {
	$args = func_get_args();
	$has = "";
	if ($args[0] && $args[0]{0} == '/') {
		$has = '/';
	}

	foreach ($args as &$arg) {
		$arg = trim($arg, '/\\');
	}

	return $has . implode("/", $args);
}

function startswith($string, $sub) {
	return strncmp($string, $sub, min(strlen($sub), strlen($string))) === 0;
}

function rrmdir($dir) {
	foreach (glob($dir . '/*') as $file) {
		if (is_dir($file))
			rrmdir($file);
		else
			unlink($file);
	}
	rmdir($dir);
}

function is_cli() {
	return defined("STDIN");
}

function is_ajax() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && $_GET['jsonp']);
}

function is_post() {
	return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
}

function is_get() {
	return strtolower($_SERVER['REQUEST_METHOD']) == 'get';
}

function is_https() {
	return $_SERVER['SERVER_PORT'] == 443;
}
function get_device_type(){
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$type = 'other';
	if(strpos($agent, 'iphone')){
		$type = 'iphone';
	}
	if(strpos($agent, 'ipad')){
		$type = 'ipad';
	}
	if(strpos($agent, 'android')){
		$type = 'android';
	}
	return $type;
}

function is_android() {
	return get_device_type() == "android";
}

function is_iphone() {
	return get_device_type() == "iphone";
}

function is_ipad() {
	return get_device_type() == "ipad";
}

function is_ios() {
	return in_array(get_device_type(), array("ipad", "iphone"));
}

function is_mobile() {
	return in_array(get_device_type(), array("ipad", "iphone", "android"));
}

function url($action) {
	return rtrim(atomcode\Core::$config['route']['base'], ' /') . ltrim($action, '/ ');
}
