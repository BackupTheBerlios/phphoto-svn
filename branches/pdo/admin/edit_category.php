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

if ($action == "edit") {
	if (!Permissions::checkPerm('edit_categories'))
		die ("Permission denied.");
} else {
	if (!Permissions::checkPerm('add_categories'))
		die ("Permission denied.");
}

$ref = urldecode(Utils::pg("ref"));
$cid = Utils::pg("cid");
$category_name = "";
$category_parent = 0;
$category_description = "";

if ($action == "edit") {
	
	$category = DB_DataObject::Factory('phph_categories');
	if (PEAR::isError($category))
		die ($category->getMessage());

	$r = $category->get($cid);
	if (PEAR::isError($r))
		die ($r->getMessage());

	if ($r == 0) {
		if (!empty($ref))
			header("Location: " . $ref);

		$action = "add";
	}

	$category_name = $category->category_name;
	$category_parent = $category->category_parent;
	$category_description = $category->category_description;
}

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_PHOTOS?> :: <?php if ($action == "add") echo _ADMIN_ADD_NEW_CATEGORY; else echo _ADMIN_EDIT_CATEGORY;?></h1>
</div>

<br />

<?php


if (!empty($_POST['submit'])) {

	$err = ($action == "add" ? _ADMIN_CANT_CREATE_CATEGORY : _ADMIN_CANT_UPDATE_CATEGORY);

	try {

		$category_name = trim($_POST['category_name']);
		$category_parent = $_POST['category_parent'];
		$category_description = $_POST['category_description'];

		if ($category_name == "") {
			$err_login = true;
			throw new Exception2($err, _ADMIN_ERROR_EMPTY_CATEGORY_NAME);
		}
		
		$category = DB_DataObject::Factory('phph_categories');
		if (PEAR::isError($category)) {
			throw new Exception2(_INTERNAL_ERROR, $category->getMessage());
		}

		$category->keys('category_name', 'category_parent');
		$category->category_name = $category_name;
		if ($category_parent > 0)
			$category->category_parent = $category_parent;
		else
			$category->category_parent = DB_DataObject_Cast::sql("NULL");
		$r = $category->find();
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		$category->fetch();
		if ($r != 0 && ($action == "add" || ($action == "edit" && $category->category_id != $cid))) {
			throw new Exception2($err, _ADMIN_CATEGORY_EXISTS);
		}

		if ($action == "edit") {
			$category = DB_DataObject::Factory('phph_categories');
			if (PEAR::isError($category)) {
				throw new Exception2(_INTERNAL_ERROR, $category->getMessage());
			}

			$r = $category->get($cid);
			if (PEAR::isError($r))
				throw new Exception2(_INTERNAL_ERROR, $r->getMessage());

			if ($r == 0)
				throw new Exception2($err, _ADMIN_ERROR_CATEGORY_DOESNT_EXISTS);

		} else {
			$category->category_created = time();
			$category->category_creator = $session->_uid;
		}

		$category->category_name = $category_name;
		$category->category_description = $category_description;

		if ($category->category_parent != $category_parent || $action == "add") {
			if ($category_parent > 0) {
				$q = $db->prepare("SELECT IFNULL(MAX(category_order), 0) AS ord FROM phph_categories WHERE category_parent = ?");
				$res = $db->execute($q, $category_parent);
			} else {
				$q = $db->prepare("SELECT IFNULL(MAX(category_order)) AS ord FROM phph_categories WHERE category_parent IS NULL");
				$res = $db->execute($q, $category_parent);
			}
			$row = $res->fetchRow();
			$category->category_order = $row['ord'] + 1;
		}

		if ($category_parent > 0)
			$category->category_parent = $category_parent;
		else
			$category->category_parent = DB_DataObject_Cast::sql("NULL");

		if ($action == "edit") {
			$r = $category->update();
		} elseif ($action == "add") {
			$r = $category->insert();
		}
		
		if (PEAR::isError($r))
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", ($action == "add" ? _ADMIN_CATEGORY_CREATED : _ADMIN_CATEGORY_UPDATED), "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

function fill_category_tree($field, $ccid, $level) {
	global $db, $cid;

	$qs = "SELECT category_id, category_name FROM phph_categories ";

	if (!empty($ccid)) {
		$qs .= "WHERE category_parent = $ccid ";
	} else {
		$qs .= "WHERE category_parent IS NULL ";
	}
	$qs .= "ORDER BY category_order";
	$q = $db->prepare($qs);
	$res = $db->execute($q);
	if (PEAR::isError($res))
		die($res->getMessage());
	while ($res->fetchInto($row)) {

		if ($cid != $row['category_id']) {
			$name = "";
	
			if ($level > 0) {
				for ($i = 0; $i < $level; $i++)
					$name .= "---";
				$name .= " ";
			}

			$name .= $row['category_name'];

			$field->addOption($row['category_id'], $name);

			fill_category_tree($field, $row['category_id'], $level+1);
		}
	}

}

$form = new HTML_AdminForm("edit_category_form", $action == "add" ? _ADMIN_ADD_NEW_CATEGORY : _ADMIN_EDIT_CATEGORY, $session->addSID("edit_category.php"));
$form->addHidden("ref", $ref);
$form->addHidden("cid", $cid);
$form->addHidden("action", $action);

$pane = new HTML_AdminFormPane("p1", $action == "add" ? _ADMIN_NEW_CATEGORY : htmlspecialchars($category_name));
$field = new HTML_TextField("category_name", _ADMIN_CATEGORY_NAME_T, _ADMIN_CATEGORY_NAME_D, 50, $category_name);
$pane->addField($field);
$field = new HTML_MemoField("category_description", _ADMIN_CATEGORY_DESCRIPTION_T, _ADMIN_CATEGORY_DESCRIPTION_D, $category_description, 5, 50);
$pane->addField($field);

$field = new HTML_SelectField("category_parent", _ADMIN_CATEGORY_PARENT_T, _ADMIN_CATEGORY_PARENT_D, 10, $category_parent);
$field->addOption(0, _ADMIN_MAIN_CATEGORY);
fill_category_tree($field, null, 1);
$pane->addField($field);

$form->addPane($pane);

$form->_submit = ($action == "add" ? _ADMIN_ADD_CATEGORY : _ADMIN_SAVE);
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
