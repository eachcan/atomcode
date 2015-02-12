Atomcode PHP 开发框架
========

这个框架一个新的版本完成了。之前也有一个同名的框架，也是我做的，但是规则仍然太复杂。所以重新做一个。

为什么要做这个框架呢？
--------
我感觉框架的规则都太多了。这个非常少。你要能接受就接受，不能接受这么简单，那还是要使用别的框架得了。

这个框架都有什么规则呢？
--------

1. URL规则仅支持一种，就是 `/user/login` 这一种，别无选择。当然，你非要写成 `index.php?_url=user/login` 也可以。。
2. 配置是全局的，你在任意地方都可以使用配置文件里的值。甚至修改都可以，没有任何限制
3. 仅包含 `MVC` 结构。`M` 映射数据库，实现了一般的简单的 `ORM`，支持有限，但也足够灵活；`V` 就是页面文件，里面直接写 `HTML + PHP`，但不要在里面做超出 `View` 角色该有的功能即可；`C` 不用说了，就是处理一下用户输入，然后处理出结果后，输出给 View 来显示。
4. 支持命令行模式，在程序里的 `$_GET` 可以捕获命令行的参数
5. 有一个叫 Render 的概念，也就是说你的程序可以有不同的格式输出。默认情况下： 网页访问自动使用 `HtmlRender` 输出，加载与 `controller` 同名的 `view`； Ajax 请求会使用 JsonRender； 命令行会使用 YamlRender。这些 Render 会按照自己的输出格式来处理 `Controller` 处理出来的值。你在 controller 里，有机会自己指定使用哪种 Render

### 目录结构

总共需要有三套目录： 框架的目录， 你自己的程序目录，你的入口文件和图片之前Web资源目录。分别如下：

	| atomcode
	
	| app		# 这个名字无所谓
	
	| public	# 这个名字更无所谓，但是 Web 服务器一定要访问到

atomcode 目录你不需要关心，只需要找个地方放就行了。

app 目录结构：

	|- config
	  |- config.php
	|- controller
	|- model
	|- view


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
OK, 开始访问吧：

http://example.com/index.php?_url=index/index
