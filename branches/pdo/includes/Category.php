<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Config.php");
require_once("includes/Session.php");
require_once("includes/Utils.php");
require_once("includes/User.php");

define('CATEGORY_NOT_FOUND', 1);
define('CATEGORY_PARENT_NOT_FOUND', 2);

class Category {

	private $_cid = 0;
	private $_dbdata = array();
	private $_orig_dbdata = array();
	private $_creator = null;

	function __construct($cid = 0) {
		$this->_cid = $cid;
		$this->updateDBData();
	}

	function cid() {
		return $this->_cid;
	}

	private function updateDBData() {
		if ($this->_cid > 0) {
			$sth = Database::singletone()->db()->prepare("SELECT * FROM phph_categories WHERE category_id = :category_id");
			$sth->bindParam(":category_id", $this->_cid);
			$sth->execute();
			if (!($this->_dbdata = $sth->fetch()))
				throw new Exception(_T("Kategoria nie istnieje"));
			$this->_orig_dbdata = $this->_dbdata;
			$sth = null;
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

	function origDBData($name, $def = '') {
		if (array_key_exists($name, $this->_orig_dbdata))
			return $this->_orig_dbdata[$name];
		else
			return $def;
	}

	function creator() {
		if (is_object($this->_creator))
			return $this->_creator;

		$this->_creator = new User($this->dbdata('category_creator'));
		return $this->_creator;
	}

	function save() {
		$db = Database::singletone()->db();
		$parent = $this->dbdata('category_parent', 0);
		$o_parent = $this->origDBData('category_parent', 0);
		if ($this->dbdata('category_name') != $this->origDBData('category_name') || $parent != $o_parent) {
			if ($parent > 0) {
				$sth = $db->prepare('SELECT COUNT(*) FROM phph_categories WHERE category_name = :category_name AND category_parent = :parent');
				$sth->bindParam(':parent', $parent);
			} else {
				$sth = $db->prepare('SELECT COUNT(*) FROM phph_categories WHERE category_name = :category_name AND category_parent IS NULL');
			}
			$sth->bindValue(':category_name', $this->dbdata('category_name'));
			$sth->execute();
			$r = $sth->fetchColumn(0);
			$sth = null;
			if ($r > 0) {
				throw new Exception(_T('Kategoria o tej nazwie już istnieje.'));
			}
		}

		if (($this->cid() == 0) || $parent != $o_parent) {
			if ($parent > 0) {
				$sth = $db->prepare("SELECT IFNULL(MAX(category_order), 0) AS ord FROM phph_categories WHERE category_parent = :parent");
				$sth->bindParam(':parent', $parent);
			} else {
				$sth = $db->prepare("SELECT IFNULL(MAX(category_order), 0) AS ord FROM phph_categories WHERE category_parent IS NULL");
			}
			$sth->execute();
			$this->setDBData('category_order', $sth->fetchColumn(0) + 1);
			$sth = null;
		}

		if ($this->cid() == 0) {

			$sth = $db->prepare(
				'INSERT INTO phph_categories '.
				'(category_name, category_description, category_created, category_creator, category_parent, category_order) '.
				'VALUES '.
				'(:category_name, :category_description, :category_created, :category_creator, :category_parent, :category_order)');
			$sth->bindValue(':category_created', time());
			$sth->bindValue(':category_creator', Session::singletone()->uid());
		} else {
			$sth = $db->prepare(
				'UPDATE phph_categories SET '.
				'category_name = :category_name, '.
				'category_description = :category_description, '.
				'category_order = :category_order, '.
				'category_parent = :category_parent '.
				'WHERE category_id = :category_id');
			$sth->bindValue(':category_id', $this->cid());
		}
		$sth->bindValue(":category_name", $this->dbdata('category_name'));
		$sth->bindValue(":category_description", $this->dbdata('category_description'));
		$sth->bindValue(":category_order", $this->dbdata('category_order'));
		if (empty($parent))
			$parent = null;
		$sth->bindParam(":category_parent", $parent);
		$sth->execute();
		if ($this->cid() == 0)
			$this->_cid = $db->lastInsertId();
		$sth = null;
		$this->updateDBData();
	}

	static function getSubCategoriesCIDs($cid, $deep = false) {

		$db = Database::singletone()->db();

		$sub = array();

		if ($cid == 0) {
			$sth = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent IS NULL");
		} else {
			$sth = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent = :cid");
			$sth->bindParam(":cid", $cid);
		}
		$sth->execute();
		while ($row = $sth->fetch()) {
			$sub[] = $row['category_id'];
		}
		$sth = null;
		if ($deep) {
			$ssub = array();
			for ($i = 0; $i < count($sub); $i++) {
				$ssub = array_merge($ssub, self::getSubCategoriesCIDs($sub[$i], $deep));
			}
			$sub = array_merge($sub, $ssub);
		}

		return $sub;
	}

	static function getSubCategories($cid, $deep = false, $flat = false) {

		$db = Database::singletone()->db();

		$sub = array();

		if ($cid == 0) {
			$sth = $db->prepare("SELECT c.*, ".
					    'cb.user_id AS createor_id, cb.user_login AS creator_login, cb.user_name AS creator_name, cb.user_title AS creator_title, '.
					    "(SELECT COUNT(*) FROM phph_categories c2 WHERE c2.category_parent = c.category_id) AS subcategories_count ".
					    "FROM phph_categories c ".
					    "LEFT OUTER JOIN phph_users cb ON c.category_creator = cb.user_id ".
					    "WHERE c.category_parent IS NULL ORDER BY c.category_order ASC");
		} else {
			$sth = $db->prepare("SELECT c.*, ".
					    'cb.user_id AS createor_id, cb.user_login AS creator_login, cb.user_name AS creator_name, cb.user_title AS creator_title, '.
					    "(SELECT COUNT(*) FROM phph_categories c2 WHERE c2.category_parent = c.category_id) AS subcategories_count ".
					    "FROM phph_categories c ".
					    "LEFT OUTER JOIN phph_users cb ON c.category_creator = cb.user_id ".
					    "WHERE c.category_parent = :cid ORDER BY c.category_order ASC");
			$sth->bindParam(":cid", $cid);
		}
		$sth->execute();
		while ($row = $sth->fetch()) {
			$sub[] = $row;
		}
		$sth = null;
		if ($deep) {
			$ssub = array();
			for ($i = 0; $i < count($sub); $i++) {
				$a = self::getSubCategories($sub[$i]['category_id'], $deep, $flat);
				if ($flat)
					$ssub = array_merge($ssub, $a);
				else
					$sub[$i]['subcategories'] = $a;
			}
			if ($flat)
				$sub = array_merge($sub, $ssub);
		}

		return $sub;
	}

	static function getSubCategoriesCount($cid) {
		$db = Database::singletone()->db();

		$sub = array();

		if ($cid == 0) {
			$sth = $db->prepare("SELECT COUNT(*) FROM phph_categories WHERE category_parent IS NULL");
		} else {
			$sth = $db->prepare("SELECT COUNT(*) FROM phph_categories WHERE category_parent = :cid");
			$sth->bindParam(":cid", $cid);
		}
		$sth->execute();
		$sub_c = $sth->fetchColumn(0);
		$sth = null;
		return $sub_c;
	}

	static function getPhotosPIDs($cid, $deep, $order = 'photo_added DESC', $limit = 0) {
		$db = Database::singletone()->db();

		$pids = array();

		$sth = $db->prepare("SELECT photo_id FROM phph_photos_categories WHERE category_id = :cid");
		$sth->bindParam(":cid", $cid);
		$sth->execute();
		while ($row = $sth->fetch()) {
			$pids[] = $row['photo_id'];
		}
		$sth = null;
		if ($deep) {
			$subc = self::getSubCategoriesCIDs($cid, true);
			foreach ($subc as $sub) {
				$pids = array_merge($pids, self::getPhotosPIDs($sub, false));
			}
		}

		if (empty($pids))
			return $pids;

		sort($pids);
		array_unique($pids);

		$slm = '';
		if ($limit > 0)
			$slm = ' LIMIT 0, ' . intval($limit);

		$pids_s = implode(', ', $pids);

		$sth = $db->prepare('SELECT photo_id FROM phph_photos WHERE photo_id IN (' . $pids_s . ') ORDER BY ' . $order . $slm);
		$sth->execute();
		$pids = array();
		while ($row = $sth->fetch()) {
			$pids[] = $row['photo_id'];
		}

		return $pids;
	}

	static function getPhotosCount($cid, $deep) {
		$db = Database::singletone()->db();

		$cnt = 0;
		if ($deep) {
			$pids = self::getPhotosPIDs($cid, true);
			$cnt = count($pids);
		} else {
			$sth = $db->prepare("SELECT COUNT(*) FROM phph_photos_categories WHERE category_id = :cid");
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			$cnt = $sth->fetchColumn(0);
			$sth = null;
		}
		return $cnt;
	}

	static function getPhotosObjs($cid, $deep, $order = 'photo_added DESC', $limit = 0) {
		$photos = array();
		$pids = self::getPhotosPIDs($cid, $deep, $order, $limit);
		foreach ($pids as $pid)
			$photos[] = new Photo($pid);
		return $photos;
	}

	function subCategoriesCIDs($deep) {
		return self::getSubCategoriesCIDs($this->cid(), $deep);
	}

	function subCategories($deep, $flat = false) {
		return self::getSubCategories($this->cid(), $deep, $flat);
	}

	function subCategoriesCount() {
		return self::getSubCategoriesCount($this->cid());
	}

	function photosCount($deep) {
		return self::getPhotosCount($this->cid(), $deep);
	}

	function photosPIDs($deep, $order = 'photo_added DESC', $limit = 0) {
		return self::getPhotosPIDs($this->cid(), $deep);
	}

	function photosObjs($deep, $order = 'photo_added DESC', $limit = 0) {
		return self::getPhotosObjes($this->cid(), $deep);
	}

	function fullName($sep = ' / ') {
		$parents = $this->getParentsObjs();
		$parents[] = $this;
		$parents_n = array();
		foreach ($parents as $p) {
			$parents_n[] = $p->dbdata('category_name');
		}
		return implode($sep, $parents_n);
	}

	function moveCategory($dir) {

		$cid = 0;
		$db = Database::singletone()->db();
		if ($dir == 1) {
			if ($this->dbdata('category_parent', 0) > 0) {
				$sth = $db->prepare("SELECT category_id, category_order FROM phph_categories WHERE category_parent = :parent AND category_order < :order ORDER BY category_order DESC LIMIT 0,1");
				$sth->bindValue(':parent', $this->dbdata('category_parent'));
			} else {
				$sth = $db->prepare("SELECT category_id, category_order FROM phph_categories WHERE category_parent IS NULL AND category_order < :order ORDER BY category_order DESC LIMIT 0,1");
			}
		} elseif ($dir == -1) {
			if ($this->dbdata('category_parent', 0) > 0) {
				$sth = $db->prepare("SELECT category_id, category_order FROM phph_categories WHERE category_parent = :parent AND category_order > :order ORDER BY category_order ASC LIMIT 0,1");
				$sth->bindValue(':parent', $this->dbdata('category_parent'));
			} else {
				$sth = $db->prepare("SELECT category_id, category_order FROM phph_categories WHERE category_parent IS NULL AND category_order > :order ORDER BY category_order ASC LIMIT 0,1");
			}
		} else {
			return 0;
		}
		$sth->bindValue(':order', $this->dbdata('category_order'));
		$sth->execute();
		if ($row = $sth->fetch()) {
			$cid = $row['category_id'];
			$new_order = $row['category_order'];
		}
		$sth = null;
		if ($cid > 0) {
			$sth = $db->prepare('UPDATE phph_categories SET category_order = :order WHERE category_id = :cid');
			$sth->bindValue(':order', $this->dbdata('category_order'));
			$sth->bindValue(':cid', $cid);
			$sth->execute();
			$sth = null;
			$this->setDBData('category_order', $new_order);
			$this->save();
		}
		return $cid;
	}

	function getParents() {
		$parents = array();
		if ($this->dbdata('category_parent', 0) > 0) {
			$parent = new Category($this->dbdata('category_parent'));
			$parents = $parent->getParents();
			$parents[] = $this->dbdata('category_parent');
		}
		return $parents;
	}

	function getParentsObjs() {
		$parents = array();
		if ($this->dbdata('category_parent', 0) > 0) {
			$parent = new Category($this->dbdata('category_parent'));
			$parents = $parent->getParentsObjs();
			$parents[] = $parent;
		}
		return $parents;
	}

/*
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
			throw new Exception2("Bd wewn�rzny", $dbo->getMessage());
		$r = $dbo->get($cid);
		if (PEAR::isError($r))
			throw new Exception2("Bd wewn�rzny", $r->getMessage());
		if ($r == 0)
			throw new Exception2("Bd", "Kategoria nie istnieje", CATEGORY_NOT_FOUND);

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
*/
}

?>
