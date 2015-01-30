<?php
class UserModel extends Model {
	public $id, $password, $mobile, $nickname, $reg_time, $login_times, $last_login_time, $reg_ip, $last_login_ip, $retry_times, $last_retry_time, $exp, $auto_login_times, $syncd;
	
	public function all() {
		$this->insert(array('id' => 1, "password" => "password"), 0);
	}
}