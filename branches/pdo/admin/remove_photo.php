<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("includes/utils.php");
require_once("includes/permissions.php");
require_once("includes/photo.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

if (!Permissions::checkPerm('delete_photos'))
	die ("Permission denied.");


$ref = urldecode(Utils::pg("ref"));
$pid = urldecode(Utils::pg("pid"));

if (empty($pid))
	die();

$photo = new Photo($pid);
$photo->remove();

header("Location: $ref");

ini_restore('include_path');

?>
