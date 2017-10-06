<?php
class View {

	public static function render($render, $view, $data) {
		$t = null;
		if (AtomCode::$config['view']['render_alias'][$render]) {
            $class = AtomCode::$config['view']['render_alias'][$render];
        } else {
            $class = ucfirst($render) . "Render";
        }

		if (class_exists($class)) {
			$t = new $class();
		}
		
		if ($t && $t instanceof Renderer) {
			if (!is_cli()) {
				header('Render: ' . $render);
			}
			$t->render($view, $data);
		}
	}
}
abstract class Renderer {

	public abstract function render($_____________________view, $data);
}
class HtmlRender extends Renderer {

	public function render($_____________________view, $data) {
		extract($data);
		$file = AtomCode::$config['view']['dir'] . DIRECTORY_SEPARATOR . $_____________________view . AtomCode::$config['view']['ext'];
		if (file_exists($file)) {
			include $file;
		}
	}
}
class JsonRender extends Renderer {

	public function render($_____________________view, $data) {
		if ($_REQUEST['jsonp']) {
			header('Content-Type: application/jsonp');
			echo $_REQUEST['jsonp'] . '(' . Json::encode($data) . ')';
		} else {
			header('Content-Type: application/json');
			echo Json::encode($data);
		}
	}
}
class YamlRender extends Renderer {

	public function render($_____________________view, $data) {
		echo Spyc::YAMLDump($data);
	}
}