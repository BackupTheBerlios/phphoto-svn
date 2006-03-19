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

if (!Permissions::checkPerm('delete_groups'))
	die ("Permission denied.");

$ref = urldecode(Utils::pg("ref"));
$gid = urldecode(Utils::pg("gid"));

$q = $db->prepare("DELETE FROM phph_groups WHERE group_id = ?");
$r = $db->execute($q, $gid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("DELETE FROM phph_group_users WHERE group_id = ?");
$r = $db->execute($q, $gid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("DELETE FROM phph_permissions WHERE group_id = ?");
$r = $db->execute($q, $gid);
if (PEAR::isError($r))
	die($r->getMessage());

header("Location: $ref");

ini_restore('include_path');

?>
