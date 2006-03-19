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

$gid = Utils::pg("gid");
if (empty($gid))
	header("Location: " . $session->addSID(Config::get("site_url") . "/admin/groups.php"));

$dbo = DB_DataObject::Factory("phph_groups");
if (PEAR::isError($dbo))
	die($dbo->getMessage());
$r = $dbo->get($gid);
if (PEAR::isError($r))
	die($r->getMessage());


HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_GROUP_MEMBERS?></h1>
</div>

<br />

<?php

$action = Utils::p("action");
$user_login = Utils::p("user_login");

if (!empty($_POST['submit'])) {

	try {


		$msg = "";
		$desc = "";

		if ($action == "add") {
			if (!Permissions::checkPermAndLevelVal('add_group_members', $dbo->group_level))
				die ("Permission denied.");

			$dbo = DB_DataObject::Factory("phph_users");
			if (PEAR::isError($dbo))
				throw new Exception2(_INTERNAL_ERROR, $dbo->getMessage());
			$r = $dbo->get("user_login", $user_login);
			if (PEAR::isError($r))
				throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
			if ($r == 0)
				throw new Exception2(_ADMIN_CANT_ADD_USER_TO_GROUP, _ADMIN_USER_DOESNT_EXISTS);
			$uid = $dbo->user_id;	
			
			$dbo = DB_DataObject::Factory("phph_group_users");
			if (PEAR::isError($dbo))
				throw new Exception2(_INTERNAL_ERROR, $dbo->getMessage());

			$dbo->user_id = $uid;
			$dbo->group_id = $gid;
			$dbo->keys("user_id", "group_id");
			$r = $dbo->find();
			if (PEAR::isError($r))
				throw new Exception2(_INERNAL_ERROR, $r->getMessage());

			if ($r > 0)
				throw new Exception2(_ADMIN_CANT_ADD_USER_TO_GROUP, _ADMIN_USER_ALREADY_IN_GROUP);

			$dbo->add_time = time();
			$dbo->added_by = $session->_uid;
			$r = $dbo->insert();
			if (PEAR::isError($r))
				throw new Exception2(_INTERNAL_ERROR, $r->getMessage());

			$msg = _ADMIN_USER_ADDED_TO_GROUP;
			$user_login = "";
		}

		if (!empty($msg)) {
			$pane = new HTML_MessagePane("upd", $msg, $desc, "a_ok_pane", "a_ok_pane_hdr");
			$pane->show();
		}

	} catch (Exception2 $e) {
		$pane = new HTML_MessagePane("upd", $e->getMessage(), $e->getDescription(), "a_fail_pane", "a_fail_pane_hdr");
		$pane->show();
	}
}

class Pane_GroupUsers extends HTML_Pane {
	private $_start = 0;
	private $_count = 20;
	var $_group = "";

	function __construct($start, $count) {
		$this->HTML_Pane("group_users_pane", _ADMIN_GROUP_MEMBERS);
		$this->_start = $start;
		$this->_count = $count;
	}

	function startPane() {
?>
		<div style="padding: 5px;"><div style="width: 100%">
		<table class="a_table_list" cellspacing="0" cellpadding="0">
		<tr>
		<th class="a_pane_hdr" width="5%"><?=_ADMIN_USER_ID_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_USER_LOGIN_T?></th>
		<th class="a_pane_hdr" width="28%"><?=_ADMIN_TIME_ADDED?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_ADDED_BY?></th>
		<th class="a_pane_hdr" width="*"><?=_ADMIN_ACTIONS?></th>
		</tr>
		
<?php
	}

	function endPane() {
?>
		</table>
		</div></div>
<?php
	}
	
	function renderContent() {
		global $session, $db, $gid;

		$q = $db->prepare(
			"SELECT " . 
			"u.user_id AS memb_id, " .
			"u.user_login AS memb_login, " .
			"u.user_name AS memb_name, " .
			"gu.add_time, " .
			"ua.user_id AS add_id, " .
			"ua.user_login AS add_login, " .
			"ua.user_name AS add_name, " .
			"u.user_email AS memb_email, " .
			"u.user_www AS memb_www " .
			"FROM phph_group_users gu " .
			"INNER JOIN phph_users u ON gu.user_id = u.user_id " .
			"LEFT OUTER JOIN phph_users ua ON gu.added_by = ua.user_id " .
			"LIMIT " . $this->_start . ", " . $this->_count
		);
		$res = $db->execute($q);
		if (PEAR::isError($res))
			die($res->getMessage());
		while ($res->fetchInto($row)) {
			$added = strftime("%Y-%m-%d %T", $row['add_time']);

?>
			<tr>
				<td><a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $row['memb_id']));?>"><?=$row['memb_id']?></a></td>
				<td>
				<a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $row['memb_id']));?>"><?=htmlspecialchars($row['memb_login'])?></a>
				<div class="a_table_list_details"><?=htmlspecialchars($row['memb_login'])?></div>
				</td>
				<td><?=$added?></td>
				<td>
				<a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $row['add_id']));?>"><?=htmlspecialchars($row['add_login'])?></a>
				<div class="a_table_list_details"><?=htmlspecialchars($row['add_name'])?></div>
				</td>
				<td>
<?php if ($row['memb_id'] == $session->_uid || Permissions::checkPermAndLevel('remove_group_members', $row['memb_id'])) { ?>
	<a href="<?php echo HTML::addRef($session->addSID("remove_member.php?uid=" . $row['memb_id'] . "&amp;gid=" . $gid));?>" onclick='return confirm("<?=_ADMIN_CONFIRM_REMOVE_MEMBER?>");' title="<?=_ADMIN_REMOVE_MEMBER?>"><?php HTML::img("remove.gif", _ADMIN_REMOVE_MEMBER); ?></a>
<?php } ?>

<?php if ($row['memb_id'] == $session->_uid || Permissions::checkPermAndLevel('edit_users', $row['memb_id'])) { ?>
	<a href="<?php echo HTML::addRef($session->addSID("edit_user.php?uid=" . $row['memb_id']));?>" title="<?=_ADMIN_EDIT_USER?>"><?php HTML::img("edit.gif", _ADMIN_EDIT_USER); ?></a>
<?php } ?>

<?php if ($row['memb_id'] == $session->_uid || Permissions::checkPermAndLevel('delete_users', $row['memb_id'])) { ?>
	<a href="<?php echo HTML::addRef($session->addSID("remove_user.php?uid=" . $row['memb_id']));?>" onclick='return confirm("<?=sprintf(_ADMIN_CONFIRM_DELETE_USER, $row['memb_login'])?>");' title="<?=_ADMIN_REMOVE_USER?>"><?php HTML::img("trash.gif", _ADMIN_REMOVE_USER); ?></a>
<?php } ?>

	<a href="mailto:<?=htmlspecialchars($row['memb_email'])?>" title="<?=_ADMIN_USER_EMAIL_T?>"><?php HTML::img("email.gif", _ADMIN_USER_EMAIL_T); ?></a>
	<?php if (!empty($row['memb_www'])) { ?>
	<a href="<?=htmlspecialchars($row['memb_www'])?>" target="_blank" title="<?=_ADMIN_USER_HOME_PAGE_T?>"><?php HTML::img("www.gif", _ADMIN_USER_HOME_PAGE_T); ?></a>
	<?php } ?>
				</td>
			</tr>
<?php
		} 
	}
}

$block = new HTML_Block("group_users", $dbo->group_name);
$block->_expanded = true;
$gusers = new Pane_GroupUsers(0, 20);
$block->addPane($gusers);
$block->show();

echo "<br />";

if (Permissions::checkPermAndLevelVal('add_group_members', $dbo->group_level)) {
	$addform = new HTML_AdminForm("add_user_to_group", _ADMIN_ADD_MEMBER, $session->addSID("group_users.php?gid=$gid"));
	$addform->addHidden("action", "add");
	$addform->addHidden("gid", $gid);
	$addform->_expanded = ($action == "add");
	$pane = new HTML_AdminFormPane("autg", "");
	$field = new HTML_TextField("user_login", _ADMIN_USER_T, _ADMIN_USER_D, "", $user_login);
	$pane->addField($field);
	$addform->addPane($pane);
	$addform->_submit = _ADMIN_ADD_MEMBER;
	$addform->show();
}

HTML::endBODY();
HTML::endHTML();

ini_restore('include_path');

?>
