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


class Phphoto extends Engine {

	function __construct($action) {
		$this->_supported = array("start", "login", "register", "logout", "activate");
		parent::__construct($action);
	}

	function call() {
		if (Config::get("require_login", 0) && $this->_action != 'login' && $this->_session->requireLogin())
			exit();

		parent::call();
	}

	protected function _start() {
	}

	protected function _view() {
		$pid = Utils::pg("pid");
		$cid = Utils::pg("cid", 0);
		$w = Utils::pg("w", 640);
		$h = Utils::pg("h", 600);
		$ok = true;

		if (empty($pid))
			$ok = false;

		if ($ok) {
			try {
				$photo = new Photo($pid);
			} catch (Exception2 $e) {
				$ok = false;
				$this->_smarty->assign("error", 1);
				$this->_smarty->assign("error_title", $e->getMessage());
				$this->_smarty->assign("error_description", $e->getDescription());
				$this->_smarty->assign("error_code", $e->getCode());
			}
		}

		$this->_smarty->assign('pid', $pid);
		$this->_smarty->assign('w', $w);
		$this->_smarty->assign('h', $h);
		if ($ok) {
			$data = $photo->get($w, $h);
			$this->_smarty->assign('photo_categories', $photo->_categories);

			foreach ($photo->_category_objs as $cat) {
				$trees[] = $cat->getParentTree(true);
				$subtrees[] = $cat->getSubTree(true);
			}
			$this->_smarty->assign('categories_full', $trees);
			$this->_smarty->assign('categories_tree', $subtrees);

			$main_c = new Category();
			$this->_smarty->assign('full_category_tree', $main_c->getSubTree());

			$this->_smarty->assign('photo_id', $photo->_pid);
			$this->_smarty->assign('photo_cids', $photo->_cids);
			$this->_smarty->assign('photo_cid_map', $photo->_cid_map);
			$this->_smarty->assign('photo_url', $data[0]);
			$this->_smarty->assign('photo_w', $data[1]);
			$this->_smarty->assign('photo_h', $data[2]);
			$this->_smarty->assign('photo_imgw', $data[3]);
			$this->_smarty->assign('photo_imgh', $data[4]);
			$this->_smarty->assign('photo_imgwh', $data[5]);
			$this->_smarty->assign('photo_title', $photo->_dbo->photo_title);
			$this->_smarty->assign('page_title', $photo->_dbo->photo_title);
			$this->_smarty->assign('photo_description', $photo->_dbo->photo_description);
			$this->_smarty->assign('post_comment_action', url('postcomment'));

			$comments_o = $photo->getComments();
			$comments = array();
			foreach ($comments_o as $cmnt) {
				$comments[] = array (
					'id' => $cmnt->_cmid,
					'title' => $cmnt->_dbo->comment_title,
					'text' => $cmnt->_dbo->comment_text,
					'date' => Utils::formatTime($cmnt->_dbo->comment_date),
					'user_id' => $cmnt->_dbo->user_id,
					'user_url' => url('user', array('uid' => $cmnt->_dbo->user_id)),
					'user_login' => $cmnt->_user->user_login
				);
			}
			$this->_smarty->assign('photo_comments', $comments);
			if (!$this->_session->requireLogin(false))
				$this->_smarty->assign('allow_comments', 1);

			$next_pid = 0;
			$prev_pid = 0;

			$cat = new Category($cid);
			$pids = $cat->getPhotos();
			$cid_s = "";
			if (!empty($pids))
				$cid_s = " AND photo_id IN (" . implode(", ", $pids) . ") ";
			$q = $this->_db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id > ? $cid_s ORDER BY photo_id ASC LIMIT 0, 1");
			$res = $this->_db->execute($q, array($pid));
			if ($row = $res->fetchRow()) {
				$prev_pid = $row['photo_id'];
			}
			$q = $this->_db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id < ? $cid_s ORDER BY photo_id DESC LIMIT 0, 1");
			$res = $this->_db->execute($q, array($pid));
			if ($row = $res->fetchRow()) {
				$next_pid = $row['photo_id'];
			}

			if (!empty($next_pid))
				$this->_smarty->assign('next_photo', get_photo($next_pid, $cid));
			if (!empty($prev_pid))
				$this->_smarty->assign('prev_photo', get_photo($prev_pid, $cid));
		}
	}

	protected function _categories() {
		$cid = Utils::pg("cid", 0);

		$main_c = new Category($cid);
		$this->_smarty->assign('category_tree', $main_c->getSubTree(true));
		$this->_smarty->assign('page_title', "Kategorie");
	}

	protected function _category() {
		$cid = Utils::pg("cid", 0);

		$cat = new Category($cid);
		$this->_smarty->assign('category_tree', $cat->getSubTree(true));
		if ($cid > 0) {
			$this->_smarty->assign('page_title', $cat->_dbo->category_name);
			$this->_smarty->assign('category_name', $cat->_dbo->category_name);
			if (!$this->_session->requireLogin(false)) {
				if (!$cat->checkSubscription($this->_session->_uid))
					$this->_smarty->assign('subscribe_url', url('subscribe', array('cid' => $cid, 'ref' => Utils::selfURL())));
				elseif ($cat->checkSubscription($this->_session->_uid, false))
					$this->_smarty->assign('unsubscribe_url', url('unsubscribe', array('cid' => $cid, 'ref' => Utils::selfURL())));
			}
		} else {
			$this->_smarty->assign('page_title', "Wszystkie kategorie");
			$this->_smarty->assign('category_name', "Wszystkie kategorie");
		}

		$pids = $cat->getPhotos();
		if (!empty($pids)) {
			$in_s = implode(", ", $pids);
			$q = $this->_db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id IN ($in_s) AND photo_approved IS NOT NULL ORDER BY photo_added DESC LIMIT ?, ?");
			$res = $this->_db->execute($q, array(intval($this->_page) * $this->_count, intval($this->_count)));

			$photos = array();

			while ($row = $res->fetchRow()) {
				$photos[] = get_photo($row['photo_id'], $cid);
			}
			$this->_smarty->assign('photos', $photos);

			$this->_pages = pager(url('category', array('cid' => $cid)), count(array_unique($pids)));
			$this->_smarty->assign('pager', $this->_pages);
		}
	}

	protected function _subscribe() {
		$cid = Utils::pg("cid", 0);
		$category = new Category($cid);
		$category->addSubscription($this->_session->_uid);

		header("Location: $this->_ref");
		exit();
	}

	protected function _unsubscribe() {
		$cid = Utils::pg("cid", 0);
		$category = new Category($cid);
		$category->removeSubscription($this->_session->_uid);

		header("Location: $this->_ref");
		exit();
	}

	protected function _user() {
		$cid = Utils::pg("cid", 0);
		$uid = Utils::pg("uid", 0);

		$cat = new Category($cid);
		$user = new User($uid);
		$this->_smarty->assign('category_tree', $cat->getSubTree(true));

		$this->_smarty->assign('page_title', $user->_dbo->user_login);
		$this->_smarty->assign('user_login', $user->_dbo->user_login);
		$this->_smarty->assign('user_name', $user->_dbo->user_name);

		$pids = $cat->getPhotos();
		if (!empty($pids)) {
			$in_s = implode(", ", $pids);
			$q = $this->_db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id IN ($in_s) AND photo_approved IS NOT NULL AND user_id = ? ORDER BY photo_added DESC LIMIT ?, ?");
			$res = $this->_db->execute($q, array($uid, intval($this->_page) * $this->_count, intval($this->_count)));

			$photos = array();

			while ($row = $res->fetchRow()) {
				$photos[] = get_photo($row['photo_id'], $cid);
			}
			$this->_smarty->assign('photos', $photos);

			$this->_pages = pager(url('user', array('cid' => $cid, 'uid' => $uid)), count(array_unique($pids)));
			$this->_smarty->assign('pager', $this->_pages);
		}
	}

	protected function _register() {

		if (!Config::get("enable_registration")) {
			$this->_smarty->assign('page_title', "Rejestracja chwilowo niedost�na.");
			$this->_template = "reg-disabled.tpl";
			return;
		}

		$register_ok = false;

		if (!empty($_POST['submit'])) {
			$user = new User(0);

			$pid = $user->register($_POST);
			$user = new User($pid);
			if (Config::get("account_activation", 0))
				$user->sendActivation($this->url('activate'));
			else
				$user->activate($user->dbdata('user_activation'), $this->url('login'));
			$register_ok = true;
		}

		$user_login = Utils::p('user_login');
		$user_email = Utils::p('user_email');
		$this->_smarty->assign('login', $user_login);
		$this->_smarty->assign('email', $user_email);
		$this->_smarty->assign('register_action', $this->url('register'));
		$this->_smarty->assign('page_title', "Rejestracja");
		$this->_smarty->assign('need_activation', Config::get("account_activation"));

		if ($register_ok)
			$this->_template = "registered.tpl";
		else
			$this->_template = "register-form.tpl";
	}

	protected function _activate() {
		$uid = Utils::g('uid', 0);
		$r = Utils::g('r');

		$user = new User($uid);
		$user->activate($r, $this->url('login'));

		$this->setTemplateVar('login_url', $this->url('login'));
		$this->setTemplateVar('activation_ok', 1);
	}

	protected function _login() {
		$this->_smarty->assign('login_action', $this->url('login'));
		$this->_smarty->assign('page_title', 'Logowanie');

		if (!empty($_POST['submit'])) {
			$user = new User(0);
			$user->login($_POST['user_login'], $_POST['user_pass']);
			if (empty($this->_ref))
				header("Location: " . $this->url('start'));
			else
				header("Location: $this->_ref");
		}
	}

	protected function _logout() {
		$this->session()->logout();
	}

	protected function _postcomment() {
		if ($this->_session->requireLogin(false)) {
			header("Location: $this->_ref");
			exit();
		}

		$pid = Utils::p('pid');
		$photo = new Photo($pid);

		$photo->addComment($_POST['comment_title'], $_POST['comment_text']);

		header("Location: $this->_ref");
		exit();
	}

}

?>
