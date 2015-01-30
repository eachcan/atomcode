<?php
abstract class Model implements ArrayAccess {
	/**
	 * @var Criteria
	 */
	private $_criteria;
	
	public $_database = 'default';
	public $_table = '';
	public $_config;
	protected $_last_query = "";
	/**
	 * @var Database
	 */
	protected $_db;
	
	protected $_primary = "id";
	
	public function __construct() {
		$this->_criteria = new Criteria();
		$this->_table = $this->getTableName();
		$this->_config = &AtomCode::$config['db'][$this->_database];
		$this->_db = Database::get($this->_database);
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->$offset = $value;
		} else {
			$this->$offset = $value;
		}
	}
	public function offsetExists($offset) {
		return isset($this->$offset);
	}
	public function offsetUnset($offset) {
		unset($this->$offset);
	}
	public function offsetGet($offset) {
		return isset($this->$offset) ? $this->$offset : null;
	}
	
	public function getTableName(){
		$class = get_class($this);
		return strtolower(substr($class, 0, -5));
	}
	
	public function getUsingTable() {
		return $this->_config['dbprefix'] . $this->_table;
	}
	
	public function select($columns = '*') {
		$this->_criteria->select = $columns;
	}
	
	public function from($_table) {
		$this->_criteria = $_table;
	}
	
	public function where($where, $bind = array()) {
		if (is_array($where)) {
			foreach ($where as $col => $val) {
				$this->where("$col = :$col");
			}
			
			$this->bind($where);
		} else {
			$this->_criteria->where[] = $where;
			
			if ($bind) {
				$this->bind($bind);
			}
		}
	}
	
	public function orderBy($order) {
		$this->_criteria->order = $order;
	}
	
	public function having($having) {
		$this->_criteria->having = $having;
	}
	
	public function groupBy($group) {
		$this->_criteria->group = $group;
	}
	
	public function limit($limit, $offset = null) {
		$this->_criteria->limit = ($offset ? $offset . ',' : '') . $limit;
	}
	
	public function data($key, $val = false) {
		if (is_array($key)) {
			$this->_criteria->data = array_merge($this->_criteria->data, $key);
		} else {
			$this->_criteria->data[$key] = $val;
		}
	}
	
	public function find() {
		$this->_last_query = $this->buildSelectSql();
		$result = $this->_db->queryArray($this->_last_query, $this->_criteria->binding);
		
		return $result;
	}
	
	public function insertUpdate($data) {
		$this->_last_query = $this->buildInsertUpdateSql($data);
		echo $this->_last_query;
		$result = $this->_db->queryArray($this->_last_query, $this->_criteria->binding);
		return $result;
	}
	
	public function insertSelect($insertSelect) {
		$this->_criteria->insertSelect = $insertSelect;
	}
	/**
	 * @todo impl
	 * @param unknown $array
	 * @return Ambigous <boolean, PDOStatement>
	 */
	public function insertBatch($array) {
		$this->_last_query = $this->buildInsertBatchSql($data ? $data : $this->_criteria->data, $ignore);
		echo $this->_last_query;
		$result = $this->_db->query($this->_last_query, $this->_criteria->binding);
		
		return $result;
		
	}
	
	public function join($str) {
		$this->_criteria->join = $str;
	}
	
	public function get($id) {
		$this->where($this->_primary . ' = :' . $this->_primary, array($this->_primary => $id));
		$rows = $this->find();
		
		$m = null;
		if ($rows[0]) {
			$c = get_class($this);
			$m = new $c();
			foreach ($rows[0] as $k => $v) {
				$m->{$k} = $v;
			} 
		}
		return $m;
	}
	
	public function insert($data = array(), $ignore = false) {
		$this->_last_query = $this->buildInsertSql($data ? $data : $this->_criteria->data, $ignore);
		$result = $this->_db->query($this->_last_query, $this->_criteria->binding);
		
		return $result;
	}
	
	public function update($data = array()) {
		$this->_last_query = $this->buildUpdateSql($data ? $data : $this->_criteria->data);
		$result = $this->_db->query($this->_last_query, $this->_criteria->binding);
		
		return $result;
		
	}
	
	public function save() {
		$data = $this->value();
		$this->data($data);
		return $this->insertUpdate($data);
	}
	
	public function saveUpdate($data = array()) {
		
	}
	
	public function bind($key, $value = null) {
		if (is_array($key)) {
			$this->_criteria->binding = array_merge($this->_criteria->binding, $key);
		} else {
			$this->_criteria->binding[$key] = $value;
		}
		
	}
	
	public function buildSelectSql() {
		return $this->partSelectSql() . $this->partFromSql() . $this->partJoinSql()
		 . $this->partWhereSql() . $this->partGroupSql() . $this->partHavingSql()
		 . $this->partOrderSql() . $this->partLimitSql();
	}
	
	public function buildInsertUpdateSql($data) {
		$cols = array_keys($this->_criteria->data);
		
		return $this->buildInsertSql($this->_criteria->data) . ' ON DUPLICATE KEY UPDATE ' . $this->partUpdateSql($data);
	}
	
	public function buildInsertSql($data, $ignore = false) {
		foreach ($data as $k => $v) {
			if (is_null($v)) {
				unset($data[$k]);
			}
		}
		$cols = array_keys($data);
		
		return 'INSERT' . ($ignore ? ' IGNORE' : '') . ' INTO ' . $this->getUsingTable() . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $this->quote($data)) . ')';
	}
	
	public function buildUpdateSql($data) {
		$data = $data ? $data : $this->_criteria->data;
		
		return 'UPDATE ' . $this->getUsingTable() . ' SET ' . $this->partUpdateSql($data) . $this->partWhereSql() . $this->partOrderSql() . $this->partLimitSql();
	}
	
	private function partSelectSql() {
		return 'SELECT ' . ($this->_criteria->select ? $this->_criteria->select : implode(', ', $this->getTableColumns()));
	}
	
	private function partFromSql() {
		return ' FROM ' . $this->getUsingTable();
	}
	
	private function partJoinSql() {
		if ($this->_criteria->join) {
			return ' JOIN ' . $this->_criteria->join;
		} else {
			return '';
		}
	}
	
	private function partWhereSql() {
		if ($this->_criteria->where) {
			return ' WHERE ' . implode(' AND ', $this->_criteria->where);
		} else {
			return '';
		}
	}
	
	private function partGroupSql() {
		if ($this->_criteria->groupBy) {
			return ' GROUP BY ' . $this->_criteria->groupBy;
		} else {
			return '';
		}
	}
	
	private function partHavingSql() {
		if ($this->_criteria->having) {
			return ' HAVING ' . $this->_criteria->having;
		} else {
			return '';
		}
	}
	
	private function partOrderSql() {
		if ($this->_criteria->order) {
			return ' ORDER BY ' . $this->_criteria->order;
		} else {
			return '';
		}
	}
	
	private function partLimitSql() {
		if ($this->_criteria->limit) {
			return ' LIMIT ' . $this->_criteria->limit;
		} else {
			return '';
		}
	}
	
	private function partUpdateSql($data) {
		$items = array();
		foreach ($data as $k => $v) {
			if (is_null($v)) continue;
			
			$items[] = $k . '=' . ($v == '?' ? $v : $this->quote($v));
		}
		
		return implode(', ', $items);
	}
	
	public function quote($data) {
		if (is_array($data)) {
			foreach ($data as &$d) {
				$d = addslashes($d);
				$d = "'$d'";
			}
		} else {
			$data = addslashes($data);
			$data = "'$data'";
		}
		
		return $data;
	}
	
	public function value() {
		$c = array();
		$ps = get_object_vars($this);
		foreach ($ps as $n => $_) {
			if ($n{0} != '_') {
				$c[$n] = $_;
			}
		}

		return $c;
	}
	
	protected function getTableColumns() {
		return array_keys($this->value());
	}
}