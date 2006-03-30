<?php
// $Id$

require_once("includes/Engine.php");


class Error extends Engine {

	function __construct($action) {
		parent::__construct("error");
	}
	
	function supported($action) {
		return true;
	}

	function call() {
		parent::call();
	}

	protected function _error() {
		$this->_template = "404.tpl";
	}
}

?>
