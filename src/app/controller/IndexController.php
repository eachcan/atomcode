<?php
class IndexController extends Controller {
	public function indexAction() {
		error_reporting(E_ALL);
		$user = new UserModel();
		$user->where("id=:id");
		$user->data('password', md5('ddd'));
		$user->bind('id', 1);
		$user->update();
		
		//(new UserModel())->all();
	}
}