<?php
// $Id$

require_once("includes/Session.php");
require_once("includes/Language.php");
require_once("includes/PhphSmarty.php");
require_once("includes/Photo.php");
require_once("includes/Category.php");
require_once("includes/User.php");
require_once("includes/Engine.php");
require_once("Mail.php");


class Admin extends Engine {

	function __construct($action) {
		$this->_supported = array("admin");
		parent::__construct($action);
	}

	function call() {
		if ($this->_session->requireLogin())
			exit();
		
		parent::call();
	}
}

?>
