<?php
// $Id$

set_include_path(get_include_path() . ":../");

require_once("includes/session.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/db.php");
require_once("includes/html.php");
require_once("includes/lang.php");
require_once("includes/permissions.php");
require_once("includes/comment.php");

$session = Session::singletone();
if ($session->requireLogin())
	exit;

if (!Permissions::checkPerm('admin_panel'))
	die ("Permission denied.");

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

$action = Utils::pg("action", "edit");
$cmid = Utils::pg("cmid");

$ref = urldecode(Utils::pg("ref"));
$comment_title = "";
$comment_text = "";

$comment = new Comment($cmid);

$comment_title = $comment->_dbo->comment_title;
$comment_text = $comment->_dbo->comment_text;
	
if (!Permissions::checkPermAndLevel('edit_comments', $comment->_dbo->user_id))
	die ("Permission denied.");

?>

<div class="a_white_pane">
<h1 class="a_title">Edycja komentarza</h1>
</div>

<br />

<?php


if (!empty($_POST['submit'])) {

	try {

		$comment->update($_POST['comment_title'], $_POST['comment_text']);

		if (!empty($ref))
			header("Location: " . $ref);

		$pane = new HTML_MessagePane("upd", ($action == "add" ? _ADMIN_GROUP_CREATED : _ADMIN_GROUP_UPDATED), "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("edit_comment_form", "Edycja komentarza", $session->addSID("edit_comment.php"));
$form->addHidden("ref", $ref);
$form->addHidden("cmid", $cmid);

$pane = new HTML_AdminFormPane("p1", "Komentarz");
$field = new HTML_TextField("comment_title", "Tytu³", "", 50, $comment_title);
$pane->addField($field);
$field = new HTML_MemoField("comment_text", "Tre¶æ", "", $comment_text, 5, 50);
$pane->addField($field);
$form->addPane($pane);

$form->_submit = "Zapisz";
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
