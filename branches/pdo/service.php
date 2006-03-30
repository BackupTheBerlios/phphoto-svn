<?php
// $Id$

require_once("includes/Utils.php");
require_once("includes/AjaxService.php");

$action = Utils::pg("action", "start");

if ($action == "service") {
	// AJAX service handlers
	$ajax = new AjaxService();
	$ajax->call(Utils::pg("method", "unknown"), Utils::pg("_uniqid", ""));
	$ajax->response();
}

?>
