<?php
// $Id$

require_once("includes/Utils.php");
require_once("includes/Phphoto.php");
require_once("includes/Admin.php");
require_once("includes/Error.php");

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

$engine = new Phphoto($action);
if (!$engine->valid())
	$engine = new Admin($action);	// Try route to admin module
if (!$engine->valid())
	$engine = new Error($action);	// Page not found. Give up.

$engine->call();
$engine->output();

?>
