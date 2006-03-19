<?php
// $Id$

require_once("includes/session.php");
require_once("DB/DataObject.php");
require_once("includes/html.php");
require_once("includes/lang.php");
require_once("includes/smarty.php");
require_once("includes/utils.php");
require_once("includes/photo.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

HTML::startHTML();
HTML::head(_UPLOAD_PHOTO);
HTML::startBODY();

$smarty = new Phph_Smarty(_UPLOAD_PHOTO);

$ref = Utils::pg("ref");

if (isset($_POST['submit'])) {
	try {

		$photo = new Photo();
		$photo->upload($_FILES['file'], $_POST['title'], $_POST['description'], $_POST['cid']);

	} catch (Exception2 $e) {
		$smarty->assign('error', 1);
		$smarty->assign('error_title', $e->getMessage());
		$smarty->assign('error_description', $e->getDescription());
	}
}


$smarty->assign('l_upload', _UPLOAD);
$smarty->assign('l_file', _FILE);
$smarty->assign('l_title', _TITLE);
$smarty->assign('l_description', _DESCRIPTION);
$smarty->assign('l_category', _CATEGORY);
$smarty->assign('max_file_size', Config::get("max_file_size") * 1024);
$smarty->assign('ref', $ref);

function fill_category_tree(&$categories, $ccid, $level) {
	global $db;

	$qs = "SELECT category_id, category_name FROM phph_categories ";

	if (!empty($ccid)) {
		$qs .= "WHERE category_parent = $ccid ";
	} else {
		$qs .= "WHERE category_parent IS NULL ";
	}
	$qs .= "ORDER BY category_order";
	$q = $db->prepare($qs);
	$res = $db->execute($q);
	if (PEAR::isError($res))
		die($res->getMessage());
	while ($res->fetchInto($row)) {

		$name = "";
	
		if ($level > 0) {
			for ($i = 0; $i < $level; $i++)
				$name .= "---";
			$name .= " ";
		}

		$name .= $row['category_name'];

		$categories[$row['category_id']] = $name;

		fill_category_tree(&$categories, $row['category_id'], $level+1);
	}
}

$categories = array();
fill_category_tree(&$categories, "", 0);

$smarty->assign('categories', $categories);


$smarty->display('upload-form.tpl');


HTML::endBODY();
HTML::endHTML();


?>
