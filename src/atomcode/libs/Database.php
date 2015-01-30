<?php
class Database {
	private static $links = array();
	private $link;
	
	private static $instances = array();
	
	/**
	 * 
	 * @param string $specify_db
	 * @return Database
	 */
	public static function &get($specify_db = 'default') {
		AtomCode::addConfig("database");
		$configs = AtomCode::$config['db'];
		
		if (!isset(self::$instances[$specify_db]) && is_array($configs[$specify_db])) {
			self::$instances[$specify_db] = new Database($specify_db, $configs[$specify_db]);
		}
		return self::$instances[$specify_db];
	}
	
	public function __construct($specify_db, $config) {
		$dsn = "mysql:host=$config[hostname];dbname=$config[database];port=3306;charset=".$config['char_set'];
		
		try {
			self::$links[$specify_db] = new PDO($dsn, $config['username'], $config['password']);
			$this->link = &self::$links[$specify_db];
			$stmt = $this->link->prepare('SET NAMES ?');
			if (!$stmt->execute(array($config['char_set']))) {
				log_err("unsupport charset: " . $config['char_set']);
				log_err("PDO: " . var_export($stmt->errorInfo(), true));
			}
		} catch (PDOException $e) {
			log_err("cannot connect to db: $dsn, user: $config[db_user], error: " . $e->getMessage());
		}
	}

	/**
	 * @return PDOStatement
	 */
	public function bind($sql, $binding) {
		$stmt = $this->link->prepare($sql);
		
		foreach ($binding as $k => $v) {
			$stmt->bindParam(':' . $k, $v);
		}
		
		return $stmt;
	}
	
	/**
	 * @param PDOStatement $stmt
	 * @return boolean
	 */
	public function query($stmt, $binding = array()) {
		if (is_string($stmt))
			$stmt = $this->bind($stmt, $binding);
	
		if (!$stmt->execute()) {
			log_err("query sql: " . $stmt->queryString);
			$error = $stmt->errorInfo();
			log_err("query error[$error[1]]: " . $error[2]);
			return false;
		}
	
		return $stmt;
	}
	
	/**
	 * @param PDOStatement|String $stmt
	 * @return boolean
	 */
	public function queryArray($stmt, $binding = array()) {
		$res = $this->query($stmt, $binding);
	
		if ($res) {
			return $res->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return array();
		}
	}

	function queryRow($sql) {
		$array = call_user_func_array(array($this, 'queryArray'), func_get_args());
		
		if (!$array) {
			return array();
		} else {
			return $array[0];
		}
	}
}