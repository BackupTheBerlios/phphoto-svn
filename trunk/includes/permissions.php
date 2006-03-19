<?php
// $Id$

require_once("DB/DataObject.php");
require_once("includes/db.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/session.php");

class Permissions {

	static function isAdmin() {
		if (Permissions::isAnon())
			return false;
		$session = Session::singletone();
		return $session->getUser()->user_admin;
	}

	static function isAnon() {
		$session = Session::singletone();
		return $session->_uid == ANON_USER;
	}

	static function getUserLevel($uid) {

		global $db;

		$gl = 0;

		$q = $db->prepare("SELECT IFNULL(MAX(group_level), 0) AS gl FROM phph_groups WHERE group_id IN (SELECT group_id FROM phph_group_users WHERE user_id = ?)");
		if (PEAR::isError($q))
			die($q->getMessage());
		$r = $db->execute($q, $uid);
		if (PEAR::isError($r))
			die($r->getMessage());

		$row = $r->fetchRow();
		$gl = $row['gl'];

		$user = DB_DataObject::Factory("phph_users");
		if (PEAR::isError($user))
			die($user->getMessage());
		$r = $user->get($uid);
		if (PEAR::isError($r))
			die($r->getMessage());
		if ($r == 0)
			return 0;

		return max($gl, $user->user_level);
	}

	static function checkLevel($uid) {
		if (Permissions::isAnon())
			return false;
		if (Permissions::isAdmin())
			return true;

		$session = Session::singletone();
		return Permissions::getUserLevel($session->_uid) > Permissions::getUserLevel($uid);
	}

	static function checkLevelVal($level) {
		if (Permissions::isAnon())
			return false;
		if (Permissions::isAdmin())
			return true;

		$session = Session::singletone();
		return Permissions::getUserLevel($session->_uid) > $level;
	}

	static function checkPerm($perm) {
		global $db;

		if (Permissions::isAnon())
			return false;
		if (Permissions::isAdmin())
			return true;

		$session = Session::singletone();

		$q = $db->prepare("SELECT permission FROM phph_permissions WHERE permission = ? AND user_id = ?");
		if (PEAR::isError($q))
			die($q->getMessage());
		$r = $db->execute($q, array($perm, $session->_uid));
		if (PEAR::isError($r))
			die($r->getMessage());

		if ($r->numRows() > 0)
			return true;

		$q = $db->prepare("SELECT permission FROM phph_permissions WHERE permission = ? AND group_id IN (SELECT group_id FROM phph_group_users WHERE user_id = ?)");
		if (PEAR::isError($q))
			die($q->getMessage());
		$r = $db->execute($q, array($perm, $session->_uid));
		if (PEAR::isError($r))
			die($r->getMessage());

		if ($r->numRows() > 0)
			return true;

		return false;
		
	}

	static function checkPermAndLevel($perm, $uid) {
		return Permissions::checkPerm($perm) && Permissions::checkLevel($uid);
	}

	static function checkPermAndLevelVal($perm, $level) {
		return Permissions::checkPerm($perm) && Permissions::checkLevel($level);
	}
}

?>
