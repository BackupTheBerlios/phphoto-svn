<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Utils.php");
require_once("includes/Session.php");
require_once("includes/User.php");
require_once("includes/Group.php");

class AjaxService {

	private $_dom;
	private $_service;
	private $_response;
	private $_query;
	private $_method;
	private $_db;

	function __construct() {
		$this->_dom = new DOMDocument("1.0", "utf-8");
	}

	private function error($desc, $val = "") {
		$this->_service->setAttribute("status", "error");
		$error = $this->_service->appendChild($this->_dom->createElement("error"));
		$error->appendChild($this->_dom->createElement($desc, $val));
		return $error;
	}

	private function success() {
		$this->_service->setAttribute("status", "success");
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
		$this->success();
	}

	function addGroupMember() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$uid = Utils::pg("uid", 0);
		$gid = Utils::pg("gid", 0);
		$login = Utils::pg("user-login", 0);

		$this->_query->appendChild($this->_dom->createElement("group-id", $gid));
		$this->_query->appendChild($this->_dom->createElement("user-id", $uid));

		if (!empty($login)) {
			$this->_query->appendChild($this->_dom->createElement("user-login", $login));
			$uid = User::getUID($login);
		}

		if ($uid <= 0 || $gid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$group = new Group($gid);
		$user = new User($uid);

		if (!$session->checkPermAndLevelVal("add-group-members", $group->getLevel())) {
			$this->error("permission-denied");
			return;
		}

		$group->addMember($user);

		$memb = $group->getMemberInfo($uid);
		$member = $this->_dom->createElement("member");
		$member->appendChild($this->_dom->createElement("user-id", $memb['user_id']));
		$member->appendChild($this->_dom->createElement("user-login", $memb['user_login']));
		$member->appendChild($this->_dom->createElement("user-name", $memb['user_name']));
		$member->appendChild($this->_dom->createElement("user-title", $memb['user_title']));
		$member->appendChild($this->_dom->createElement('addedby-id', $memb['addedby_id']));
		$member->appendChild($this->_dom->createElement('addedby-login', $memb['addedby_login']));
		$member->appendChild($this->_dom->createElement('addedby-name', $memb['addedby_name']));
		$member->appendChild($this->_dom->createElement('addedby-title', $memb['addedby_title']));
		$member->appendChild($this->_dom->createElement('add-time', Utils::formatTime($memb['add_time'], Config::getUser($session->uid(), 'datetime-format'))));
		$member->appendChild($this->_dom->createElement('allow-remove', $session->checkPermAndLevel('remove-group-members', $memb['user_id'])));
		$this->_response->appendChild($member);

		$this->success();
	}

	function removeGroupMember() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$uid = Utils::pg("uid", 0);
		$gid = Utils::pg("gid", 0);

		$this->_query->appendChild($this->_dom->createElement("group-id", $gid));
		$this->_query->appendChild($this->_dom->createElement("user-id", $uid));

		if ($uid <= 0 || $gid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$group = new Group($gid);
		$user = new User($uid);

		if (!$session->checkPermAndLevel("remove-group-members", $uid)) {
			$this->error("permission-denied");
			return;
		}

		$group->removeMember($user);
		$this->success();
	}

	function getGroupMembers() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$gid = Utils::pg("gid", 0);

		$this->_query->appendChild($this->_dom->createElement("group-id", $gid));

		if ($gid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$group = new Group($gid);

		if (!$session->checkPermAndLevelVal("view-group-members", $group->getLevel()) && !$group->isMember($session->uid())) {
			$this->error("permission-denied");
			return;
		}

		$members = $group->getMembersInfo();

		foreach ($members as $memb) {
			$member = $this->_dom->createElement("member");
			$member->appendChild($this->_dom->createElement("user-id", $memb['user_id']));
			$member->appendChild($this->_dom->createElement("user-login", $memb['user_login']));
			$member->appendChild($this->_dom->createElement("user-name", $memb['user_name']));
			$member->appendChild($this->_dom->createElement("user-title", $memb['user_title']));
			$member->appendChild($this->_dom->createElement('addedby-id', $memb['addedby_id']));
			$member->appendChild($this->_dom->createElement('addedby-login', $memb['addedby_login']));
			$member->appendChild($this->_dom->createElement('addedby-name', $memb['addedby_name']));
			$member->appendChild($this->_dom->createElement('addedby-title', $memb['addedby_title']));
			$member->appendChild($this->_dom->createElement('add-time', Utils::formatTime($memb['add_time'], Config::getUser($session->uid(), 'datetime-format'))));
			$member->appendChild($this->_dom->createElement('allow-remove', $session->checkPermAndLevel('remove-group-members', $memb['user_id'])));
			$this->_response->appendChild($member);
		}
		$this->_response->appendChild($this->_dom->createElement('allow-add', $session->checkPermAndLevelVal('add-group-members', Group::getGroupLevel($gid))));

		$this->success();
	}

	function countGroupMembers() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$gid = Utils::pg("gid", 0);

		$this->_query->appendChild($this->_dom->createElement("group-id", $gid));

		if ($gid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$group = new Group($gid);

		if (!$session->checkPermAndLevelVal("view-group-members", $group->getLevel()) && !$group->isMember($session->uid())) {
			$this->error("permission-denied");
			return;
		}

		$members = $group->getMembersCount();
		$this->_response->appendChild($this->_dom->createElement("count", $members));

		$this->success();
	}

	function call($method, $callid) {

		$this->_method = $method;
		$this->_service = $this->_dom->appendChild($this->_dom->createElement("service"));
		$this->_service->setAttribute("method", $method);
		$this->_service->setAttribute("call-id", $callid);
		$this->_query = $this->_service->appendChild($this->_dom->createElement("query"));
		$this->_response = $this->_service->appendChild($this->_dom->createElement("response"));

		try {
			switch ($this->_method) {
			case 'check-login-exists':
				$this->checkLoginExists();
				break;
			case 'add-group-member':
				$this->addGroupMember();
				break;
			case 'get-group-members':
				$this->getGroupMembers();
				break;
			case 'remove-group-member':
				$this->removeGroupMember();
				break;
			case 'count-group-members':
				$this->countGroupMembers();
				break;
			default:
				$this->error("unknown-method");
				break;
			}
		} catch (Exception $e) {
			$this->error("exception", $e->getMessage());
		}
	}

	function response() {
		header("Content-Type: text/xml; charset=utf-8");
		echo $this->_dom->saveXML();
	}
}

?>
