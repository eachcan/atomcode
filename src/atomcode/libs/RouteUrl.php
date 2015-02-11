<?php
class RouteUrl extends Route {
	public function __construct() {
		parent::__construct();
		
		$path = $_REQUEST[$this->config['url_param'] ? $this->config['url_param'] : '_url'];
		$this->parsePath($path);
	}
}