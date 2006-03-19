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
if (empty($uid)) {
	$gid = Utils::pg("gid");
	if (empty($gid)) {
		header("Location: " . $session->addSID(Config::get("site_url") . "/admin/users.php"));
	}
}

if (!empty($uid)) {
	
	if (!Permissions::checkPerm('change_users_permissions'))
		die ("Permission denied.");

	$user = DB_DataObject::Factory('phph_users');
	if (PEAR::isError($user))
		die($user->getMessage());

	$r = $user->get($uid);
	if (PEAR::isError($r))
		die($r->getMessage());

	if ($r == 0)
		header("Location: " . $session->addSID(Config::get("site_url") . "/admin/users.php"));

	$name = $user->user_login;

	$field = "user_id";
	$id = $uid;
} else {

	if (!Permissions::checkPerm('change_groups_permissions'))
		die ("Permission denied.");

	$group = DB_DataObject::Factory('phph_groups');
	if (PEAR::isError($group))
		die($group->getMessage());

	$r = $group->get($gid);
	if (PEAR::isError($r))
		die($r->getMessage());

	if ($r == 0)
		header("Location: " . $session->addSID(Config::get("site_url") . "/admin/groups.php"));

	$name = $group->group_name;

	$field = "group_id";
	$id = $gid;
}

$q = $db->prepare("SELECT permission FROM phph_permissions WHERE $field = ?");
$res = $db->execute($q, $id);
if (PEAR::isError($res))
	die($res->getMessage());

$permissions = array();

while ($row = $res->fetchRow()) {
	$permissions[$row['permission']] = 1;
}

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_PERMISSIONS?> :: <?=$name?></h1>
</div>

<br />

<?php

if (!empty($_POST['submit'])) {

	try {
		$perms = array(
			"admin_panel", 
			"site_configuration",
			"edit_users",
			"delete_users",
			"change_users_permissions",
			"add_users",
			"add_groups",
			"edit_groups",
			"delete_groups",
			"change_groups_permissions",
			"add_group_members",
			"remove_group_members",
			"gallery_settings",
			"add_categories",
			"edit_categories",
			"delete_categories",
			"edit_photos",
			"delete_photos",
			"approve_photos",
			"users_and_groups_settings",
			"delete_comments",
			"edit_comments",
			"mass_message"
		);

		foreach ($perms as $perm) {
			$q = $db->prepare("DELETE FROM phph_permissions WHERE permission = ? AND $field = ?");
			$db->execute($q, array($perm, $id));
			if ($_POST[$perm] == 1) {
				$q = $db->prepare("INSERT INTO phph_permissions (permission, $field) VALUES (?, ?)");
				$db->execute($q, array($perm, $id));
			}
		}

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", _ADMIN_PERMISSIONS_UPDATED, "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("permissions_form", _ADMIN_EDIT_USER, $session->addSID("perms.php"));
if (!empty($uid))
	$form->addHidden("uid", $uid);
elseif (!empty($gid))
	$form->addHidden("gid", $gid);
$form->addHidden("ref", $ref);

function add_perm($pane, $name, $title, $desc) {
	global $permissions;
	$field = new HTML_SelectField($name, $title, $desc, 0, isset($permissions[$name]) ? 1 : 0);
	$field->addOption(1, _YES);
	$field->addOption(0, _NO);
	$pane->addField($field);
}

$pane = new HTML_AdminFormPane("p1", _ADMIN_GENERAL);
add_perm($pane, "admin_panel", _ADMIN_P_ADMIN_PANEL_T, _ADMIN_P_ADMIN_PANEL_D);
add_perm($pane, "site_configuration", _ADMIN_P_SITE_CONFIGURATION_T, _ADMIN_P_SITE_CONFIGURATION_D);
add_perm($pane, "users_and_groups_settings", _ADMIN_P_USERS_AND_GROUPS_SETTINGS_T, _ADMIN_P_USERS_AND_GROUPS_SETTINGS_D);
add_perm($pane, "gallery_settings", _ADMIN_P_GALLERY_SETTINGS_T, _ADMIN_P_ADD_GROUPS_D);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p2", _ADMIN_USERS);
add_perm($pane, "add_users", _ADMIN_P_ADD_USERS_T, _ADMIN_P_ADD_USERS_D);
add_perm($pane, "edit_users", _ADMIN_P_EDIT_USERS_T, _ADMIN_P_EDIT_USERS_D);
add_perm($pane, "delete_users", _ADMIN_P_DELETE_USERS_T, _ADMIN_P_DELETE_USERS_D);
add_perm($pane, "change_users_permissions", _ADMIN_P_CHANGE_USERS_PERMISSIONS_T, _ADMIN_P_CHANGE_USERS_PERMISSIONS_D);
add_perm($pane, "mass_message", "Zezwalaj na wysy³anie wiadomo¶ci do wszystkich u¿ytkowników", "");
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p3", _ADMIN_GROUPS);
add_perm($pane, "add_groups", _ADMIN_P_ADD_GROUPS_T, _ADMIN_P_ADD_GROUPS_D);
add_perm($pane, "edit_groups", _ADMIN_P_EDIT_GROUPS_T, _ADMIN_P_EDIT_GROUPS_D);
add_perm($pane, "delete_groups", _ADMIN_P_DELETE_GROUPS_T, _ADMIN_P_DELETE_GROUPS_D);
add_perm($pane, "change_groups_permissions", _ADMIN_P_CHANGE_GROUPS_PERMISSIONS_T, _ADMIN_P_CHANGE_GROUPS_PERMISSIONS_D);
add_perm($pane, "add_group_members", _ADMIN_P_ADD_GROUP_MEMBERS_T, _ADMIN_P_ADD_GROUP_MEMBERS_D);
add_perm($pane, "remove_group_members", _ADMIN_P_REMOVE_GROUP_MEMBERS_T, _ADMIN_P_REMOVE_GROUP_MEMBERS_D);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p4", _ADMIN_GALLERY);
add_perm($pane, "add_categories", _ADMIN_P_ADD_CATEGORIES_T, _ADMIN_P_ADD_CATEGORIES_D);
add_perm($pane, "edit_categories", _ADMIN_P_EDIT_CATEGORIES_T, _ADMIN_P_EDIT_CATEGORIES_D);
add_perm($pane, "delete_categories", _ADMIN_P_DELETE_CATEGORIES_T, _ADMIN_P_DELETE_CATEGORIES_D);
add_perm($pane, "edit_photos", _ADMIN_P_EDIT_PHOTOS_T, _ADMIN_P_EDIT_PHOTOS_D);
add_perm($pane, "delete_photos", _ADMIN_P_DELETE_PHOTOS_T, _ADMIN_P_DELETE_PHOTOS_D);
add_perm($pane, "approve_photos", "Akceptowanie zdjêæ", "");
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p5", "Komentarze");
add_perm($pane, "edit_comments", "Edycja komentarzy", "Zezwól na edycjê komentarzy pod zdjêciami.");
add_perm($pane, "delete_comments", "Usuwanie komentarzy", "Zezwól na usuwanie komentarzy pod zdjêciami.");
$form->addPane($pane);

$form->_submit = _ADMIN_SAVE;
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
