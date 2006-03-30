<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Utils.php");
require_once("includes/Session.php");

class AjaxService {

	private $_dom;
	private $_service;
	private $_response;
	private $_query;
	private $_method;

	function __construct() {
		$this->_dom = new DOMDocument("1.0", "utf-8");
	}

	private function error($desc) {
		$this->_service->setAttribute("status", "error");
		$error = $this->_service->appendChild($this->_dom->createElement("error"));
		$error->appendChild($this->_dom->createElement($desc));
		return $error;
	}

	function checkLoginExists() {
		$db = Database::singletone()->db();

		$login = Utils::pg("login", "");
		if (empty($login)) {
			$this->error("bad-arguments");
			return;
		}
		$this->_query->appendChild($this->_dom->createElement("login", $login));
		
		$sth = $db->prepare("SELECT user_id FROM phph_users WHERE user_login = :login");
		$sth->bindParam(":login", $login);
		$sth->execute();
		$r = $sth->fetch();
		if (!empty($r)) {
			$this->_response->appendChild($this->_dom->createElement("exists", "1"));
			$this->_response->appendChild($this->_dom->createElement("user-id", $r["user_id"]));
		}
	}

	function call($method, $callid) {
		
		$this->_method = $method;
		$this->_service = $this->_dom->appendChild($this->_dom->createElement("service"));
		$this->_service->setAttribute("method", $method);
		$this->_service->setAttribute("call-id", $callid);
		$this->_query = $this->_service->appendChild($this->_dom->createElement("query"));
		$this->_response = $this->_service->appendChild($this->_dom->createElement("response"));

		switch ($this->_method) {
		case "checkloginexists":
			$this->checkLoginExists();
			break;
		default:
			$this->error("unknown-method");
			break;
		}
	}
	
	function response() {
		header("Content-Type: text/xml; charset=utf-8");
		echo $this->_dom->saveXML();
	}
}

?>
