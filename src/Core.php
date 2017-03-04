<?php
namespace atomcode;

define('SYS_PATH', __DIR__);

include SYS_PATH . '/functions.php';
include SYS_PATH . '/Model.php';
class Core {

	public static $config = array();

	public static $session;

	public static $auto_load_config = array();

	/**
	 *
	 * @var Route
	 */
	public static $route;

	public static function start() {
		if (ENVIRONMENT == "production") {
			error_reporting(0);
			ini_set("display_errors", 0);
		}
		
		if (isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
			exit('Request tainting attempted.');
		}
		
		self::addConfig("config", false);
		foreach (self::$auto_load_config as $file) {
			self::addConfig($file, false);
		}
		
		self::registerAutoloadDir(SYS_PATH . DIRECTORY_SEPARATOR . 'libs');
		self::registerAutoloadDir(APP_PATH . DIRECTORY_SEPARATOR . 'model');
		self::registerAutoload();
		
		if (self::$config['session']['mode'] == 'db') {
			self::$session = new Session();
			self::assocSession();
		}
		
		if (defined('STDIN')) {
			self::$route = new RouteCli();
		} else {
			self::$route = new RouteUrl();
		}
		
		$controller_file = APP_PATH . '/controller/' . self::$route->getControllerFile();
		if (file_exists($controller_file))
			include $controller_file;
		
		$controller_class = self::$route->getControllerClass();
		if (!class_exists($controller_class, false)) {
			exit("Controller: $controller_class does not exist");
		}
		
		if (get_magic_quotes_gpc()) {
			$_GET = rstripslashes($_GET);
			$_POST = rstripslashes($_POST);
			$_COOKIE = rstripslashes($_COOKIE);
			$_REQUEST = rstripslashes($_REQUEST);
		}
		
		// why place the allow origin here? controller class may crash when running.
		if (self::$config['view']['ajax-origen'] && $_SERVER['HTTP_ORIGIN'] && preg_match(self::$config['view']['ajax-origen'], $_SERVER['HTTP_ORIGIN'])) {
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
		}
		
		$controller = new $controller_class();
		$controller->config = &self::$config;
		$action_str = self::$route->getAction();
		
		$src_action = $action_str;
		$action_str = $controller->_resolve($action_str);
		
		self::$route->setAction($action_str);
		$action = self::$route->getActionName();
		
		if (!method_exists($controller, $action)) {
			exit("Action: $controller_class does not have method `$action_str`" . ($action == $src_action ? "" : ", origen action is $src_action"));
		}
		$result = $controller->$action();
		if (!$result) {
			$result = $controller->getData();
		}
		
		if (!$controller->isDisabledRender()) {
			$view = $controller->getView();
			if (!$view) {
				$view = self::$route->getModuleDir() . self::$route->getController() . DIRECTORY_SEPARATOR . self::$route->getAction();
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
			return array();
		}
		
		if ($prior) {
			self::$config = array_merge(self::$config, $config);
		} else {
			self::$config = array_merge($config, self::$config);
		}
		
		return self::$config[$file];
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
		if (is_cli()) {
			return 'yaml';
		} elseif (is_ajax()) {
			return 'json';
		} else {
			return 'html';
		}
	}

	private static function assocSession() {
		session_set_save_handler(
			array(self::$session, 'open'), 
			array(self::$session, 'close'), 
			array(self::$session, 'read'), 
			array(self::$session, 'write'), 
			array(self::$session, 'destroy'), 
			array(self::$session, 'gc')
		);
	}
}

function __atomcode_autoload($class) {
	$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	
	include $class . '.php';
}
abstract class Route {

	public $config;

	protected $module = "";

	protected $controller = "";

	protected $action = "";

	protected $path = "";

	public function __construct() {
		$this->config = self::$config['route'];
		
		$this->module = $this->config['default_module'];
		$this->controller = $this->config['default_controller'];
		$this->action = $this->config['default_action'];
	}

	protected function parsePath($path) {
		$path = trim($path, ' /');
		$this->path = $path;
		$ps = explode('/', $path);
		
		if ($path) {
			if (file_exists(APP_PATH . '/controller/' . $path)) {
				$this->module = $path;
			} elseif (count($ps) > 1 && file_exists(APP_PATH . '/controller/' . dirname($path))) {
				$this->module = dirname($path);
				$this->controller = array_pop($ps);
			} elseif (count($ps) > 2 && file_exists(APP_PATH . '/controller/' . dirname(dirname($path)))) {
				$this->module = dirname(dirname($path));
				$this->action = array_pop($ps);
				$this->controller = array_pop($ps);
			} elseif (count($ps) <= 2) {
				if ($ps[0]) $this->controller = $ps[0];
				if ($ps[1]) $this->action = $ps[1];
			}
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
		return str_replace(" ", '', ucwords(str_replace(array('-', '_'), ' ', $this->controller))) . 'Controller';
	}

	public function getControllerFile() {
		return $this->getModuleDir() . $this->getControllerClass() . '.php';
	}

	public function getActionName() {
		return str_replace(" ", '', ucwords(str_replace(array('-', '_'), ' ', $this->action))). 'Action';
	}

	public function setAction($ac) {
		$this->action = $ac;
	}

	public function getPath() {
		return $this->path;
	}
}