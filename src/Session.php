<?php
class Session {
	private static $exists = false;
	
	private static $user_id = 0;
	
	public function __construct() {
		session_name(AtomCode::$config['session']['key']);
		$id = $_POST[AtomCode::$config['session']['key']];
		if (!$id) {
			$id = $_GET[AtomCode::$config['session']['key']];
		}
		if (!$id) {
			$id = $_COOKIE[AtomCode::$config['session']['key']];
		}
		if ($id) {
			session_id($id);
		}
	}
	
	public static function setUser($user_id) {
		self::$user_id = intval($user_id);
	}
	
	public function open($save_path, $session_name) {
		return true;
	}

	public function close() {
		return true;
	}
	

	public function read($session_id) {
		$session = "";

		$db = Database::get(AtomCode::$config['session']['db']);
		$sql = "SELECT * FROM sessions WHERE session_id = :id";
		$results = $db->queryArray($sql, array('id' => $session_id));
		if ($results) {
			if ($results[0]['last_activity'] > time() - 86400 * 2) {
				$session = $results[0]['user_data'];
				self::$exists = true;
			} else {
				$this->destroy($session_id);
			}
		}
		
		return $session;
	}

	public function write($session_id, $data) {
		$data = str_replace("'", "\\'", $data);
		$time = time();
		$db = Database::get(AtomCode::$config['session']['db']);
		if (self::$exists) {
			$sql = "UPDATE sessions SET user_data= '$data', last_activity= '$time', user_id='" . self::$user_id . "' WHERE session_id = '$session_id'";
			$query = $db->bind($sql, array());
			$db->query($query);
		} else {
			$sql = "insert sessions(session_id, ip_address, user_agent, last_activity, user_data, user_id) VALUES ('$session_id', '" . get_ip() . "', '" . $_SERVER['HTTP_USER_AGENT'] . "', " . time() . ", '$data', '" . self::$user_id . "')";

			$result = $db->query($sql);
		}
		return true;
	}

	public function destroy($session_id) {
		$sql = "DELETE FROM sessions WHERE session_id = :id";
		
		self::$user_id = 0;
		
		$db = Database::get(AtomCode::$config['session']['db']);
		$db->query($sql, array('id' => $session_id));
		return true;
	}

	public function gc($lifetime) {
		$sql = "DELETE FROM sessions WHERE last_activity < :time";
		$db = Database::get(AtomCode::$config['session']['db']);
		$db->query($sql, array('time' => time() - 86400 * 2));
		return true;
	}

	public function createSid() {
		return md5(time() . mt_rand(1,3000000));
	}
}