<?php
class Route {
	public static $config;
	private static $module = "";
	private static $controller = "";
	private static $action = "";
	
	public static function init() {
		self::$config = AtomCode::$config['route'];
		self::$module = self::$config['default_module'];
		self::$controller = self::$config['default_controller'];
		self::$action = self::$config['default_action'];
		
		$path = $_REQUEST[self::$config['url_param'] ? self::$config['url_param'] : '_url'];
		$path = trim($path, ' /');
		$ps = explode('/', $path);
		if (count($ps) >= 2) {
			self::$action = array_pop($ps);
			self::$controller = array_pop($ps);
			if ($ps) {
				self::$module = implode('/', $ps);
			}
		} elseif ($path) {
			self::$controller = $path;
		}
	}
	
	public static function getModule() {
		return self::$module;
	}
	
	public static function getModuleDir() {
		return self::$module ? self::$module . DIRECTORY_SEPARATOR : "";
	}
	
	public static function getController() {
		return self::$controller;
	}
	
	public static function getAction() {
		return self::$action;
	}
	
	public static function getControllerClass() {
		return str_replace(" ", '', ucwords(str_replace(array('-', '_'), '', self::$controller))) . 'Controller';
	}
	
	public static function getControllerFile() {
		return self::getModuleDir() . self::getControllerClass() . '.php';
	}
	
	public static function getActionName() {
		return ucfirst(self::getAction()) . 'Action';
	}
	
	public static function setAction($ac) {
		self::$action = $ac;
	}
}