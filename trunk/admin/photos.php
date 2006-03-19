<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("XML/Tree.php");
require_once("HTML/Crypt.php");
require_once("DB/DataObject.php");
require_once("DB/DataObject/Cast.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/db.php");
require_once("includes/html.php");
require_once("includes/lang.php");
require_once("includes/permissions.php");
require_once("includes/photo.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

$cid = Utils::pg("cid", null);
$uid = Utils::pg("uid", null);

if (!empty($cid)) {
	$dbo = DB_DataObject::Factory("phph_categories");
	if (PEAR::isError($dbo))
		die ($dbo->getMessage());
	$r = $dbo->get($cid);
	if (PEAR::isError($r))
		die ($r->getMessage());

	$cat_name = $dbo->category_name;
}

if (!empty($uid)) {
	$dbo = DB_DataObject::Factory("phph_users");
	if (PEAR::isError($dbo))
		die ($dbo->getMessage());
	$r = $dbo->get($uid);
	if (PEAR::isError($r))
		die ($r->getMessage());

	$u_name = $dbo->user_login;
}

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_PHOTOS?><?php if (!empty($cat_name)) echo(" :: " . htmlspecialchars($cat_name));?><?php if (!empty($u_name)) echo(" :: " . htmlspecialchars($u_name));?></h1>
</div>

<br />

<?php

if (!empty($_POST['submit'])) {

	try {

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

class Pane_Photos extends HTML_Pane {

	function __construct() {
		$this->HTML_Pane("photos_pane", _ADMIN_PHOTOS);
	}

	function startPane() {
?>
		<div style="padding: 5px;"><div style="width: 100%; text-align: center;" class="a_photo_wrap">
<!--		<table class="a_table_list" cellspacing="0" cellpadding="0" cols="5">-->
		
<?php
	}

	function endPane() {
?>
<!--		</table> -->
		</div></div>
<?php
	}

	function renderContent() {
		global $cid, $uid, $db;

		if (!empty($uid)) {
			$uwhere = " user_id = ?";
			$puid = $uid;
		} else {
			$uwhere = " 1 = ?";
			$puid = 1;
		}

		if (empty($cid)) {
			$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE " . $uwhere);
			$res = $db->execute($q, array($puid));
		} else {
			$q = $db->prepare("SELECT photo_id FROM phph_photos WHERE photo_id IN (SELECT photo_id FROM phph_photos_categories WHERE category_id = ?) AND " . $uwhere);
			$res = $db->execute($q, array($cid, $puid));
		}
		if (PEAR::isError($res))
			die($res->getMessage());
		while ($res->fetchInto($row)) {
			$photo = new Photo($row['photo_id']);
			$data = $photo->get(100, 100);
			$data_o = $photo->get();
			$session = Session::singletone();

			$photo_title = htmlspecialchars($photo->_dbo->photo_title);
			if (empty($photo_title))
				$photo_title = "&nbsp;";
			$popup_body  = "<strong>" . _ADMIN_AUTHOR_P . "</strong> " . htmlspecialchars($photo->_user->user_login) . "<br />";
			$popup_body .= "<strong>" . _ADMIN_ADDED_P . "</strong> " . htmlspecialchars(Utils::formatTime($photo->_dbo->photo_added)) . "<br />";
			if (!empty($photo->_dbo->photo_description))
				$popup_body .= "<strong>" . _ADMIN_DESCRIPTION_P . "</strong> " . htmlspecialchars(nl2br($photo->_dbo->photo_description)) . "<br />";
			if (!empty($photo->_categories))
				$cats = htmlspecialchars(implode(", ", $photo->_categories));
			else
				$cats = htmlspecialchars("-");
			$popup_body .= "<strong>" . _ADMIN_CATEGORIES_P . "</strong> " . $cats . "<br />";
			$popup_body .= "<strong>" . _ADMIN_WIDTH_P . "</strong> " . $photo->_dbo->photo_width . "<br />";
			$popup_body .= "<strong>" . _ADMIN_HEIGHT_P . "</strong> " . $photo->_dbo->photo_height . "<br />";
			$popup_body .= $photo->exif();
			$popup_body = htmlspecialchars($popup_body);
			$overlib = "onmouseover=\"return overlib('" . $popup_body . "', CAPTION, '$photo_title');\" onmouseout=\"return nd();\"";
?>
			<div class="a_photo_block" <?=$overlib?>><div style="width: auto; margin: 0 auto; text-align: center;">
			<div class="a_photo_title"><?=$photo_title?></div>
			<a href="<?=$data_o[0]?>" target="_blank"><img src="<?=$data[0]?>" <?=$data[5]?> alt="<?=$photo_title?>" /></a>
			<div class="a_photo_details"><?=htmlspecialchars($photo->_user->user_login)?><br />
			<?=Utils::formatDate($photo->_dbo->photo_added)?>
			</div>
			<div class="a_photo_actions">
				<?php if ($photo->_dbo->user_id == $session->_uid || Permissions::checkPerm('edit_photos')) { ?>
					<a href="<?php echo HTML::addRef($session->addSID("edit_photo.php?pid=" . $photo->_dbo->photo_id));?>" title="Edytuj zdjêcie"><?php HTML::img("edit.gif", "Edytuj zdjêcie"); ?></a>
				<? } ?>
				<?php if ($photo->_dbo->user_id == $session->_uid || Permissions::checkPermAndLevel('delete_photos', $photo->_dbo->user_id)) { ?>
					<a href="<?php echo HTML::addRef($session->addSID("remove_photo.php?pid=" . $photo->_dbo->photo_id));?>" onclick='return confirm("<?=sprintf(_ADMIN_CONFIRM_DELETE_PHOTO, $photo->_dbo->photo_title)?>");' title="<?=_ADMIN_REMOVE_PHOTO?>"><?php HTML::img("trash.gif", _ADMIN_REMOVE_PHOTO); ?></a>
				<?php } ?>

			</div>
			</div>
			</div>
<?php
		}
	
			//$this->renderCategory(0, $cid);
	}
	
}

$block = new HTML_Block("photos", _ADMIN_PHOTOS);
$block->_expanded = true;
$pane = new Pane_Photos();
$block->addPane($pane);

function fill_category_tree($field, $ccid, $level) {
	global $db;

	$qs = "SELECT category_id, category_name FROM phph_categories ";

	if (!empty($ccid)) {
		$qs .= "WHERE category_parent = $ccid";
	} else {
		$qs .= "WHERE category_parent IS NULL";
	}
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
		$field->addOption($row['category_id'], $name);
		fill_category_tree($field, $row['category_id'], $level+1);
	}

}

$block->show();

$action = Utils::g("action");
$block = new HTML_AdminForm("jump_to_category", _ADMIN_JUMP_TO_CATEGORY, $session->addSID("photos.php"), false, "get");
$block->addHidden("action", "jump");
if (!empty($uid))
	$block->addHidden("uid", $uid);
$block->_expanded = ($action == "jump");
$pane = new HTML_AdminFormPane("jtc", "");
$field = new HTML_SelectField("cid", _ADMIN_CATEGORY_T, _ADMIN_CATEGORY_D, 0, $cid);
$field->addOption(0, _ADMIN_ALL);
fill_category_tree($field, null, 1);
$pane->addField($field);
$block->addPane($pane);
$block->_submit = _ADMIN_JUMP;
$block->show();

HTML::endBODY();
HTML::endHTML();

ini_restore('include_path');

?>
