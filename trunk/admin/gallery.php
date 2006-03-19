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

if (!Permissions::checkPerm('gallery_settings'))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_GALLERY?> :: <?=_ADMIN_SETTINGS?></h1>
</div>

<br />

<?php

if (!empty($_POST['submit'])) {

	try {
		$values = array(
			'max_file_size' => false,
			'max_width' => false,
			'max_height' => false,
			'auto_approve' => false,
			'cache_lifetime' => false
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

$form = new HTML_AdminForm("site_form", _ADMIN_SETTINGS_FOR_GROUP . _ADMIN_GALLERY, $session->addSID("gallery.php"));

$pane = new HTML_AdminFormPane("p1", _ADMIN_PHOTOS);
$field = new HTML_TextField("max_file_size", _ADMIN_MAX_FILE_SIZE_T, _ADMIN_MAX_FILE_SIZE_D, 50, Config::get("max_file_size", 150));
$pane->addField($field);
$field = new HTML_TextField("max_width", _ADMIN_MAX_WIDTH_T, _ADMIN_MAX_WIDTH_D, 50, Config::get("max_width", 640));
$pane->addField($field);
$field = new HTML_TextField("max_height", _ADMIN_MAX_HEIGHT_T, _ADMIN_MAX_HEIGHT_D, 50, Config::get("max_height", 600));
$pane->addField($field);
$field = new HTML_SelectField("auto_approve", _ADMIN_AUTO_APPROVE_T, _ADMIN_AUTO_APPROVE_D, 0, Config::get("auto_approve", 0));
$field->addOption(1, _YES);
$field->addOption(0, _NO);
$pane->addField($field);
$field = new HTML_TextField("cache_lifetime", _ADMIN_CACHE_LIFETIME_T, _ADMIN_CACHE_LIFETIME_D, 50, Config::get("cache_lifetime", 7));
$pane->addField($field);
$form->addPane($pane);

$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
