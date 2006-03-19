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

$ref = urldecode(Utils::pg("ref"));
$msg_subject = "";
$msg_text = "";

if (!Permissions::checkPerm('mass_message'))
	die ("Permission denied.");

?>

<div class="a_white_pane">
<h1 class="a_title">Wiadomo¶æ masowa</h1>
</div>

<br />

<?php


if (!empty($_POST['submit'])) {

	try {

		$msg_subject = $_POST['msg_subject'];
		$msg_text = $_POST['msg_text'];

		$q = $db->prepare("SELECT user_email FROM phph_users");
		$res = $db->execute($q);
		$bcc = array();
		while ($row = $res->fetchRow()) {
			if (!empty($row['user_email']))
				$bcc[] = $row['user_email'];
		}
		$bccs = implode(", ", $bcc);
		$mime = new Mail_mime("\n");

		$mail = Mail::factory("mail");

		$headers = array(
			"From" => Config::get("email_user") . " <" . Config::get("email_from") . ">",
			"Subject" => $msg_subject,
			"Reply-To" => Config::get("email_from"),
			"Return-Path" => Config::get("email_from"),
			"Bcc" => $bccs
		);

		$mime->setTXTBody(Utils::linewrap($msg_text));

		$body = $mime->get(array('text_charset' => "iso-8859-2", 'html_charset' => "iso-8859-2", 'head_charset' => "iso-8859-2"));
		$headers = $mime->headers($headers);
	
		$rcpt = "";
		$mail->send($rcpt, $headers, $body);
		
		$pane = new HTML_MessagePane("upd", "Wiadomo¶æ zosta³a wys³ana.", "", "a_ok_pane", "a_ok_pane_hdr");
		$pane->show();

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

$form = new HTML_AdminForm("post_mass_message", "Wy¶lij wiadomo¶æ masow±", $session->addSID("mass.php"));

$pane = new HTML_AdminFormPane("p1", "Wiadomo¶æ");
$field = new HTML_TextField("msg_subject", "Temat", "", 70, $msg_subject);
$pane->addField($field);
$field = new HTML_MemoField("msg_text", "Tre¶æ", "", $msg_text, 10, 70);
$pane->addField($field);
$form->addPane($pane);

$form->_submit = "Wy¶lij";
$form->show();

?>

<?php
HTML::endBODY();

HTML::endHTML();

ini_restore('include_path');

?>
