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
		$file = AtomCode::$config['view']['dir'] . DIRECTORY_SEPARATOR . $_____________________view . AtomCode::$config['view']['ext'];
		if (file_exists($file)) {
			include $file;
		}
	}
}

class RenderJson extends Renderer {
	public function render($_____________________view, $data) {
		echo Json::encode($data);
	}
}

class RenderYaml extends Renderer {
	public function render($_____________________view, $data) {
		spyc_dump($data);
	}
}