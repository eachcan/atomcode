<?php
namespace atomcode;

class RouteCli extends Route {
	public function __construct() {
		global $argv;
		parent::__construct();

		$this->parsePath($argv[1]);
		
		if (count($argv) > 2) {
			$gets = array_slice($argv, 2);
			
			foreach ($gets as $get) {
				$p = explode('=', $get, 2);
				if (count($p) == 1) {
					$_GET[$p[0]] = "";
				} else {
					$_GET[$p[0]] = $p[1];
				}
			}
		}
	}
}