<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("DB/DataObject.php");
require_once("includes/lang.php");
require_once("includes/html.php");
require_once("includes/permissions.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

HTML::startHTML(true);
HTML::head(_ADMIN_TITLE);

?>

<frameset cols='220, *' frameborder='no' border='0' framespacing='0'>
	<frame name='menu' id='menu' noresize="noresize" scrolling='auto' src='menu.php' />
	<frame name='main' id='main' noresize="noresize" scrolling='auto' src='body.php' />
</frameset>


<?php

HTML::endHTML();
ini_restore('include_path');

?>
