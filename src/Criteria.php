<?php
class Criteria {
	public $select = '*';
	public $from = '';
	public $where = array();
	public $order = '';
	public $insertUpdate = '';
	public $having = array();
	public $binding = array();
	public $groupBy = '';
	public $insertSelect = '';
	public $join = array();
	public $limit = '';
	public $counter = 0;
	public $found_rows = false;
}