AtomCode
========
欢迎使用 AtomCode，这是一个适合中小型项目的框架。

安装
---
1. 通过 composer 安装
```shell
composer require "atomcode/atomcode"
```

2. 直接下载最新安装包，参考下方的目录结构

### 目录结构

总共需要有三套目录： 框架的目录， 你自己的程序目录，你的入口文件和图片之前Web资源目录。分别如下：

	| atomcode  # 框架放这里, 如果是 composer 安装则不需要此目录
	| vendor    # 这是 composer 的目录，如果是下载安装则没有此目录
	| app		# 这个名字无所谓
	| public	# 这个名字更无所谓，作为 Web 服务器的根目录

atomcode 目录你不需要关心，只需要找个地方放就行了。

app 目录结构：
```shell
|- config
  |- config.php
|- controller
|- model
|- view
```

public 目录下，需要放置一个入口文件，内容类似于:
```PHP
<?php
require '../vendor/autoload.php'; # composer 安装模式
// require '../atomcode/src/Core.php'; # 下载安装模式

define('WWW_PATH', __DIR__);
define('APP_PATH', realpath(__DIR__ . "/../app")); # 必须定义这一常量
define('CURRENT_TIME', time());

if (file_exists(WWW_PATH . '/../../.dev')) {
    define('ENVIRONMENT', 'dev'); # 不定义此常量，将认为是线上环境
    error_reporting(E_ALL & ~E_NOTICE);
}

chdir(WWW_PATH);
date_default_timezone_set("asia/shanghai"); # 这些基础设置，请在入口文件里都设置了

// AtomCode::registerAutoloadDir("/data/lib/php/phpword"); 注册自动加载要寻找的目录
AtomCode::start();
```

### 快速体验

我们把三个目录放好，把web服务器目录指向到public目录。然后开始写一个 controller
``` php
<?php
class IndexController extends Controller {
	public function indexAction() {
		echo "hello world";
	}
}
```
开始访问：

http://example.com/index.php?_url=index/index
