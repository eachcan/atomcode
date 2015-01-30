<?php
class FieldException extends Exception {
	protected $field;

	public function __construct($field, $message, $code = 0) {
		parent::__construct($message, $code, null);

		$this->field = $field;
	}

	public function getField() {
		return $this->field;
	}
	
	public function __toString() {
		return "FieldException[$this->field][$this->getCode()]: $this->getMessage()\n";
	}
}