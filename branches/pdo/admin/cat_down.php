<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/db.php");
require_once("includes/html.php");
require_once("includes/lang.php");
require_once("includes/permissions.php");
require_once("includes/category.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

if (!Permissions::checkPerm('edit_categories'))
	die ("Permission denied.");

$ref = urldecode(Utils::pg("ref"));
$cid = urldecode(Utils::pg("cid"));
$pcid = urldecode(Utils::pg("pcid"));

$category = new Category($cid);

if (!empty($pcid)) {
	$q = $db->prepare("SELECT category_order, category_id FROM phph_categories WHERE category_parent = ? AND category_order > ? ORDER BY category_order ASC LIMIT 0, 1");
	$res = $db->execute($q, array($pcid, $category->_dbo->category_order));
} else {
	$q = $db->prepare("SELECT category_order, category_id FROM phph_categories WHERE category_parent IS NULL AND category_order > ? ORDER BY category_order ASC LIMIT 0, 1");
	$res = $db->execute($q, array($category->_dbo->category_order));
}
if ($res->numRows() == 1) {
	$row = $res->fetchRow();
	$q = $db->prepare("UPDATE phph_categories SET category_order = ? WHERE category_id = ?");
	$db->execute($q, array($row['category_order'], $category->_cid));
	$db->execute($q, array($category->_dbo->category_order, $row['category_id']));
}

header("Location: $ref");

ini_restore('include_path');

?>
