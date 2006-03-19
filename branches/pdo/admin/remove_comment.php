<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("includes/utils.php");
require_once("includes/permissions.php");
require_once("includes/comment.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

$ref = urldecode(Utils::pg("ref"));
$cmid = urldecode(Utils::pg("cmid"));

if (empty($cmid))
	die();

$comment = new Comment($cmid);

if (!Permissions::checkPermAndLevel('delete_comments', $comment->_dbo->user_id))
	die ("Permission denied.");

$comment->remove();

header("Location: $ref");

ini_restore('include_path');

?>
