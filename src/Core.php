<?php
define('SYS_PATH', __DIR__);

include SYS_PATH . '/functions.php';
include SYS_PATH . '/Model.php';
class AtomCode {

	public static $config = array();

    /**
     * @var Session
     */
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

        AtomCode::registerAutoloadDir(APP_PATH);
		
		self::addConfig("config", false);
		foreach (self::$auto_load_config as $file) {
			self::addConfig($file, false);
		}
		
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

		$controller_class = self::$route->getControllerClass();

		if (!class_exists($controller_class, true)) {
			exit("Controller: $controller_class does not exist");
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
				$view = self::$route->getController() . DIRECTORY_SEPARATOR . self::$route->getAction();
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
            if (file_exists(APP_PATH . '/config/' . $file . '.php')) {
                include APP_PATH . '/config/' . $file . '.php';
            }
			if ($dir && file_exists(APP_PATH . '/config/' . $dir . $file . '.php')) {
				include APP_PATH . '/config/' . $dir . $file . '.php';
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
	    if (AtomCode::$config['view']['renderer'] && is_callable(AtomCode::$config['view']['renderer'])) {
	        return call_user_func(AtomCode::$config['view']['renderer']);
        }
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
		AtomCode::addConfig('route');

        $this->config = AtomCode::$config['route'];

        $this->controller = $this->config['default_controller'];
		$this->action = $this->config['default_action'];
	}

	protected function parsePath($path) {
		$this->path = $this->resolve($path);
        $path = $this->path;

		if ($path) {
		    $long_route = $path . '/' . $this->config['default_controller'];
		    $long_class = $this->getControllerClass($long_route);
		    $long_path = $this->getControllerFile($long_class);
		    if ($this->config['default_controller'] && file_exists($long_path)) {
		        $this->controller = $long_route;
		        return ;
            }

		    $ps = explode('/', $path);

		    if (count($ps) == 1 || file_exists($this->getControllerFile($this->getControllerClass($path)))) {
		        $this->controller = $path;
            } elseif ($path2 = dirname($path)) {
                $this->controller = $path2;
                $this->action = substr($path, strlen($path2) + 1);
            }
		}
	}

    /**
     * @param $path 解析此 URL
     * @return string 最终 url
     */
    public function resolve($path) {
        $path = trim($path, ' /');
        if (strpos($path, '..') !== false) exit("DANGER!");

        if (is_array($this->config['replace']))foreach ($this->config['replace'] as $reg => $replace) {
            $path = preg_replace('/^' . $reg . '$/', $replace, $path);
        }

        return $path;
	}

	public function getController() {
		return $this->controller;
	}

	public function getControllerClass($name = '') {
        $ps = explode("/", $name ?: $this->controller);
        $class = array_pop($ps);
        $dir = $ps ? implode("\\", $ps) . '\\' : '';
		return 'controller\\' . $dir .  str_replace(' ','', ucwords(str_replace(array('-', '_'), ' ', $class))) . 'Controller';
	}

    public function getControllerFile($class) {
        return APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
	}

    public function getAction() {
        return $this->action;
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