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

if (!Permissions::checkPerm('delete_categories'))
	die ("Permission denied.");

$ref = urldecode(Utils::pg("ref"));
$cid = urldecode(Utils::pg("cid"));

function delete_category($cid) {
	global $db;
	$q = $db->prepare("SELECT category_id FROM phph_categories WHERE category_parent = ?");
	$r = $db->execute($q, $cid);
	if (PEAR::isError($r))
		die($r->getMessage());

	while ($row = $r->fetchRow()) {
		delete_category($row['category_id']);
	}

	$q = $db->prepare("DELETE FROM phph_categories WHERE category_id = ?");
	$r = $db->execute($q, $cid);
	if (PEAR::isError($r))
		die($r->getMessage());
}

delete_category($cid);

header("Location: $ref");

ini_restore('include_path');

?>
