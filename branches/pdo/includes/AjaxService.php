<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Utils.php");
require_once("includes/Session.php");
require_once("includes/User.php");
require_once("includes/Group.php");
require_once("includes/Category.php");
require_once("includes/Photo.php");

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

	function getSubCategories() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$cid = Utils::pg("cid", 0);
		$full = Utils::pg("full-tree", 0);

		$this->_query->appendChild($this->_dom->createElement("category-id", $cid));

		if ($cid < 0) {
			$this->error("bad-arguments");
			return;
		}

		$subs = Category::getSubCategories($cid);

		foreach ($subs as $sub) {
			$el = $this->_dom->createElement("category");
			$el->appendChild($this->_dom->createElement("category-id", $sub['category_id']));
			$el->appendChild($this->_dom->createElement("subcategories-count", $sub['subcategories_count']));
			$el->appendChild($this->_dom->createElement("category-name", $sub['category_name']));
			$el->appendChild($this->_dom->createElement("category-description", $sub['category_description']));
			$el->appendChild($this->_dom->createElement("category-order", $sub['category_order']));
			$el->appendChild($this->_dom->createElement('category-created', Utils::formatTime($sub['category_created'], Config::getUser($session->uid(), 'datetime-format'))));
			$el->appendChild($this->_dom->createElement('creator-id', $sub['category_creator']));
			$el->appendChild($this->_dom->createElement('creator-login', $sub['creator_login']));
			$el->appendChild($this->_dom->createElement('creator-name', $sub['creator_name']));
			$el->appendChild($this->_dom->createElement('creator-title', $sub['creator_title']));
			$el->appendChild($this->_dom->createElement('photos-count', Category::getPhotosCount($sub['category_id'], false)));
			$el->appendChild($this->_dom->createElement('total-photos-count', Category::getPhotosCount($sub['category_id'], true)));
			$this->_response->appendChild($el);
		}

		$this->success();
	}

	function moveCategory() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$cid = Utils::pg("cid", 0);
		$dir = Utils::pg("direction", 0);

		$this->_query->appendChild($this->_dom->createElement("category-id", $cid));
		$this->_query->appendChild($this->_dom->createElement("direction", $dir));

		if ($cid <= 0 || $dir == 0) {
			$this->error("bad-arguments");
			return;
		}

		if (!$session->checkPerm("edit-categories")) {
			$this->error("permission-denied");
			return;
		}

		$category = new Category($cid);
		$old_order = $category->dbdata('category_order', 0);
		$cid2 = $category->moveCategory($dir);
		$this->_response->appendChild($this->_dom->createElement('category2-id', $cid2));
		$this->_response->appendChild($this->_dom->createElement('old-order', $old_order));
		$this->_response->appendChild($this->_dom->createElement('new-order', $category->dbdata('category_order', 0)));
		$this->success();
	}

	function getCategoryParents() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$cid = Utils::pg("cid", 0);

		$this->_query->appendChild($this->_dom->createElement("category-id", $cid));

		if ($cid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$category = new Category($cid);
		$parents = $category->getParents();
		foreach ($parents as $parent) {
			$this->_response->appendChild($this->_dom->createElement('parent', $parent));
		}
		$this->success();
	}

	function getCategory() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$cid = Utils::pg("cid", 0);
		$fn_sep = Utils::pg('sub-separator', ' / ');

		$this->_query->appendChild($this->_dom->createElement("category-id", $cid));

		if ($cid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$category = new Category($cid);

		$parents = $category->getParentsObjs();
		$parents[] = $category;
		$parents_n = array();
		foreach ($parents as $p) {
			$parents_n[] = $p->dbdata('category_name');
		}

		$this->_response->appendChild($this->_dom->createElement('category-id', $category->dbdata('category_id')));
		$this->_response->appendChild($this->_dom->createElement('category-name', $category->dbdata('category_name')));
		$this->_response->appendChild($this->_dom->createElement('category-full-name', implode($fn_sep, $parents_n)));
		$this->_response->appendChild($this->_dom->createElement('category-description', $category->dbdata('category_description')));
		$this->_response->appendChild($this->_dom->createElement('category-parent', $category->dbdata('category_parent')));
		$this->_response->appendChild($this->_dom->createElement('category-order', $category->dbdata('category_order')));
		$this->_response->appendChild($this->_dom->createElement('category-created', Utils::formatTime($category->dbdata('category_created'), Config::getUser($session->uid(), 'datetime-format'))));
		$this->_response->appendChild($category->creator()->xml($this->_dom, 'creator'));
		$this->_response->appendChild($this->_dom->createElement('photos-count', $category->photosCount(false)));
		$this->_response->appendChild($this->_dom->createElement('total-photos-count', $category->photosCount(true)));
		$this->_response->appendChild($this->_dom->createElement('subcategories-count', $category->subCategoriesCount()));

		$pids = $category->photosPIDs(false);
		$tpids = $category->photosPIDs(true);

		$photos = $this->_dom->createElement('photos');
		foreach ($pids as $pid) {
			$photos->appendChild($this->_dom->createElement('photo-id', $pid));
		}
		$this->_response->appendChild($photos);

		$photos = $this->_dom->createElement('all-photos');
		foreach ($tpids as $pid) {
			$photos->appendChild($this->_dom->createElement('photo-id', $pid));
		}
		$this->_response->appendChild($photos);
		$this->success();
	}

	function getPhoto() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$pid = Utils::pg("pid", 0);
		$w = Utils::pg('w', -1);
		$h = Utils::pg('h', -1);

		$this->_query->appendChild($this->_dom->createElement("photo-id", $pid));
		$this->_query->appendChild($this->_dom->createElement("width", $w));
		$this->_query->appendChild($this->_dom->createElement("height", $h));

		if ($pid <= 0) {
			$this->error("bad-arguments");
			return;
		}

		$photo = new Photo($pid);

		$this->_response->appendChild($this->_dom->createElement('photo-id', $photo->dbdata('photo_id')));
		$this->_response->appendChild($this->_dom->createElement('photo-title', $photo->dbdata('photo_title')));
		$this->_response->appendChild($this->_dom->createElement('photo-description', $photo->dbdata('photo_description')));
		$this->_response->appendChild($this->_dom->createElement('photo-added', Utils::formatTime($photo->dbdata('photo_added'), Config::getUser($session->uid(), 'datetime-format'))));
		if ($photo->dbdata('photo_approved', 0))
			$this->_response->appendChild($this->_dom->createElement('photo-approved', 1));
		$img = $photo->get($w, $h/*, PHOTO_OPT_GRAYSCALE*/);
		$file = $this->_dom->createElement('file', $img[0]);
		$file->setAttribute('width', $img[1]);
		$file->setAttribute('height', $img[2]);
		$this->_response->appendChild($file);
		$this->_response->appendChild($photo->author()->xml($this->_dom, 'author'));
		//if ($photo->dbdata('photo_approved', 0) > 0)
		//	$this->_response->appendChild($photo->moderator()->xml($this->_dom, 'moderator'));
		$this->_response->appendChild($photo->moderationXML($this->_dom));
		$this->_response->appendChild($photo->metaXML($this->_dom));
		$this->success();
	}

	function approvePhoto() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$pid = Utils::pg("pid", 0);

		$this->_query->appendChild($this->_dom->createElement("photo-id", $pid));

		if ($pid <= 0) {
			$this->error("bad-arguments");
			return;
		}
		if (!$session->checkPerm("approve-photos")) {
			$this->error("permission-denied");
			return;
		}

		$photo = new Photo($pid);
		$photo->approve();
		$this->success();
	}

	function rejectPhoto() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$pid = Utils::pg("pid", 0);

		$this->_query->appendChild($this->_dom->createElement("photo-id", $pid));

		if ($pid <= 0) {
			$this->error("bad-arguments");
			return;
		}
		if (!$session->checkPerm("approve-photos")) {
			$this->error("permission-denied");
			return;
		}

		$photo = new Photo($pid);
		$photo->reject(Utils::pg('note', ''));

		$this->success();
	}

	function getLogins() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$sth = $db->prepare("SELECT user_login FROM phph_users ORDER BY user_login");
		$sth->execute();
		while ($row = $sth->fetch())
			$this->_response->appendChild($this->_dom->createElement('login', $row['user_login']));

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
			case 'get-sub-categories':
				$this->getSubCategories();
				break;
			case 'move-category':
				$this->moveCategory();
				break;
			case 'get-category-parents':
				$this->getCategoryParents();
				break;
			case 'get-category':
				$this->getCategory();
				break;
			case 'get-photo':
				$this->getPhoto();
				break;
			case 'approve-photo':
				$this->approvePhoto();
				break;
			case 'reject-photo':
				$this->rejectPhoto();
				break;
			case 'get-logins':
				$this->getLogins();
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
