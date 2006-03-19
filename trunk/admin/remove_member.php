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

$ref = urldecode(Utils::pg("ref"));
$uid = urldecode(Utils::pg("uid"));
$gid = urldecode(Utils::pg("gid"));

if (!Permissions::checkPermAndLevel('remove_group_members', $uid))
	die ("Permission denied.");

$q = $db->prepare("DELETE FROM phph_group_users WHERE user_id = ? AND group_id = ?");
$r = $db->execute($q, array($uid, $gid));
if (PEAR::isError($r))
	die($r->getMessage());

header("Location: $ref");

ini_restore('include_path');

?>
