<?php
// $Id$

require_once("includes/session.php");
require_once("DB/DataObject.php");
require_once("includes/html.php");
require_once("includes/lang.php");
require_once("includes/smarty.php");
require_once("includes/photo.php");
require_once("includes/category.php");
require_once("includes/user.php");
require_once("Mail.php");

$url = Config::get("site_url");
$ref = Utils::pg("ref");
$page = Utils::pg("p", 0);
$count = Utils::pg("c", 20);
$action = Utils::pg("action", "index");

$session = Session::singletone();
if (Config::get("require_login", 0) && $action != 'login' && $session->requireLogin())
	exit();

$smarty = new Phph_Smarty($action);
$smarty->assign('ref', $ref);
$smarty->assign('self', Utils::selfURL());

$templates = array(
	'index' => 'index.tpl',
	'view' => 'view.tpl',
	'categories' => 'categories.tpl',
	'category' => 'category.tpl',
	'user' => 'user.tpl',
	'register' => 'register-form.tpl',
	'registered' => 'registered.tpl',
	'reg-disabled' => 'reg-disabled.tpl',
	'activate' => 'activation.tpl',
	'login' => 'login.tpl'
);

$smarty->register_function('url', 'smarty_url');

function url($action, $attrs = array()) {
	global $ref, $url;
	$session = Session::singletone();

	$s = $url . "/index.php?action=$action";
	$s = $session->addSID($s);
	if (!empty($ref))
		$s .= "&amp;ref=" . htmlspecialchars(urlencode($ref));

	foreach ($attrs as $id => $val) {
		$s .= htmlspecialchars("&$id=" . urlencode($val));
	}

	return $s;
}

function smarty_url($params, &$smarty) {
	return url($params['action']);
}

function get_photo($pid, $cid) {
	
	$photo = new Photo($pid);
	$data = $photo->get(100, 100);

	return array(
		'id' => $pid,
		'title' => $photo->_dbo->photo_title,
		'description' => $photo->_dbo->photo_description,
		'url' => url('view') . "&amp;pid=$pid&amp;cid=$cid",
		'thumb_img' => $data[7],
		'thumb' => $data[0],
		'user_id' => $photo->_dbo->user_id,
		'user_login' => $photo->_user->user_login,
		'user_url' => url('user', array('uid' => $photo->_dbo->user_id))
	);
}

function pager($url, $total) {
	global $count, $page;

	$n_pages = ceil($total / $count);

	$pages = array();

	for ($i = 0; $i < $n_pages; $i++) {
		$pages[] = array(
			'index' => $i,
			'page' => $i + 1,
			'url' => $url . "&amp;p=$i&amp;c=$count",
			'current' => $page == $i
		);
	}

	return $pages;
}

function action_index() {
	global $smarty, $template;
}

function action_view() {
	global $smarty, $url, $ref, $template, $db;

	$session = Session::singletone();

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
			$smarty->assign("error", 1);
			$smarty->assign("error_title", $e->getMessage());
			$smarty->assign("error_description", $e->getDescription());
			$smarty->assign("error_code", $e->getCode());
		}
	}

	$smarty->assign('pid', $pid);
	$smarty->assign('w', $w);
	$smarty->assign('h', $h);
	if ($ok) {
		$data = $photo->get($w, $h);
		$smarty->assign('photo_categories', $photo->_categories);

		foreach ($photo->_category_objs as $cat) {
			$trees[] = $cat->getParentTree(true);
			$subtrees[] = $cat->getSubTree(true);
		}
		$smarty->assign('categories_full', $trees);
		$smarty->assign('categories_tree', $subtrees);

		$main_c = new Category();
		$smarty->assign('full_category_tree', $main_c->getSubTree());

		$smarty->assign('photo_id', $photo->_pid);
		$smarty->assign('photo_cids', $photo->_cids);
		$smarty->assign('photo_cid_map', $photo->_cid_map);
		$smarty->assign('photo_url', $data[0]);
		$smarty->assign('photo_w', $data[1]);
		$smarty->assign('photo_h', $data[2]);
		$smarty->assign('photo_imgw', $data[3]);
		$smarty->assign('photo_imgh', $data[4]);
		$smarty->assign('photo_imgwh', $data[5]);
		$smarty->assign('photo_title', $photo->_dbo->photo_title);
		$smarty->assign('page_title', $photo->_dbo->photo_title);
		$smarty->assign('photo_description', $photo->_dbo->photo_description);
		$smarty->assign('post_comment_action', url('postcomment'));

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
		$smarty->assign('photo_comments', $comments);
		if (!$session->requireLogin(false))
			$smarty->assign('allow_comments', 1);

		$next_pid = 0;
		$prev_pid = 0;

		$cat = new Category($cid);
		$pids = $cat->getPhotos();
		$cid_s = "";
		if (!empty($pids))
			$cid_s = " AND photo_id IN (" . implode(", ", $pids) . ") ";
		$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id > ? $cid_s ORDER BY photo_id ASC LIMIT 0, 1");
		$res = $db->execute($q, array($pid));
		if ($row = $res->fetchRow()) {
			$prev_pid = $row['photo_id'];
		}
		$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id < ? $cid_s ORDER BY photo_id DESC LIMIT 0, 1");
		$res = $db->execute($q, array($pid));
		if ($row = $res->fetchRow()) {
			$next_pid = $row['photo_id'];
		}

		if (!empty($next_pid))
			$smarty->assign('next_photo', get_photo($next_pid, $cid));
		if (!empty($prev_pid))
			$smarty->assign('prev_photo', get_photo($prev_pid, $cid));
	}
}

function action_categories() {
	global $smarty, $url, $ref, $template, $db;

	$cid = Utils::pg("cid", 0);

	$main_c = new Category($cid);
	$smarty->assign('category_tree', $main_c->getSubTree(true));
	$smarty->assign('page_title', "Kategorie");
}

function action_category() {
	global $smarty, $url, $ref, $template, $db, $page, $count;

	$session = Session::singletone();

	$cid = Utils::pg("cid", 0);

	$cat = new Category($cid);
	$smarty->assign('category_tree', $cat->getSubTree(true));
	if ($cid > 0) {
		$smarty->assign('page_title', $cat->_dbo->category_name);
		$smarty->assign('category_name', $cat->_dbo->category_name);
		if (!$session->requireLogin(false)) {
			if (!$cat->checkSubscription($session->_uid))
				$smarty->assign('subscribe_url', url('subscribe', array('cid' => $cid, 'ref' => Utils::selfURL())));
			elseif ($cat->checkSubscription($session->_uid, false))
				$smarty->assign('unsubscribe_url', url('unsubscribe', array('cid' => $cid, 'ref' => Utils::selfURL())));
		}
	} else {
		$smarty->assign('page_title', "Wszystkie kategorie");
		$smarty->assign('category_name', "Wszystkie kategorie");
	}

	$pids = $cat->getPhotos();
	if (!empty($pids)) {
		$in_s = implode(", ", $pids);
		$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id IN ($in_s) AND photo_approved IS NOT NULL ORDER BY photo_added DESC LIMIT ?, ?");
		$res = $db->execute($q, array(intval($page) * $count, intval($count)));

		$photos = array();

		while ($row = $res->fetchRow()) {
			$photos[] = get_photo($row['photo_id'], $cid);
		}
		$smarty->assign('photos', $photos);

		$pages = pager(url('category', array('cid' => $cid)), count(array_unique($pids)));
		$smarty->assign('pager', $pages);
	}
}

function action_subscribe() {
	global $ref;

	$session = Session::singletone();

	$cid = Utils::pg("cid", 0);
	$category = new Category($cid);
	$category->addSubscription($session->_uid);

	header("Location: $ref");
	exit();
}

function action_unsubscribe() {
	global $ref;

	$session = Session::singletone();

	$cid = Utils::pg("cid", 0);
	$category = new Category($cid);
	$category->removeSubscription($session->_uid);

	header("Location: $ref");
	exit();
}

function action_user() {
	global $smarty, $url, $ref, $template, $db, $page, $count;

	$cid = Utils::pg("cid", 0);
	$uid = Utils::pg("uid", 0);

	$cat = new Category($cid);
	$user = new User($uid);
	$smarty->assign('category_tree', $cat->getSubTree(true));

	$smarty->assign('page_title', $user->_dbo->user_login);
	$smarty->assign('user_login', $user->_dbo->user_login);
	$smarty->assign('user_name', $user->_dbo->user_name);

	$pids = $cat->getPhotos();
	if (!empty($pids)) {
		$in_s = implode(", ", $pids);
		$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id IN ($in_s) AND photo_approved IS NOT NULL AND user_id = ? ORDER BY photo_added DESC LIMIT ?, ?");
		$res = $db->execute($q, array($uid, intval($page) * $count, intval($count)));

		$photos = array();

		while ($row = $res->fetchRow()) {
			$photos[] = get_photo($row['photo_id'], $cid);
		}
		$smarty->assign('photos', $photos);

		$pages = pager(url('user', array('cid' => $cid, 'uid' => $uid)), count(array_unique($pids)));
		$smarty->assign('pager', $pages);
	}
}

function action_register() {
	global $smarty, $url, $ref, $template, $db, $page, $count;

	if (!Config::get("enable_registration")) {
		$smarty->assign('page_title', "Rejestracja chwilowo niedostêpna.");
		$template = "reg-disabled";
		return;
	}

	$register_ok = false;

	if (!empty($_POST['submit'])) {
		$user = new User(0);

		try {
			$pid = $user->register($_POST);
			$user = new User($pid);
			if (Config::get("account_activation", 0))
				$user->sendActivation(url('activate'));
			else
				$user->activate($user->_dbo->user_activation, url('login'));
			$register_ok = true;

		} catch (Exception2 $e) {
			$smarty->assign('error', 1);
			$smarty->assign('error_title', $e->getMessage());
			$smarty->assign('error_description', $e->getDescription());
		}
	}
	
	$user_login = Utils::p('user_login');
	$user_email = Utils::p('user_email');
	$smarty->assign('login', $user_login);
	$smarty->assign('email', $user_email);
	$smarty->assign('register_action', url('register'));
	$smarty->assign('page_title', "Rejestracja");
	$smarty->assign('need_activation', Config::get("account_activation"));

	if ($register_ok)
		$template = "registered";
	else
		$template = "register";
}

function action_activate() {
	global $smarty;

	$uid = Utils::g('uid', 0);
	$r = Utils::g('r');

	try {
		$user = new User($uid);
		$user->activate($r, url('login'));

		$smarty->assign('login_url', url('login'));
		$smarty->assign('activation_ok', 1);
	} catch (Exception $e) {
		$smarty->assign('error', 1);
		$smarty->assign('error_title', $e->getMessage());
		$smarty->assign('error_description', $e->getDescription());
	}
}

function action_login() {
	global $smarty, $ref;

	if (!empty($_POST['submit'])) {
		try {
			$user = new User(0);
			$user->login($_POST['user_login'], $_POST['user_pass']);
			if (empty($ref))
				header("Location: " . url('index'));
			else
				header("Location: $ref");
		} catch (Exception $e) {
			$smarty->assign('error', 1);
			$smarty->assign('error_title', $e->getMessage());
			$smarty->assign('error_description', $e->getDescription());
		}
			

	}
	$smarty->assign('login_action', url('login'));
	$smarty->assign('page_title', 'Logowanie');
}

function action_postcomment() {
	global $ref;

	$session = Session::singletone();
	if ($session->requireLogin(false)) {
		header("Location: $ref");
		exit();
	}

	$pid = Utils::p('pid');
	$photo = new Photo($pid);

	$photo->addComment($_POST['comment_title'], $_POST['comment_text']);

	header("Location: $ref");
	exit();
}

$template = $action;

if ($action == 'view') {
	action_view();
} elseif ($action == "categories") {
	action_categories();
} elseif ($action == "category") {
	action_category();
} elseif ($action == "user") {
	action_user();
} elseif ($action == "register") {
	action_register();
} elseif ($action == "activate") {
	action_activate();
} elseif ($action == "login") {
	action_login();
} elseif ($action == "postcomment") {
	action_postcomment();
} elseif ($action == "subscribe") {
	action_subscribe();
} elseif ($action == "unsubscribe") {
	action_unsubscribe();
} else {
	$action = "index";
	$template = $action;
	action_index();
}

HTML::startHTML();
HTML::head($smarty->get_template_vars('page_title'));
HTML::startBODY();

$smarty->display($templates[$template]);

HTML::endBODY();
HTML::endHTML();


?>
