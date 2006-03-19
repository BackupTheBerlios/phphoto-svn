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

if (!Permissions::checkPerm('delete_users'))
	die ("Permission denied.");


$ref = urldecode(Utils::pg("ref"));
$uid = urldecode(Utils::pg("uid"));

$user = DB_DataObject::Factory("phph_users");
if (PEAR::isError($user))
	die($user->getMessage());
$r = $user->get($uid);
if (PEAR::isError($r))
	die($r->getMessage());
if (!(Permissions::isAdmin() || Permissions::checkLevel($user->user_id)))
	header("Location: $ref");



$q = $db->prepare("DELETE FROM phph_users WHERE user_id = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());


$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE user_id = ?");
$r = $db->execute($q, $uid);
while ($row = $r->fetchRow()) {
	$photo = new Photo($row['photo_id']);
	$photo->remove();
}

$q = $db->prepare("DELETE FROM phph_group_users WHERE user_id = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("DELETE FROM phph_sessions WHERE user_id = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("DELETE FROM phph_session_history WHERE user_id = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("DELETE FROM phph_user_ip WHERE user_id = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("DELETE FROM phph_permissions WHERE user_id = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("UPDATE phph_groups SET group_creator = NULL WHERE group_creator = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("UPDATE phph_group_users SET added_by = NULL WHERE added_by = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());
$q = $db->prepare("UPDATE phph_categories SET category_creator = NULL WHERE category_creator = ?");
$r = $db->execute($q, $uid);
if (PEAR::isError($r))
	die($r->getMessage());

header("Location: $ref");

ini_restore('include_path');

?>
