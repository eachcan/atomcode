<?php
class View {
	public static function render($render, $view, $data) {
		$t = null;
		$class = "Render" . ucfirst($render);
		if (class_exists($class)) {
			$t = new $class;
		}
		
		if ($t && $t instanceof Renderer) {
			$t->render($view, $data);
		}
	}
}

abstract class Renderer {
	public abstract function render($_____________________view, $data);	
}

class RenderHtml extends Renderer {
	public function render($_____________________view, $data) {
		extract($data);
		include AtomCode::$config['view']['dir'] . DIRECTORY_SEPARATOR . $_____________________view . AtomCode::$config['view']['ext'];
	}
}

class RenderJson extends Renderer {
	public function render($_____________________view, $data) {
		echo Json::encode($data);
	}
}