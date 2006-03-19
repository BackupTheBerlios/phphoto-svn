<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("includes/db.php");
require_once("includes/html.php");
require_once("includes/lang.php");
require_once("includes/permissions.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;


if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

$url = Config::getStatic("site_url");

HTML::startHTML(true);
HTML::head();
HTML::startBODY("a_menu");

$session = Session::singletone();

?>

<div class="a_menu_pane" style="background-color: #fff;">
<h1 class="a_title"><?=_ADMIN_MENU?></h1>
</div>

<br />

<?php

$admin_url = $url . '/admin';

$items = array();

if (Permissions::checkPerm('site_configuration')) {
	$items['gs_sc'] = array(
		'name' => _ADMIN_SITE_CONFIGURATION,
		'href' => $session->addSID($admin_url . '/site.php')
	);
}

$items['gs_db'] = array(
	'name' => _ADMIN_DATABASE_OPERATIONS,
	'href' => $session->addSID($admin_url . '/database.php')
);

$menu_data = array(
	'gs' => array(
		'name' => _ADMIN_GENERAL_SETTINGS,
		'items' => $items
	)
);

$menu = new HTML_Menu($menu_data, "a_menu_pane");
$menu->show();

echo "<br />";

$items = array();
if (Permissions::checkPerm('users_and_groups_settings')) {
	$items['uag_s'] = array(
		'name' => _ADMIN_SETTINGS,
		'href' => $session->addSID($admin_url . '/user_settings.php')
	);
}
$items['uag_u'] = array(
	'name' => _ADMIN_USERS,
	'href' => $session->addSID($admin_url . '/users.php')
);
if (Permissions::checkPerm('add_users')) {
	$items['uag_au'] = array(
		'name' => _ADMIN_ADD_NEW_USER,
		'href' => $session->addSID($admin_url . '/add_user.php')
	);
}
$items['uag_g'] = array(
	'name' => _ADMIN_GROUPS,
	'href' => $session->addSID($admin_url . '/groups.php')
);
if (Permissions::checkPerm('add_groups')) {
	$items['uag_ag'] = array(
		'name' => _ADMIN_ADD_NEW_GROUP,
		'href' => $session->addSID($admin_url . '/edit_group.php?action=add')
	);
}
if (Permissions::checkPerm('mess_message')) {
	$items['uag_mm'] = array(
		'name' => "Wiadomo¶æ masowa",
		'href' => $session->addSID($admin_url . '/mass.php')
	);
}

$menu_data = array(
	'uag' => array(
		'name' => _ADMIN_USERS_AND_GROUPS,
		'items' => $items
	)
);

$menu = new HTML_Menu($menu_data, "a_menu_pane_2");
$menu->show();

echo "<br />";

$menu_data = array(
	'gal' => array(
		'name' => _ADMIN_GALLERY,
		'items' => array(
			'gal_s' => array(
				'name' => _ADMIN_SETTINGS,
				'href' => $session->addSID($admin_url . '/gallery.php')
			),
			'gal_c' => array(
				'name' => _ADMIN_CATEGORIES,
				'href' => $session->addSID($admin_url . '/categories.php')
			),
			'gal_ac' => array(
				'name' => _ADMIN_ADD_NEW_CATEGORY,
				'href' => $session->addSID($admin_url . '/edit_category.php?action=add')
			),
			'gal_p' => array(
				'name' => _ADMIN_PHOTOS,
				'href' => $session->addSID($admin_url . '/photos.php')
			)
		)
	)
);

$menu = new HTML_Menu($menu_data, "a_menu_pane_3");
$menu->show();

echo "<br />";


HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
