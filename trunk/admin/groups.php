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

HTML::startHTML();
HTML::head();
HTML::startBODY("a_body");

?>

<div class="a_white_pane">
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_GROUPS?></h1>
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

class Pane_Groups extends HTML_Pane {
	private $_start = 0;
	private $_count = 20;

	function __construct($start, $count) {
		$this->HTML_Pane("groups_pane", _ADMIN_GROUPS);
		$this->_start = $start;
		$this->_count = $count;
	}

	function startPane() {
?>
		<div style="padding: 5px;"><div style="width: 100%">
		<table class="a_table_list" cellspacing="0" cellpadding="0">
		<tr>
		<th class="a_pane_hdr" width="5%"><?=_ADMIN_GROUP_ID_T?></th>
		<th class="a_pane_hdr" width="20%"><?=_ADMIN_GROUP_NAME_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_GROUP_CREATED_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_GROUP_CREATOR_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_GROUP_MEMBERS_T?></th>
		<th class="a_pane_hdr" width="*"><?=_ADMIN_GROUP_ACTIONS_T?></th>
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
		global $session;
		global $db;

		$q = $db->prepare(
			"SELECT " .
			"group_id, " .
			"group_name, " .
			"group_created, " .
			"group_creator, " .
			"group_level, " .
			"user_login, " .
			"user_name, " .
			"(SELECT COUNT(*) FROM phph_group_users WHERE phph_group_users.group_id = phph_groups.group_id) AS users " .
			"FROM phph_groups " .
			"LEFT OUTER JOIN phph_users ON phph_groups.group_creator = phph_users.user_id " .
			"LIMIT " . $this->_start . ", " . $this->_count
		);
		$res = $db->execute($q);
		if (PEAR::isError($res))
			die($res->getMessage());
		while ($res->fetchInto($row)) {
			$created = strftime("%Y-%m-%d %T", $row['group_created']);
?>
<tr>
	<td><a href="<?php echo HTML::addRef($session->addSID("group_info.php?gid=" . $row['group_id']));?>"><?=$row['group_id']?></a></td>
	<td><a href="<?php echo HTML::addRef($session->addSID("group_info.php?gid=" . $row['group_id']));?>"><?=htmlspecialchars($row['group_name'])?></a></td>
	<td><?=$created?></td>


	<td>
	<?php if ($row['group_creator'] > 0) { ?>
		<a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $row['group_creator']));?>"><?=htmlspecialchars($row['user_login'])?></a><div class="a_table_list_details"><?=htmlspecialchars($row['user_name'])?></div>
	<?php } else { ?>
		-
	<?php } ?>
	</td>	

	<td><?=$row['users']?></td>

	<td>
		<?php if (Permissions::checkPermAndLevelVal('edit_groups', $row['group_level'])) { ?>
			<a href="<?php echo HTML::addRef($session->addSID("edit_group.php?action=edit&amp;gid=" . $row['group_id']));?>" title="<?=_ADMIN_EDIT_GROUP?>"><?php HTML::img("edit.gif", _ADMIN_EDIT_GROUP); ?></a>
		<?php } ?>
			
		<?php if (Permissions::checkPermAndLevelVal('change_groups_permissions', $row['group_level'])) { ?>
			<a href="<?php echo HTML::addRef($session->addSID("perms.php?gid=" . $row['group_id']));?>" title="<?=_ADMIN_PERMISSIONS?>"><?php HTML::img("flag.gif", _ADMIN_PERMISSIONS); ?></a>
		<?php } ?>
		<?php if (Permissions::checkPermAndLevelVal('delete_groups', $row['group_level'])) { ?>
			<a href="<?php echo HTML::addRef($session->addSID("remove_group.php?gid=" . $row['group_id']));?>" title="<?=_ADMIN_REMOVE_GROUP?>" onclick='return confirm("<?=sprintf(_ADMIN_CONFIRM_DELETE_GROUP, $row['group_name'])?>");'><?php HTML::img("trash.gif", _ADMIN_REMOVE_GROUP); ?></a>
		<?php } ?>
			<a href="<?php echo HTML::addRef($session->addSID("group_users.php?gid=" . $row['group_id']));?>" title="<?=_ADMIN_GROUP_MEMBERS?>"><?php HTML::img("users.gif", _ADMIN_GROUP_MEMBERS); ?></a>
	</td>
</tr>
<?php
		} 
	}
}

$block = new HTML_Block("groups", _ADMIN_GROUPS);
$block->_expanded = true;
$groups = new Pane_Groups(0, 20);
$block->addPane($groups);
$block->show();

HTML::endBODY();
HTML::endHTML();

ini_restore('include_path');

?>
