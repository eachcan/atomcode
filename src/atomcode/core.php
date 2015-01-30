<?php
define('SYS_PATH', __DIR__);

include SYS_PATH . '/libs/functions.php';
include SYS_PATH . '/libs/Model.php';

class AtomCode {
	public static $config = array();
	public static $auto_load_config = array();
	
	public static function start() {
		if (ENVIRONMENT == "development") {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		} else {
			error_reporting(0);
		}
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		if (isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
			exit('Request tainting attempted.');
		}

		if (get_magic_quotes_gpc()) {
			$_GET = rstripslashes($_GET);
			$_POST = rstripslashes($_POST);
			$_COOKIE = rstripslashes($_COOKIE);
			$_REQUEST = rstripslashes($_REQUEST);
		}
		
		self::addConfig("config", false);
		foreach (self::$auto_load_config as $file) {
			self::addConfig($file, false);
		}
		
		self::registerAutoloadDir(SYS_PATH . DIRECTORY_SEPARATOR . 'libs');
		self::registerAutoload();
		
		Route::init();
		$controller_file = APP_PATH . '/controller/' . Route::getControllerFile();
		include $controller_file;
		$controller_class = Route::getControllerClass();
		$controller = new $controller_class();
		$controller->config = &self::$config;
		$action = Route::getActionName();
		$result = $controller->$action();
		if (!$result) {
			$result = $controller->getData();
		}
		
		if (!$controller->isDisabledRender()) {
			$view = $controller->getView();
			if (!$view) {
				$view  = Route::getModuleDir() . Route::getController() . DIRECTORY_SEPARATOR . Route::getAction();
			}
			
			$render = $controller->getRender();
			if (!$render) {
				$render = self::decideRender();
			}
			
			View::render($render, $view, $result);
		}
	}
	
	public static function addConfig($file, $prior = true) {
		$config = array();
		$dir = ENVIRONMENT ? ENVIRONMENT . '/' : '';
		
		if ($file{0} == '/' || $file{1} == ":") {
			include $file;
		} elseif ($file{0} == '.') {
			include APP_PATH . '/' . $file;
		} else {
			if (file_exists(APP_PATH . '/config/' . $dir . $file . '.php')) {
				include APP_PATH . '/config/' . $dir . $file . '.php';
			} else {
				include APP_PATH . '/config/' . $file . '.php';
			}
		}
		
		if ($prior) {
			self::$config = array_merge(self::$config, $config);
		} else {
			self::$config = array_merge($config, self::$config);
		}
	}
	
	public static function registerAutoload() {
		spl_autoload_register("__atomcode_autoload");
	}
	
	public static function registerAutoloadDir($dir) {
		set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
	}
	
	public static function registerAutoloadDirs($dirs) {
		set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $dirs));
	}
	
	public static function decideRender() {
		return is_ajax() ? 'json' : 'html';
	}
}

function __atomcode_autoload($class) {
	if (substr($class, -5) == 'Model') {
		include APP_PATH . '/model/' . $class . '.php';
	} else {
		include $class . '.php';
	}
}