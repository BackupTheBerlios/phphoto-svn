<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");
require_once("includes/Language.php");
require_once("includes/Session.php");

class Group {

	private $_gid = 0;
	private $_dbdata = array();
	private $_orig_dbdata = null;
	private $_members = array();
	private $_members_info = array();
	private $_members_info_map = array();

	private static $_level_cache = array();

	function __construct($gid = 0) {
		$this->_gid = $gid;
		$this->updateDBData();
	}

	function gid() {
		return $this->_gid;
	}

	private function updateDBData() {
		if ($this->_gid > 0) {
			$sth = Database::singletone()->db()->prepare("SELECT * FROM phph_groups WHERE group_id = :group_id");
			$sth->bindParam(":group_id", $this->_gid);
			$sth->execute();
			if (!($this->_dbdata = $sth->fetch()))
				throw new Exception(_T("Grupa nie istnieje"));
			$this->_orig_dbdata = $this->_dbdata;
			self::$_level_cache[$this->dbdata('group_id')] = $this->dbdata('group_level');
			$sth = null;

			$sth = Database::singletone()->db()->prepare(
				'SELECT '.
				'gm.user_id, '.
				'u.user_login, u.user_name, u.user_title, '.
				'gm.add_time, ab.user_id AS addedby_id, ab.user_login AS addedby_login, ab.user_name AS addedby_name, ab.user_title AS addedby_title '.
				'FROM phph_group_members gm '.
				'INNER JOIN phph_users u ON gm.user_id = u.user_id '.
				'LEFT OUTER JOIN phph_users ab ON gm.added_by = ab.user_id '.
				'WHERE gm.group_id = :group_id');
			$sth->bindParam(":group_id", $this->_gid);
			$sth->execute();
			$this->_members = array();
			$this->_members_info = array();
			while ($row = $sth->fetch()) {
				$this->_members[] = $row['user_id'];
				$this->_members_info[] = $row;
				$this->_members_info_map[$row['user_id']] = $row;
			}
		}
	}

	function dbdata($name, $def = '') {
		if (array_key_exists($name, $this->_dbdata))
			return $this->_dbdata[$name];
		else
			return $def;
	}

	function setDBData($name, $val) {
		$this->_dbdata[$name] = $val;
	}

	function save() {
		$db = Database::singletone()->db();
		if ($this->dbdata('group_name') != $this->_orig_dbdata['group_name']) {
			$sth = $db->prepare('SELECT COUNT(*) FROM phph_groups WHERE group_name = :group_name');
			$sth->bindValue(':group_name', $this->dbdata('group_name'));
			$sth->execute();
			$r = $sth->fetchColumn(0);
			$sth = null;
			if ($r > 0) {
				throw new Exception(_T('Grupa o tej nazwie już istnieje.'));
			}
		}

		if ($this->gid() == 0) {
			$sth = $db->prepare(
				'INSERT INTO phph_groups '.
				'(group_name, group_description, group_created, group_creator, group_level) '.
				'VALUES '.
				'(:group_name, :group_description, :group_created, :group_creator, :group_level)');
			$sth->bindValue(':group_created', time());
			$sth->bindValue(':group_creator', Session::singletone()->uid());
		} else {
			$sth = $db->prepare(
				'UPDATE phph_groups SET '.
				'group_name = :group_name, '.
				'group_description = :group_description, '.
				'group_level = :group_level '.
				'WHERE group_id = :group_id');
			$sth->bindValue(':group_id', $this->gid());
		}
		$sth->bindValue(":group_name", $this->dbdata('group_name'));
		$sth->bindValue(":group_description", $this->dbdata('group_description'));
		$sth->bindValue(":group_level", $this->dbdata('group_level', Config::get('default_group_level', 0)));
		$sth->execute();
		if ($this->gid() == 0)
			$this->_gid = $db->lastInsertId();
		$sth = null;
		$this->updateDBData();
	}

	static function getGroupLevel($gid) {

		if (array_key_exists($gid, self::$_level_cache)) {
			return self::$_level_cache[$gid];
		} else {
			$db = Database::singletone()->db();

			$sth = $db->prepare("SELECT group_level FROM phph_groups WHERE group_id = :group_id");
			$sth->bindParam(":group_id", $gid);
			$sth->execute();
			$gl = $sth->fetchColumn(0);
			$sth = null;
			self::$_level_cache[$gid] = $gl;

			return $gl;
		}
	}

	function getLevel() {
		return self::getGroupLevel($this->gid());
	}

	function getPerm($perm) {
		$db = Database::singletone()->db();

		$sth = $db->prepare("SELECT COUNT(*) FROM phph_permissions WHERE permission = :perm AND group_id = :gid");
		$sth->bindParam(":perm", $perm);
		$sth->bindValue(":gid", $this->gid());
		$sth->execute();
		$r = $sth->fetchColumn(0);
		$sth = null;
		if ($r > 0)
			return true;

		return false;
	}

	function setPerm($perm, $val) {
		$db = Database::singletone()->db();

		$sth = $db->prepare('DELETE FROM phph_permissions WHERE permission = :permission AND group_id = :group_id');
		$sth->bindParam(':permission', $perm);
		$sth->bindValue(':group_id', $this->gid());
		$sth->execute();
		$sth = null;

		if ($val) {
			$sth = $db->prepare('INSERT INTO phph_permissions (permission, group_id) VALUES (:permission, :group_id)');
			$sth->bindParam(':permission', $perm);
			$sth->bindValue(':group_id', $this->gid());
			$sth->execute();
			$sth = null;
		}
	}

	function addMember($user) {
		$db = Database::singletone()->db();

		if ($this->isMember($user->uid()))
			throw new Exception(_T("Użytkownik jest już członkiem tej grupy."));

		$sth = $db->prepare('INSERT INTO phph_group_members (user_id, group_id, added_by, add_time) VALUES (:user_id, :group_id, :added_by, :add_time)');
		$sth->bindValue(':user_id', $user->uid());
		$sth->bindValue(':group_id', $this->gid());
		$sth->bindValue(':added_by', Session::singletone()->uid());
		$sth->bindValue(':add_time', time());
		$sth->execute();
		$sth = null;
		$user->addToGroup($this, false);
		$this->updateDBData();
	}

	function removeMember($user) {
		$db = Database::singletone()->db();

		if (!$this->isMember($user->uid()))
			throw new Exception(_T("Użytkownik nie jest członkiem tej grupy."));

		$sth = $db->prepare('DELETE FROM phph_group_members WHERE user_id = :user_id AND group_id = :group_id');
		$sth->bindValue(':user_id', $user->uid());
		$sth->bindValue(':group_id', $this->gid());
		$sth->execute();
		$sth = null;
		$user->removeFromGroup($this);
		$this->updateDBData();
	}

	function isMember($uid) {
		return array_search($uid, $this->_members) !== FALSE;
	}

	function getMembers() {
		return $this->_members;
	}

	function getMembersInfo() {
		return $this->_members_info;
	}

	function getMemberInfo($uid) {
		if (!$this->isMember($uid))
			throw new Exception(_T("Użytkownik nie jest członkiem tej grupy."));
		return $this->_members_info_map[$uid];
	}

	function getMembersCount() {
		return count($this->_members);
	}
}

?>
