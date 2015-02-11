<?php
abstract class Controller {
	private $__render = "", $__data = array(), $__view = "", $__disable_render = false;
	public $config;
	
	public function getRender() {
		return $this->__render;
	}
	
	protected function setRender($render) {
		$this->__render = $render;
	}
	
	protected function setVar($name, $value) {
		$this->__data[$name] = $value;
	}
	
	protected function setVars($arr) {
		$this->__data = array_merge($this->__data, $arr);
	}
	
	public function getData() {
		return $this->__data;
	}
	
	protected function setView($view) {
		$this->__view = $view;
	}
	
	public function getView() {
		return $this->__view;
	}
	
	protected function disableRender() {
		$this->__disable_render = true;
	}
	
	public function isDisabledRender() {
		return $this->__disable_render;
	}
	
	public function requireRoute($route) {
		if ($route == "cli" && !class_exists("RouteCli", false)) {
			exit("Command Line Required");
		} elseif ($route == "url" && !class_exists("RouteUrl", false)) {
			exit("Access From Web Browswer is Required");
		}
	}
	
	public function requireCli() {
		return $this->requireRoute('cli');
	}
	
	public function requireUrl() {
		return $this->requireRoute('url');
	}
}