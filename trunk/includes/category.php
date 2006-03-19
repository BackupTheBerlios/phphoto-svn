<?php
// $Id$

require_once("DB/DataObject.php");
require_once("includes/db.php");
require_once("includes/config.php");
require_once("includes/utils.php");

define('CATEGORY_NOT_FOUND', 1);
define('CATEGORY_PARENT_NOT_FOUND', 2);

class Category {
	
	var $_cid = 0;
	var $_dbo;
	

	function __construct($cid = 0) {
		global $db;

		$this->_cid = $cid;

		if ($this->_cid != 0) {
		
			$this->_dbo = DB_DataObject::Factory("phph_categories");
			if (PEAR::isError($this->_dbo))
				throw new Exception2("B³±d wewnêtrzny", $this->_dbo->getMessage());
			$r = $this->_dbo->get($cid);
			if (PEAR::isError($r))
				throw new Exception2("B³±d wewnêtrzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("B³±d", "Kategoria nie istnieje", CATEGORY_NOT_FOUND);
		}

	}

	function getParentTree($self = true) {
		global $db;

		$q = $db->prepare("SELECT pc.category_id AS category_id, pc.category_name AS category_name FROM phph_categories c INNER JOIN phph_categories pc ON c.category_parent = pc.category_id WHERE c.category_id = ?");
		$parent = $this->_cid;
		$tree = array();
		while ($parent > 0) {
			$res = $db->execute($q, array($parent));
			$row = $res->fetchRow();
			if (!$row)
				break;
			$parent = $row['category_id'];
			$tree[] = array('id' => $row['category_id'], 'name' => $row['category_name']);
		}
		$tree = array_reverse($tree);
		if ($self && $this->_cid > 0)
			$tree[] = array('id' => $this->_cid, 'name' => $this->_dbo->category_name);

		return $tree;
	}

	private function getSubTreeR($cid) {
		global $db;

		$dbo = DB_DataObject::Factory("phph_categories");
		if (PEAR::isError($dbo))
			throw new Exception2("B³±d wewnêtrzny", $dbo->getMessage());
		$r = $dbo->get($cid);
		if (PEAR::isError($r))
			throw new Exception2("B³±d wewnêtrzny", $r->getMessage());
		if ($r == 0)
			throw new Exception2("B³±d", "Kategoria nie istnieje", CATEGORY_NOT_FOUND);

		$sub = array();

		$q = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent = ? ORDER BY category_order ASC");
		$res = $db->execute($q, array($cid));
		while ($row = $res->fetchRow())
			$sub[] = $this->getSubTreeR($row['category_id']);

		return array(
			'id' => $cid,
			'name' => $dbo->category_name,
			'sub' => $sub
		);
	}

	function getSubTree($self = true) {
		global $db;
		$tree = array();

		if ($self && $this->_cid > 0) {
			$tree[] = $this->getSubTreeR($this->_cid);
		} else {
			if ($this->_cid > 0) {
				$q = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent = ? ORDER BY category_order ASC");
				$res = $db->execute($q, array($this->_cid));
			} else {
				$q = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent IS NULL ORDER BY category_order ASC");
				$res = $db->execute($q);
			}

			while ($row = $res->fetchRow())
				$tree[] = $this->getSubTreeR($row['category_id']);
		}

		return $tree;
	}

	private function getPhotosR($cid, $approved_only) {
		global $db;

		$pids = array();

		if ($cid > 0) {
			if ($approved_only)
				$q = $db->prepare("SELECT c.photo_id FROM phph_photos_categories c INNER JOIN phph_photos WHERE category_id = ? AND photo_approved IS NOT NULL");
			else
				$q = $db->prepare("SELECT photo_id FROM phph_photos_categories WHERE category_id = ?");
			$res = $db->execute($q, array($cid));
			while ($row = $res->fetchRow())
				$pids[] = $row['photo_id'];

			$q = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent = ?");
			$res = $db->execute($q, array($cid));
		} else {
			$q = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent IS NULL");
			$res = $db->execute($q);
		}

		while ($row = $res->fetchRow()) {
			$a = $this->getPhotosR($row['category_id'], $approved_only);
			$pids = array_merge($pids, $a);
		}

		return $pids;
	}

	function getPhotos($approved_only = true) {
		return $this->getPhotosR($this->_cid, $approved_only);
	}

	function checkSubscription($uid, $cp = true) {
		global $db;
		$q = $db->prepare("SELECT * FROM phph_subscriptions WHERE user_id = ? AND category_id = ?");
		$res = $db->execute($q, array($uid, $this->_cid));
		if ($res->numRows() > 0)
			return true;
		if (empty($this->_dbo->category_parent) || !$cp)
			return false;

		$parent = new Category($this->_dbo->category_parent);
		return $parent->checkSubscription($uid);
	}

	function addSubscription($uid) {
		global $db;

		if ($this->checkSubscription($uid))
			return;

		$q = $db->prepare("INSERT INTO phph_subscriptions (user_id, category_id, subscription_date) VALUES (?, ?, ?)");
		$db->execute($q, array($uid, $this->_cid, time()));
	}

	function removeSubscription($uid) {
		global $db;

		$q = $db->prepare("DELETE FROM phph_subscriptions WHERE user_id = ? AND category_id = ?");
		$db->execute($q, array($uid, $this->_cid));
	}

	function getSubscribers(&$subs) {
		global $db;

		if (!empty($this->_dbo->category_parent)) {
			$category = new Category($this->_dbo->category_parent);
			$category->getSubscribers(&$subs);
		}

		$q = $db->prepare("SELECT s.user_id, s.category_id, u.user_email, u.user_name, u.user_login, c.category_name FROM phph_subscriptions s INNER JOIN phph_users u ON s.user_id = u.user_id INNER JOIN phph_categories c ON c.category_id = s.category_id WHERE s.category_id = ?");
		$res = $db->execute($q, array($this->_cid));

		while ($row = $res->fetchRow()) {
			$subs[$row['user_id']]['user_login'] = $row['user_login'];
			$subs[$row['user_id']]['user_name'] = $row['user_name'];
			$subs[$row['user_id']]['user_email'] = $row['user_email'];
			$subs[$row['user_id']]['cids'][$row['category_id']]['category_name'] = $row['category_name'];
		}
	}
}

?>
