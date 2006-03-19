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
require_once("includes/permissions.php");
require_once("includes/lang.php");

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
<h1 class="a_title"><?=_ADMIN_USERS_AND_GROUPS?> :: <?=_ADMIN_USERS?></h1>
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

class Pane_Users extends HTML_Pane {
	private $_start = 0;
	private $_count = 20;

	function __construct($start, $count) {
		$this->HTML_Pane("users_pane", _ADMIN_USERS);
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
		<th class="a_pane_hdr" width="28%"><?=_ADMIN_USER_NAME_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_USER_REGISTERED_T?></th>
		<th class="a_pane_hdr" width="17%"><?=_ADMIN_USER_LAST_LOGIN_T?></th>
		<th class="a_pane_hdr" width="*" colspan="6"><?=_ADMIN_USER_ACTIONS_T?></th>
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
		$users = DB_DataObject::Factory("phph_users");
		$users->limit($this->_start, $this->_count);
		$users->find();
		while ($users->fetch()) {
			$reg = strftime("%Y-%m-%d %T", $users->user_registered);
			if ($users->user_activated > 0)
				$act = strftime("%Y-%m-%d %T", $users->user_activated);
			else
				$act = "nie aktywowane";
			$login = "-";
			if (!empty($users->user_lastlogin))
				$login = strftime("%Y-%m-%d %T", $users->user_lastlogin);

			$ip = DB_DataObject::Factory("phph_user_ip");
			$ip->orderBy("last_visit DESC");
			$ip->get("user_id", $users->user_id);
?>
			<tr>
				<td><a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $users->user_id));?>"><?=$users->user_id?></a></td>
				<td><a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $users->user_id));?>"><?=htmlspecialchars($users->user_login)?></a><div class="a_table_list_details"><?=htmlspecialchars($users->user_title)?></div></td>
				<td><a href="<?php echo HTML::addRef($session->addSID("user_info.php?uid=" . $users->user_id));?>"><?=htmlspecialchars($users->user_name)?></a></td>
				<td><span title="Data rejestracji"><?=$reg?></span><div class="a_table_list_details" title="Data aktywacji"><?=$act?></div></td>
				<td><?=$login?><div class="a_table_list_details"><?=Utils::decodeIP($ip->ip)?></div></td>

				<td class="a_icon">
				<?php if ($users->user_id == $session->_uid || Permissions::checkPermAndLevel('edit_users', $users->user_id)) { ?>
					<a href="<?php echo HTML::addRef($session->addSID("edit_user.php?uid=" . $users->user_id));?>" title="<?=_ADMIN_EDIT_USER?>"><?php HTML::img("edit.gif", _ADMIN_EDIT_USER); ?></a>
				<? } ?>
				</td>

				<td class="a_icon">
				<?php if (Permissions::isAdmin() || ($users->user_id != $session->_uid && Permissions::checkPermAndLevel('change_users_permissions', $users->user_id))) { ?>
					<a href="<?php echo HTML::addRef($session->addSID("perms.php?uid=" . $users->user_id));?>" title="<?=_ADMIN_PERMISSIONS?>"><?php HTML::img("flag.gif", _ADMIN_PERMISSIONS); ?></a>
				<? } ?>
				</td>

				<td class="a_icon">
				<?php if ($users->user_id == $session->_uid || Permissions::checkPermAndLevel('delete_users', $users->user_id)) { ?>
					<a href="<?php echo HTML::addRef($session->addSID("remove_user.php?uid=" . $users->user_id));?>" onclick='return confirm("<?=sprintf(_ADMIN_CONFIRM_DELETE_USER, $users->user_login)?>");' title="<?=_ADMIN_REMOVE_USER?>"><?php HTML::img("trash.gif", _ADMIN_REMOVE_USER); ?></a>
				<?php } ?>
				</td>

				<td class="a_icon">
					<a href="mailto:<?=htmlspecialchars($users->user_email)?>" title="<?=_ADMIN_USER_EMAIL_T?>"><?php HTML::img("email.gif", _ADMIN_USER_EMAIL_T); ?></a>
				</td>

				<td class="a_icon">
	<?php if (!empty($users->user_www)) { ?>
	<a href="<?=htmlspecialchars($users->user_www)?>" target="_blank" title="<?=_ADMIN_USER_HOME_PAGE_T?>"><?php HTML::img("www.gif", _ADMIN_USER_HOME_PAGE_T); ?></a>
	<?php } ?>
				</td>

				<td class="a_icon">
		<a href="<?php echo HTML::addRef($session->addSID("photos.php?uid=" . $users->user_id));?>" title="<?=_ADMIN_PHOTOS?>"><?php HTML::img("photo.gif", _ADMIN_PHOTOS); ?></a>
				</td>
			</tr>
<?php
		} 
	}
}

$block = new HTML_Block("users", _ADMIN_USERS);
$block->_expanded = true;
$users = new Pane_Users(0, 20);
$block->addPane($users);
$block->show();

HTML::endBODY();
HTML::endHTML();

ini_restore('include_path');

?>
