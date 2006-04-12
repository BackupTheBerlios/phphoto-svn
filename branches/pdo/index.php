<?php
// $Id$

require_once("includes/Utils.php");
require_once("includes/Phphoto.php");
require_once("includes/Admin.php");
require_once("includes/Error.php");

$time_start = microtime(true);

if (get_magic_quotes_gpc()) {
	function stripslashes_deep($value) {
		$value = is_array($value) ?
		array_map('stripslashes_deep', $value) :
		stripslashes($value);

		return $value;
	}

	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}


//function get_photo($pid, $cid) {
//
//	$photo = new Photo($pid);
//	$data = $photo->get(100, 100);
//
//	return array(
//		'id' => $pid,
//		'title' => $photo->_dbo->photo_title,
//		'description' => $photo->_dbo->photo_description,
//		'url' => url('view') . "&amp;pid=$pid&amp;cid=$cid",
//		'thumb_img' => $data[7],
//		'thumb' => $data[0],
//		'user_id' => $photo->_dbo->user_id,
//		'user_login' => $photo->_user->user_login,
//		'user_url' => url('user', array('uid' => $photo->_dbo->user_id))
//	);
//}

$action = Utils::pg("action", "start");

if ($action == "admin" || strpos($action, 'adm-') === 0)
	$engine = new Admin($action);	// Try route to admin module
else
	$engine = new Phphoto($action);
if (!$engine->valid())
	$engine = new Error($action, $engine->statusCode());	// Page not found. Give up.

$engine->call();
$engine->output($time_start);

?>
