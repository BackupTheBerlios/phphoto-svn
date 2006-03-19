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

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

$cid = Utils::pg("cid", null);

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_PHOTOS?> :: <?=_ADMIN_CATEGORIES?></h1>
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

class Pane_Categories extends HTML_Pane {

	function __construct() {
		$this->HTML_Pane("categories_pane", _ADMIN_CATEGORIES);
	}

	function startPane() {
?>
		<div style="padding: 5px;"><div style="width: 100%">
		<table class="a_table_list" cellspacing="0" cellpadding="0">
		<tr>
		<th class="a_pane_hdr" width="5%"><?=_ADMIN_CATEGORY_ID_T?></th>
		<th class="a_pane_hdr" width="40%"><?=_ADMIN_CATEGORY_NAME_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_CATEGORY_CREATED_T?></th>
		<th class="a_pane_hdr" width="15%"><?=_ADMIN_CATEGORY_CREATOR_T?></th>
		<th class="a_pane_hdr" width="12%"><?=_ADMIN_CATEGORY_PHOTOS_T?></th>
		<th class="a_pane_hdr" width="*" colspan="5"><?=_ADMIN_ACTIONS?></th>
		</tr>
		
<?php
	}

	function endPane() {
?>
		</table>
		</div></div>
<?php
	}

	function renderCategory($level, $cat) {
		global $session;
		global $db;

		$qs =	"SELECT " .
			"category_id, " .
			"category_name, " .
			"category_description, " .
			"category_created, " .
			"category_creator, " .
			"user_login, " .
			"user_name, " .
			"(SELECT COUNT(*) FROM phph_photos_categories WHERE phph_photos_categories.category_id = phph_categories.category_id) AS photos " .
			//"(SELECT COUNT(*) FROM phph_group_users WHERE phph_group_users.group_id = phph_groups.group_id) " .
			"FROM phph_categories " .
			"LEFT OUTER JOIN phph_users ON phph_categories.category_creator = phph_users.user_id ";
		if (!empty($cat)) {
			$qs .= "WHERE category_parent = $cat ";
		} else {
			$qs .= "WHERE category_parent IS NULL ";
		}
		$qs .= "ORDER BY category_order ASC";
		$q = $db->prepare($qs);
		$res = $db->execute($q);
		if (PEAR::isError($res))
			die($res->getMessage());
		$i = 0;
		while ($res->fetchInto($row)) {
			$created = strftime("%Y-%m-%d %T", $row['category_created']);
?>
<tr>
	<td><a href="<?php echo HTML::addRef($session->addSID("category_info.php?cid=" . $row['category_id']));?>"><?=$row['category_id']?></a></td>
<?php
			$indent = $level * 30 + 4;
?>
	<td style="padding-left: <?=$indent?>px;">
	
<a href="<?php echo HTML::addRef($session->addSID("category_info.php?cid=" . $row['category_id']));?>"><?=htmlspecialchars($row['category_name'])?></a><div class="a_table_list_details"><?=nl2br(htmlspecialchars($row['category_description']))?></div></td>
	<td><?=$created?></td>


	<td>
	<?php if ($row['category_creator'] > 0) { ?>
		<a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $row['category_creator']));?>"><?=htmlspecialchars($row['user_login'])?></a><div class="a_table_list_details"><?=htmlspecialchars($row['user_name'])?></div>
	<?php } else { ?>
		-
	<?php } ?>
	</td>	

	<td><?=$row['photos']?></td>

	<td class="a_icon">
	<?php if ($i > 0) { ?>
		<a href="<?php echo HTML::addRef($session->addSID("cat_up.php?cid=" . $row['category_id'] . "&amp;pcid=" . $cat));?>" title="Przenie¶ wy¿ej"><?php HTML::img("up.gif", "Przenie¶ wy¿ej"); ?></a>
	<?php } ?>
	</td>
	<td class="a_icon">
	<?php if ($i < $res->numRows() - 1) { ?>
		<a href="<?php echo HTML::addRef($session->addSID("cat_down.php?cid=" . $row['category_id'] . "&amp;pcid=" . $cat));?>" title="Przenie¶ ni¿ej"><?php HTML::img("down.gif", "Przenie¶ ni¿ej"); ?></a>
	<?php } ?>
	</td>
	<td class="a_icon">
		<a href="<?php echo HTML::addRef($session->addSID("edit_category.php?action=edit&amp;cid=" . $row['category_id']));?>" title="<?=_ADMIN_EDIT_CATEGORY?>"><?php HTML::img("edit.gif", _ADMIN_EDIT_CATEGORY); ?></a>
	</td>
	<td class="a_icon">
		<a href="<?php echo HTML::addRef($session->addSID("remove_category.php?cid=" . $row['category_id']));?>" title="<?=_ADMIN_REMOVE_CATEGORY?>" onclick='return confirm("<?=sprintf(_ADMIN_CONFIRM_DELETE_CATEGORY, $row['category_name'])?>");'><?php HTML::img("trash.gif", _ADMIN_REMOVE_CATEGORY); ?></a>
	</td>
	<td class="a_icon">
		<a href="<?php echo HTML::addRef($session->addSID("photos.php?cid=" . $row['category_id']));?>" title="<?=_ADMIN_PHOTOS?>"><?php HTML::img("photo.gif", _ADMIN_PHOTOS); ?></a>
	</td>
</tr>
<?php
			$this->renderCategory($level + 1, $row['category_id']);
			$i++;
		} 
	}

	function renderContent() {
		global $cid;
		$this->renderCategory(0, $cid);
	}
	
}

$block = new HTML_Block("categories", _ADMIN_CATEGORIES);
$block->_expanded = true;
$cats = new Pane_Categories();
$block->addPane($cats);
$block->show();

HTML::endBODY();
HTML::endHTML();

ini_restore('include_path');

?>
