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
require_once("includes/lang.php");
require_once("includes/permissions.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

if (!Permissions::checkPerm('add_users'))
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
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_ADD_NEW_USER?></h1>
</div>

<br />

<?php

$ref = urldecode(Utils::pg("ref"));

if (!empty($_POST['submit'])) {

	try {

		$user_login = trim($_POST['user_login']);
		$user_pass1 = $_POST['user_pass1'];
		$user_pass2 = $_POST['user_pass2'];
		$user_email = trim($_POST['user_email']);

		if ($user_login == "") {
			$err_login = true;
			throw new Exception2(_ADMIN_CANT_CREATE_ACCOUNT, _ADMIN_ERROR_EMPTY_LOGIN);
		}
		
		if ($user_pass1 == "") {
			$err_pass1 = true;
			$err_pass2 = true;
			throw new Exception2(_ADMIN_CANT_CREATE_ACCOUNT, _ADMIN_ERROR_EMPTY_PASSWORD);
		}
		
		if ($user_email == "") {
			$err_email = true;
			throw new Exception2(_ADMIN_CANT_CREATE_ACCOUNT, _ADMIN_ERROR_EMPTY_EMAIL);
		}

		if ($user_pass1 != $user_pass2) {
			$err_pass1 = true;
			$err_pass2 = true;
			throw new Exception2(_ADMIN_CANT_CREATE_ACCOUNT, _ADMIN_ERROR_PASSWORDS_DO_NOT_MATCH);
		}
		
		$user = DB_DataObject::Factory('phph_users');
		if (PEAR::isError($user)) {
			throw new Exception2(_INTERNAL_ERROR, $user->getMessage());
		}

		$r = $user->get('user_login', $_POST['user_login']);
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		if ($r != 0) {
			throw new Exception2(_ADMIN_CANT_CREATE_ACCOUNT, _ADMIN_LOGIN_EXISTS);
		}

		$user->user_login = $user_login;
		$user->user_pass = md5($user_pass1);
		$user->user_email = $user_email;
		$user->user_registered = time();
		$user->user_activated = time();
		$r = $user->insert();
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", _ADMIN_USER_ADDED, "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("add_user_form", _ADMIN_ADD_NEW_USER, $session->addSID("add_user.php"));
$form->addHidden("ref", urlencode($ref));

$pane = new HTML_AdminFormPane("p1", _ADMIN_NEW_USER_DATA);
$field = new HTML_TextField("user_login", _ADMIN_USER_LOGIN_T, _ADMIN_USER_LOGIN_D, 50, $user_login);
$pane->addField($field);
$field = new HTML_TextField("user_pass1", _ADMIN_USER_PASSWORD_T, _ADMIN_USER_PASSWORD_D, 50, $user_pass1, true);
$pane->addField($field);
$field = new HTML_TextField("user_pass2", _ADMIN_REENTER_USER_PASSWORD_T, _ADMIN_REENTER_USER_PASSWORD_D, 50, "", true);
$pane->addField($field);
$field = new HTML_TextField("user_email", _ADMIN_USER_EMAIL_T, _ADMIN_USER_EMAIL_D, 50, $user_email);
$pane->addField($field);
$form->addPane($pane);

$form->_submit = _ADMIN_ADD_USER;
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
