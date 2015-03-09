<?php
define('SYS_PATH', __DIR__);

include SYS_PATH . '/libs/functions.php';
include SYS_PATH . '/libs/Model.php';

class AtomCode {
	public static $config = array();
	public static $session;
	public static $auto_load_config = array();
	/**
	 * @var Route
	 */
	public static $route;
	
	public static function start() {
		if (ENVIRONMENT == "production") {
			error_reporting(0);
			ini_set("display_errors", 0);
		} else {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
			ini_set("display_errors", 1);
		}
		
		if (isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
			exit('Request tainting attempted.');
		}
		
		self::addConfig("config", false);
		foreach (self::$auto_load_config as $file) {
			self::addConfig($file, false);
		}
		
		self::registerAutoloadDir(SYS_PATH . DIRECTORY_SEPARATOR . 'libs');
		self::registerAutoload();
		
		if (AtomCode::$config['session']['mode'] == 'db') {
			self::$session = new Session();
			self::assocSession();
		}
		
		if (defined('STDIN')) {
			AtomCode::$route = new RouteCli();
		} else {
			AtomCode::$route = new RouteUrl();
		}
		
		$controller_file = APP_PATH . '/controller/' . AtomCode::$route->getControllerFile();
		if (file_exists($controller_file)) include $controller_file;
		
		$controller_class = AtomCode::$route->getControllerClass();
		if (!class_exists($controller_class, false)) {
			exit("Controller: $controller_class does not exist");
		}


		if (get_magic_quotes_gpc()) {
			$_GET = rstripslashes($_GET);
			$_POST = rstripslashes($_POST);
			$_COOKIE = rstripslashes($_COOKIE);
			$_REQUEST = rstripslashes($_REQUEST);
		}
		$controller = new $controller_class();
		$controller->config = &self::$config;
		$action = AtomCode::$route->getActionName();
		if (!method_exists($controller, $action)) {
			exit("Action: $controller_class does not have method `$action`");
		}
		$result = $controller->$action();
		if (!$result) {
			$result = $controller->getData();
		}
		
		if (!$controller->isDisabledRender()) {
			$view = $controller->getView();
			if (!$view) {
				$view  = AtomCode::$route->getModuleDir() . AtomCode::$route->getController() . DIRECTORY_SEPARATOR . AtomCode::$route->getAction();
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

		if (!$config || !is_array($config)) {
			return ;
		}
		
		if ($prior) {
			self::$config = array_merge(self::$config, $config);
		} else {
			self::$config = array_merge($config, self::$config);
		}
	}
	
	private static function registerAutoload() {
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
	
	private static function assocSession() {
		session_set_save_handler(
		    array(self::$session, 'open'),
		    array(self::$session, 'close'),
		    array(self::$session, 'read'),
		    array(self::$session, 'write'),
		    array(self::$session, 'destroy'),
		    array(self::$session, 'gc'));
	}
}

function __atomcode_autoload($class) {
	$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	
	if (substr($class, -5) == 'Model') {
		include APP_PATH . '/model/' . $class . '.php';
	} else {
		include $class . '.php';
	}
}

abstract class Route {
	public $config;
	protected $module = "";
	protected $controller = "";
	protected $action = "";
	protected $path = "";


	public function __construct() {
		$this->config = AtomCode::$config['route'];
	
		$this->module = $this->config['default_module'];
		$this->controller = $this->config['default_controller'];
		$this->action = $this->config['default_action'];
	}
	
	protected function parsePath($path) {
		$path = trim($path, ' /');
		$this->path = $path;
		$ps = explode('/', $path);
		if (count($ps) >= 2) {
			$this->action = array_pop($ps);
			$this->controller = array_pop($ps);
			if ($ps) {
				$this->module = implode('/', $ps);
			}
		} elseif ($path) {
			$this->controller = $path;
		}
	}
	
	public function getModule() {
		return $this->module;
	}
	
	public function getModuleDir() {
		return $this->module ? $this->module . DIRECTORY_SEPARATOR : "";
	}
	
	public function getController() {
		return $this->controller;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function getControllerClass() {
		return str_replace(" ", '', ucwords(str_replace(array('-', '_'), '', $this->controller))) . 'Controller';
	}
	
	public function getControllerFile() {
		return $this->getModuleDir() . $this->getControllerClass() . '.php';
	}
	
	public function getActionName() {
		return $this->getAction() . 'Action';
	}
	
	public function setAction($ac) {
		$this->action = $ac;
	}
	
	public function getPath() {
		return $this->path;
	}
}