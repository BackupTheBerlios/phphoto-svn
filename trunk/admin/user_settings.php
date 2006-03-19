<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("XML/Tree.php");
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

if (!Permissions::checkPerm('users_and_groups_settings'))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_SETTINGS?></h1>
</div>

<br />

<?php

if (!empty($_POST['submit'])) {

	try {
		$values = array(
			'require_login' => false,
			'enable_registration' => false,
			'account_activation' => false,
			'activation_message' => false
		);

		foreach ($values as $name => $htmlize) {
			$val = $_POST[$name];
			if ($htmlize)
				$val = htmlspecialchars($val);

			$r = Config::set($name, $val);
			if (PEAR::isError($r)) {
				throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
			}
		}

		$pane = new HTML_MessagePane("upd", _ADMIN_SETTINGS_UPDATED, "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();
	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("site_form", _ADMIN_SETTINGS_FOR_GROUP . _ADMIN_USERS_AND_GROUPS, $session->addSID("user_settings.php"));

$pane = new HTML_AdminFormPane("p1", "Rejestracja");
$field = new HTML_SelectField("require_login", "Wymagaj logowania", "Dostêp do serwisu tylko dla zalogowanych u¿ytkowników?", 0, Config::get("require_login", 0));
$field->addYesNo();
$pane->addField($field);
$field = new HTML_SelectField("enable_registration", "W³±cz rejestracjê", "Czy w³±czyæ mo¿liwo¶æ rejestracji nowych kont w systemie?", 0, Config::get("enable_registration", 1));
$field->addYesNo();
$pane->addField($field);
$field = new HTML_SelectField("account_activation", "Wymagana aktywacja konta", "Czy wymagaæ od u¿ytkownika aktywacji konta za pomoc± linka przesy³anego emailem?", 0, Config::get("account_activation", 0));
$field->addYesNo();
$pane->addField($field);
$field = new HTML_MemoField("activation_message", "Wiadomo¶æ aktywacyjna", "Komunikat przesy³any do u¿ytkowników w mailu aktywacyjnym.", Config::get("activation_message"), 10, 70);
$pane->addField($field);
$form->addPane($pane);

$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
