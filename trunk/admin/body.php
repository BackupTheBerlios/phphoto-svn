<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("DB/DataObject.php");
require_once("includes/db.php");
require_once("includes/html.php");
require_once("includes/permissions.php");

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();

HTML::startBODY("a_body");
HTML::endBODY("");

HTML::endHTML();

ini_restore('include_path');

?>
