<?php
define('WWW_PATH', __DIR__);
define('APP_PATH', realpath(__DIR__ . "/../app"));
define('CURRENT_TIME', time());
if (file_exists(WWW_PATH . '/../../.dev')) {
	define('ENVIRONMENT', 'development');
} elseif (file_exists(WWW_PATH . '/../../.test')) {
	define('ENVIRONMENT', 'testing');
} else {
	define('ENVIRONMENT', 'production');
}

chdir(WWW_PATH);

include './../atomcode/core.php';

AtomCode::start();