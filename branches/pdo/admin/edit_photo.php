<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
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

if (!Permissions::checkPerm('edit_categories'))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

$action = Utils::pg("action", "edit");
$ref = urldecode(Utils::pg("ref"));
$pid = Utils::pg("pid");

if (empty($pid))
	die();

$photo = new Photo($pid);

$photo_title = $photo->_dbo->photo_title;
$photo_description = $photo->_dbo->photo_description;
$photo_approved = !empty($photo->_dbo->photo_approved);
$photo_cids = $photo->_cids;

?>

<div class="a_white_pane">
<h1 class="a_title">Edycja zdjêcia :: <?=htmlspecialchars($photo_title)?></h1>
</div>

<br />

<?php


if (!empty($_POST['submit'])) {

	$err = "Nie mo¿na zapisaæ zdjêcia";

	try {

		$photo_title = trim($_POST['photo_title']);
		$photo_description = $_POST['photo_description'];
		$photo_approved = $_POST['photo_approved'];

		$photo->_dbo->photo_title = $photo_title;
		$photo->_dbo->photo_description = $photo_description;
		$photo->_cids = array();
		if (!empty($_POST['photo_cids']))
			$photo->_cids = $_POST['photo_cids'];

		$approve = false;
		if (empty($photo->_dbo->photo_approved) && Permissions::checkPerm("approve_photos") && $photo_approved) {
			$approve = true;
		}

		$photo->update();
		if ($approve)
			$photo->approve();

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", "Zdjêcie zapisane", "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

function fill_category_tree($field, $ccid, $level) {
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

		$field->addOption($row['category_id'], $name);

		fill_category_tree($field, $row['category_id'], $level+1);
	}

}

class Pane_Preview extends HTML_Pane {

	var $_photo;

	function __construct($photo) {
		$this->HTML_Pane("preview_pane", htmlspecialchars($photo->_dbo->photo_title), "a_form_pane", "a_form_pane_hdr");
		$this->_photo = $photo;
	}

	function renderContent() {
		echo "<div class=\"a_photo_preview\">";
		echo $this->_photo->getImg(480, 360);
		echo "</div>";
	}
}

class Pane_Comments extends HTML_Pane {

	var $_photo;

	function __construct($photo) {
		$this->HTML_Pane("comments_pane", "Komentarze");
		$this->_photo = $photo;
	}

	function startPane() {
?>
		<div style="padding: 5px;">
<?php
	}

	function endPane() {
?>
		</div>
<?php
	}
	
	function renderContent() {

		$comments = $this->_photo->getComments();
		$session = Session::singletone();

		if (!empty($comments)) {
			foreach ($comments as $cmnt) {
?>
<div class="a_comment">
<div class="a_comment_hdr"><?=htmlspecialchars($cmnt->_dbo->comment_title)?></div>
<div class="a_comment_text">
<div class="a_table_list_details"><?=htmlspecialchars($cmnt->_user->user_login)?>, <?=Utils::formatTime($cmnt->_dbo->comment_date)?></div>
<?=nl2br(htmlspecialchars($cmnt->_dbo->comment_text))?>
</div>

<div class="a_comment_actions">
<?php if ($cmnt->_dbo->user_id == $session->_uid || Permissions::checkPermAndLevel('edit_comments', $cmnt->_dbo->user_id)) { ?>
	<a href="<?php echo HTML::addRef($session->addSID("edit_comment.php?cmid=" . $cmnt->_cmid));?>" title="Edytuj komentarz"><?php HTML::img("edit.gif", "Edytuj komentarz"); ?></a>
<? } ?>
<?php if ($cmnt->_dbo->user_id == $session->_uid || Permissions::checkPermAndLevel('delete_comments', $cmnt->_dbo->user_id)) { ?>
	<a href="<?php echo HTML::addRef($session->addSID("remove_comment.php?cmid=" . $cmnt->_cmid));?>" onclick='return confirm("Czy na pewno usun±æ komentarz?");' title="Usuñ komentarz"><?php HTML::img("trash.gif", "Usuñ komentarz"); ?></a>
<?php } ?>
</div>
</div>
<?php
			}
		} else {
?>
<div class="a_comment">Brak komentarzy.</div>
<?php
		}
	}
}



$form = new HTML_AdminForm("edit_photo_form", "Edycja zdjêcia", $session->addSID("edit_photo.php"));
$form->addHidden("ref", $ref);
$form->addHidden("pid", $pid);
$form->addHidden("action", "edit");

$pane = new Pane_Preview($photo);
$form->addPane($pane);

$pane = new HTML_AdminFormPane("p1", "Dane zdjêcia");
$field = new HTML_TextField("photo_title", "Tytu³", "", 50, $photo_title);
$pane->addField($field);
$field = new HTML_MemoField("photo_description", "Opis", "", $photo_description, 5, 50);
$pane->addField($field);

$field = new HTML_SelectField("photo_cids[]", "Kategorie", "Wybierz jedn± lub wiêcej kategorii", 10, $photo_cids);
$field->_multiselect = true;
$field->addOption(0, "Brak kategori");
fill_category_tree($field, null, 1);
$pane->addField($field);
if (Permissions::checkPerm("approve_photos") && !$photo_approved) {
	$field = new HTML_SelectField("photo_approved", "Zaakceptowane", "", 0, $photo_approved);
	$field->addYesNo();
} else {
	$field = new HTML_StaticField("photo_approved_st", "Zaakceptowane", "", $photo_approved ? "Tak" : "Nie");
	$form->addHidden("photo_approved", $photo_approved);
}
$pane->addField($field);

$form->addPane($pane);

$form->_submit = "Zapisz";
$form->show();

$block = new HTML_Block("comments", "Komentarze");
$block->_expanded = true;
$pane = new Pane_Comments($photo);
$block->addPane($pane);
$block->show();


?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
