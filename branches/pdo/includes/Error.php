<?php
// $Id$

require_once("includes/Engine.php");


class Error extends Engine {

	private $_error_code;

	function __construct($action, $code) {
		parent::__construct("error");
		if ($this->valid()) {
			$this->_main_template = "error/index.tpl";
			$this->_error_code = $code;
			$this->_template = $code . ".tpl";
		}
	}

	function supported($action) {
		return true;
	}

	function call() {
		parent::call();
	}

	protected function _error() {
	}
}

?>
