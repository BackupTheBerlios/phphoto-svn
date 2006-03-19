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

if (!Permissions::checkPerm('site_configuration'))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_GENERAL_SETTINGS?> :: <?=_ADMIN_SITE_CONFIGURATION?></h1>
</div>

<br />

<?php

if (!empty($_POST['submit'])) {

	try {
		$values = array(
			'site_title' => true,
			'site_url' => false,
			'cookie_domain' => false,
			'cookie_name' => false,
			'cookie_path' => false,
			'session_lifetime' => false,
			'session_cookie_name' => false,
			'email_from' => false,
			'email_user' => false
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

$form = new HTML_AdminForm("site_form", _ADMIN_SETTINGS_FOR_GROUP . _ADMIN_SITE_CONFIGURATION, $session->addSID("site.php"));

$pane = new HTML_AdminFormPane("p1", _ADMIN_NAMES_AND_ADDRESSES);
$field = new HTML_TextField("site_title", _ADMIN_SITE_NAME_T, _ADMIN_SITE_NAME_D, 50, Config::get("site_title", _ADMIN_SITE_NAME_DEF));
$pane->addField($field);
$field = new HTML_TextField("site_url", _ADMIN_SITE_URL_T, _ADMIN_SITE_URL_D, 50, Config::get("site_url", _ADMIN_SITE_URL_DEF));
$pane->addField($field);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p3", _ADMIN_SESSION_AND_COOKIES);

preg_match("/^(http:\/\/)?([^\/]+)/i", Config::get("site_url", _ADMIN_SITE_URL_DEF), $matches);
$def_domain = $matches[2];

$field = new HTML_TextField("cookie_domain", _ADMIN_COOKIE_DOMAIN_T, _ADMIN_COOKIE_DOMAIN_D, 50, Config::get("cookie_domain", $def_domain));
$pane->addField($field);
$field = new HTML_TextField("cookie_name", _ADMIN_COOKIE_NAME_T, _ADMIN_COOKIE_NAME_D, 50, Config::get("cookie_name", "phph"));
$pane->addField($field);
$field = new HTML_TextField("cookie_path", _ADMIN_COOKIE_PATH_T, _ADMIN_COOKIE_PATH_D, 50, Config::get("cookie_path", "/"));
$pane->addField($field);
$field = new HTML_TextField("session_cookie_name", _ADMIN_SESSION_COOKIE_NAME_T, _ADMIN_SESSION_COOKIE_NAME_D, 50, Config::get("session_cookie_name", "sid"));
$pane->addField($field);
$field = new HTML_TextField("session_lifetime", _ADMIN_SESSION_LIFETIME_T, _ADMIN_SESSION_LIFETIME_D, 50, Config::get("session_lifetime", "3600"));
$pane->addField($field);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p4", "Email");
$field = new HTML_TextField("email_from", "Adres email", "Adres spod którego bêd± wysy³ane listy do u¿ytkowników", 50, Config::get("email_from", ""));
$pane->addField($field);
$field = new HTML_TextField("email_user", "Nazwa", "Nazwa u¿ytkownika pod jak± bêd± wysy³ane listy do u¿ytkowników", 50, Config::get("email_user", "PHPhoto"));
$pane->addField($field);
$form->addPane($pane);

$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
