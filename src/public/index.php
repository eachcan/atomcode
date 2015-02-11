<?php
define('APP_PATH', realpath("../app"));
//define('WWW_PATH', __DIR__);
//define('CURRENT_TIME', time());
if (file_exists(WWW_PATH . '/../../.dev')) {
	//define('ENVIRONMENT', 'development');
} elseif (file_exists(WWW_PATH . '/../../.test')) {
	//define('ENVIRONMENT', 'testing');
} else {
	//define('ENVIRONMENT', 'production');
}
define('ENVIRONMENT', 'development');

include '../atomcode/core.php';

AtomCode::addConfig("database");
AtomCode::registerAutoloadDir(APP_PATH . '/library');

AtomCode::start();