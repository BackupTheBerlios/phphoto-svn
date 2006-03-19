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

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

$action = Utils::pg("action", "edit");
$gid = Utils::pg("gid");


$ref = urldecode(Utils::pg("ref"));
$group_name = "";
$group_description = "";

if ($action == "edit") {
	
	$group = DB_DataObject::Factory('phph_groups');
	if (PEAR::isError($group))
		die ($group->getMessage());

	$r = $group->get($gid);
	if (PEAR::isError($r))
		die ($r->getMessage());

	if ($r == 0) {
		if (!empty($ref))
			header("Location: " . $ref);

		$action = "add";
	}

	$group_name = $group->group_name;
	$group_description = $group->group_description;
	
	$group_level = $group->group_level;
}

if ($action == "edit") {
	if (!Permissions::checkPermAndLevelVal('edit_groups', $group_level))
		die ("Permission denied.");
} else {
	if (!Permissions::checkPerm('add_groups'))
		die ("Permission denied.");
}

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_GROUPS?> :: <?php if ($action == "add") echo _ADMIN_ADD_NEW_GROUP; else echo _ADMIN_EDIT_GROUP;?></h1>
</div>

<br />

<?php


if (!empty($_POST['submit'])) {

	$err = ($action == "add" ? "Nie mo¿na utworzyæ grupy." : "Nie mo¿na zapisaæ grupy.");

	try {

		$group_name = trim($_POST['group_name']);
		$group_description = $_POST['group_description'];
		$group_level = $_POST['group_level'];

		if ($group_name == "") {
			throw new Exception2($err, _ADMIN_ERROR_EMPTY_GROUP_NAME);
		}
		
		$group = DB_DataObject::Factory('phph_groups');
		if (PEAR::isError($group)) {
			throw new Exception2(_INTERNAL_ERROR, $group->getMessage());
		}

		$r = $group->get("group_name", $group_name);
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		$group->fetch();
		if ($r != 0 && ($action == "add" || ($action == "edit" && $group->group_id != $gid))) {
			throw new Exception2($err, _ADMIN_GROUP_EXISTS);
		}

		if ($action == "edit") {
			$group = DB_DataObject::Factory('phph_groups');
			if (PEAR::isError($group)) {
				throw new Exception2(_INTERNAL_ERROR, $group->getMessage());
			}

			$r = $group->get($gid);
			if (PEAR::isError($r))
				throw new Exception2(_INTERNAL_ERROR, $r->getMessage());

			if ($r == 0)
				throw new Exception2($err, _ADMIN_ERROR_GROUP_DOESNT_EXISTS);

		} else {
			$group->group_created = time();
			$group->group_creator = $session->_uid;
		}

		$group->group_name = $group_name;
		$group->group_description = $group_description;

		if (Permissions::checkLevelVal($group_level))
			$group->group_level = $group_level;
		else
			throw new Exception2($err, _ADMIN_GROUP_LEVEL_TO_HIGH);

		if ($action == "edit") {
			$r = $group->update();
		} elseif ($action == "add") {
			$r = $group->insert();
		}
		
		if (PEAR::isError($r))
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", ($action == "add" ? _ADMIN_GROUP_CREATED : _ADMIN_GROUP_UPDATED), "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("edit_group_form", $action == "add" ? _ADMIN_ADD_NEW_GROUP : _ADMIN_EDIT_GROUP, $session->addSID("edit_group.php"));
$form->addHidden("ref", $ref);
$form->addHidden("gid", $gid);
$form->addHidden("action", $action);

$pane = new HTML_AdminFormPane("p1", $action == "add" ? _ADMIN_NEW_GROUP : htmlspecialchars($group_name));
$field = new HTML_TextField("group_name", _ADMIN_GROUP_NAME_T, _ADMIN_GROUP_NAME_D, 50, $group_name);
$pane->addField($field);
$field = new HTML_MemoField("group_description", _ADMIN_GROUP_DESCRIPTION_T, _ADMIN_GROUP_DESCRIPTION_D, $group_description, 5, 50);
$pane->addField($field);
if (Permissions::checkLevelVal($group_level)) {
	$field = new HTML_TextField("group_level", "Poziom grupy", "", 10, $group_level);
	$pane->addField($field);
}

$form->addPane($pane);

$form->_submit = ($action == "add" ? _ADMIN_ADD_GROUP : _ADMIN_SAVE);
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
