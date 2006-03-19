<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("XML/Tree.php");
require_once("DB/DataObject.php");
require_once("DB/DataObject/Cast.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/db.php");
require_once("includes/html.php");
require_once("includes/permissions.php");
require_once("includes/lang.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

$uid = Utils::pg("uid");
if (empty($uid))
	header("Location: " . $session->addSID(Config::get("site_url") . "/admin/users.php"));

if (!Permissions::checkPermAndLevel('edit_users', $uid))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

$err_login = false;
$err_pass1 = false;
$err_pass2 = false;
$err_email = false;
$user_login = "";
$user_pass1 = "";
$user_pass2 = "";
$user_email = "";
?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_EDIT_USER?></h1>
</div>

<br />

<?php

$user = DB_DataObject::Factory("phph_users");
if (PEAR::isError($user))
	die($user->getMessage());

$r = $user->get($uid);
if (PEAR::isError($r))
	die($r->getMessage());

if ($r == 0) {
	$pane = new HTML_MessagePane("upd", _ADMIN_USER_NOT_FOUND, "", "a_fail_pane", "a_fail_pane_hdr");
	$pane->show();
	HTML::endBODY();
	HTML::endHTML();
	exit;
}

$user_login = $user->user_login;
$user_name = $user->user_name;
$user_email = $user->user_email;
$user_www = $user->user_www;
$user_pass = $user->user_pass;
$user_jid = $user->user_jid;
$user_title = $user->user_title;
$user_from = $user->user_from;
$user_language = $user->user_language;
$user_admin_language = $user->user_admin_language;
$user_level = $user->user_level;
$user_admin = $user->user_admin;
$ref = urldecode(Utils::pg("ref"));

if (!empty($_POST['submit'])) {

	try {

		$user_login = trim($_POST['user_login']);
		$user_pass1 = $_POST['user_pass1'];
		$user_pass2 = $_POST['user_pass2'];
		$user_email = trim($_POST['user_email']);
		$user_name = trim($_POST['user_name']);
		$user_www = trim($_POST['user_www']);
		$user_jid = trim($_POST['user_jid']);
		$user_title = trim($_POST['user_title']);
		$user_from = trim($_POST['user_from']);
		$user_level = $_POST['user_level'];
		if (Permissions::isAdmin() && isset($_POST['user_admin']))
			$user_admin = $_POST['user_admin'];

		if ($user_login == "") {
			$err_login = true;
			throw new Exception2(_ADMIN_CANT_UPDATE_ACCOUNT, _ADMIN_ERROR_EMPTY_LOGIN);
		}
		
		if ($user_pass1 != "") {
			if ($user_pass1 != $user_pass2) {
				$err_pass1 = true;
				$err_pass2 = true;
				throw new Exception2(_ADMIN_CANT_UPDATE_ACCOUNT, _ADMIN_ERROR_PASSWORDS_DO_NOT_MATCH);
			}

			$user_pass = md5($user_pass1);
		}
		
		if ($user_email == "") {
			$err_email = true;
			throw new Exception2(_ADMIN_CANT_UPDATE_ACCOUNT, _ADMIN_ERROR_EMPTY_EMAIL);
		}

		$user = DB_DataObject::Factory('phph_users');
		if (PEAR::isError($user)) {
			throw new Exception2(_INTERNAL_ERROR, $user->getMessage());
		}

		$r = $user->get('user_login', $_POST['user_login']);
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		if ($r != 0 && $user->user_id != $uid) {
			throw new Exception2(_ADMIN_CANT_UPDATE_ACCOUNT, _ADMIN_LOGIN_EXISTS);
		}

		$user = DB_DataObject::Factory('phph_users');
		if (PEAR::isError($user)) {
			throw new Exception2(_INTERNAL_ERROR, $user->getMessage());
		}

		$r = $user->get($uid);
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		$user->user_login = $user_login;
		$user->user_name = $user_name;
		$user->user_pass = $user_pass;
		$user->user_email = $user_email;
		$user->user_www = $user_www;
		$user->user_jid = $user_jid;
		$user->user_title = $user_title;
		$user->user_from = $user_from;
		$user->user_admin = $user_admin;
		if (Permissions::checkLevelVal($user_level))
			$user->user_level = $user_level;
		else
			throw new Exception2(_ADMIN_CANT_UPDATE_ACCOUNT, _ADMIN_USER_LEVEL_TO_HIGH);
		$r = $user->update();
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", "Dane zapisane", "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("edit_user_form", _ADMIN_EDIT_USER, $session->addSID("edit_user.php"));
$form->addHidden("uid", $uid);
$form->addHidden("ref", urlencode($ref));

$pane = new HTML_AdminFormPane("p1", _ADMIN_USER_DATA);
$field = new HTML_TextField("user_name", _ADMIN_USER_NAME_T, _ADMIN_USER_NAME_D, 50, $user_name);
$pane->addField($field);
$field = new HTML_TextField("user_login", _ADMIN_USER_LOGIN_T, _ADMIN_USER_LOGIN_D, 50, $user_login);
$pane->addField($field);
$field = new HTML_TextField("user_pass1", _ADMIN_USER_PASSWORD_T, _ADMIN_USER_PASSWORD_D, 50, $user_pass1, true);
$pane->addField($field);
$field = new HTML_TextField("user_pass2", _ADMIN_REENTER_USER_PASSWORD_T, _ADMIN_REENTER_USER_PASSWORD_D, 50, "", true);
$pane->addField($field);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p2", _ADMIN_CONTACT);
$field = new HTML_TextField("user_email", _ADMIN_USER_EMAIL_T, _ADMIN_USER_EMAIL_D, 50, $user_email);
$pane->addField($field);
$field = new HTML_TextField("user_jid", _ADMIN_USER_JID_T, _ADMIN_USER_JID_D, 50, $user_jid);
$pane->addField($field);
$field = new HTML_TextField("user_www", _ADMIN_USER_HOME_PAGE_T, _ADMIN_USER_HOME_PAGE_D, 50, $user_www);
$pane->addField($field);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p3", _ADMIN_ADDITIONAL_INFORMATIONS);
$field = new HTML_TextField("user_title", _ADMIN_USER_TITLE_T, _ADMIN_USER_TITLE_D, 50, $user_title);
$pane->addField($field);
$field = new HTML_TextField("user_from", _ADMIN_USER_FROM_T, _ADMIN_USER_FROM_D, 50, $user_from);
$pane->addField($field);
$form->addPane($pane);

unset($pane);
if (Permissions::checkLevel($uid)) {
	if (!isset($pane))
		$pane = new HTML_AdminFormPane("perms", _ADMIN_PERMISSIONS);
	$field = new HTML_TextField("user_level", _ADMIN_USER_LEVEL_T, _ADMIN_USER_LEVEL_D, 10, $user_level);
	$pane->addField($field);
}
if (Permissions::isAdmin()) {
	if (!isset($pane))
		$pane = new HTML_AdminFormPane("perms", _ADMIN_PERMISSIONS);
	$field = new HTML_RadioGroup("user_admin", _ADMIN_SUPER_USER_T, _ADMIN_SUPER_USER_D, $user_admin);
	$field->addOption(1, _YES);
	$field->addOption(0, _NO);
	$pane->addField($field);
}
if (isset($pane))
	$form->addPane($pane);


$form->_submit = _ADMIN_SAVE;
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
