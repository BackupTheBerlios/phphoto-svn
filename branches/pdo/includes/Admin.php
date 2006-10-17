<?php
// $Id$

require_once("includes/Session.php");
require_once("includes/PhphSmarty.php");
require_once("includes/Engine.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");
require_once("includes/User.php");
require_once("includes/Group.php");
require_once("includes/Category.php");
require_once("includes/Language.php");


class Admin extends Engine {

	function __construct($action) {
		$this->_supported = array(
			'admin',
			'adm-sitecfg',
			'adm-usercfg',
			'adm-users',
			'adm-edit-user',
			'adm-edit-perms',
			'adm-groups',
			'adm-edit-group',
			'adm-group-members',
			'adm-galcfg',
			'adm-categories',
			'adm-edit-category',
			'adm-photos'
		);
		parent::__construct($action);

		if (!$this->valid())
			return;
		if (!$this->session()->checkPerm("admin-panel")) {
			$this->_valid = false;
			$this->_status_code = 403;
		}

		if (!$this->valid())
			return;

		$this->_main_template = "admin/index.tpl";
		$this->setTemplateVar("admin_panel", 1);
		$this->setTemplateVar("sub", Utils::pg("sub", ""));
		$this->_action_fn = str_replace("_adm_", "_", $this->_action_fn);
		$this->_template = str_replace("adm-", "", $this->_template);

		$this->addCSS(Utils::fullURL("css/admin/admin.css"));
	}

	function call() {
		if ($this->_session->requireLogin())
			exit();

		parent::call();
	}

	protected function denyAccess() {
		$this->_template = "";
		throw new Exception(_T("Brak uprawnień."));
	}

	protected function finishAction($msg) {
		$this->addMessage($msg);
		if (!empty($this->_ref)) {
			header('Location: ' . $this->_ref);
			die();
		}
	}

	protected function _admin() {
	}

	protected function _sitecfg() {

		if (!$this->session()->checkPerm("site-config"))
			$this->denyAccess();

		$site_url = Utils::p("site_url", Config::get("site-url"));
		$site_title = Utils::p("site_title", Config::get("site-title"));
		$cookie_domain = Utils::p("cookie_domain", Config::get("cookie-domain"));
		$cookie_name = Utils::p("cookie_name", Config::get("cookie-name", "phphoto"));
		$cookie_path = Utils::p("cookie_path", Config::get("cookie-path", "/"));
		$session_cookie_name = Utils::p("session_cookie_name", Config::get("session-cookie-name", "sid"));
		$session_lifetime = Utils::p("session_lifetime", Config::get("session-lifetime", 3600));
		$email_from = Utils::p("email_from", Config::get("email-from"));
		$email_user = Utils::p("email_user", Config::get("email-user"));
		$email_signature = Utils::p("email_signature", Config::get("email-signature"));
		$debug_trace = Utils::p("debug_trace", Config::get("debug-trace", 0));
		$ajax_http_method = Utils::p("ajax_http_method", Config::get("ajax-http-method", 'POST'));
		$datetime_format = Utils::p("datetime_format", Config::get("datetime-format", "%Y-%m-%d %H:%M:%S"));
		$date_format = Utils::p("date_format", Config::get("date-format", "%Y-%m-%d"));
		$time_format = Utils::p("time_format", Config::get("time-format", "%H:%M:%S"));

		$this->setTemplateVar("frm_site_url", $site_url);
		$this->setTemplateVar("frm_site_title", $site_title);
		$this->setTemplateVar("frm_cookie_domain", $cookie_domain);
		$this->setTemplateVar("frm_cookie_name", $cookie_name);
		$this->setTemplateVar("frm_cookie_path", $cookie_path);
		$this->setTemplateVar("frm_session_cookie_name", $session_cookie_name);
		$this->setTemplateVar("frm_session_lifetime", $session_lifetime);
		$this->setTemplateVar("frm_email_user", $email_user);
		$this->setTemplateVar("frm_email_from", $email_from);
		$this->setTemplateVar("frm_email_signature", $email_signature);
		$this->setTemplateVar("frm_debug_trace", $debug_trace);
		$this->setTemplateVar("frm_ajax_http_method", $ajax_http_method);
		$this->setTemplateVar("frm_datetime_format", $datetime_format);
		$this->setTemplateVar("frm_time_format", $time_format);
		$this->setTemplateVar("frm_date_format", $date_format);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			Config::set("site-url", $site_url);
			Config::set("site-title", $site_title);
			Config::set("cookie-domain", $cookie_domain);
			Config::set("cookie-name", $cookie_name);
			Config::set("cookie-path", $cookie_path);
			Config::set("session-cookie-name", $session_cookie_name);
			Config::set("session-lifetime", $session_lifetime);
			Config::set("email-from", $email_from);
			Config::set("email-user", $email_user);
			Config::set("email-signature", $email_signature);
			Config::set("debug-trace", $debug_trace);
			Config::set("ajax-http-method", $ajax_http_method);
			Config::set("datetime-format", $datetime_format);
			Config::set("date-format", $date_format);
			Config::set("time-format", $time_format);

			$this->addMessage("Ustawienia zostały zapisane.");
		}
	}

	protected function _usercfg() {

		if (!$this->session()->checkPerm("users-and-groups-config"))
			$this->denyAccess();

		$allow_userlevel = $this->session()->isAdmin();
		$allow_grouplevel = $this->session()->isAdmin();

		$require_login = Utils::p("require_login", Config::get("require-login", 0));
		$enable_registration = Utils::p("enable_registration", Config::get("enable-registration", 1));
		$account_activation = Utils::p("account_activation", Config::get("account-activation", 1));
		$activation_message = Utils::p("activation_message", Config::get("activation-message"));
		$default_user_title = Utils::p("default_user_title", Config::get("default-user-title"));
		if ($allow_userlevel)
			$default_user_level = Utils::p("default_user_level", Config::get("default-user-level"));
		if ($allow_grouplevel)
			$default_group_level = Utils::p("default_group_level", Config::get("default-group-level"));

		$this->setTemplateVar("frm_require_login", $require_login);
		$this->setTemplateVar("frm_enable_registration", $enable_registration);
		$this->setTemplateVar("frm_account_activation", $account_activation);
		$this->setTemplateVar("frm_activation_message", $activation_message);
		$this->setTemplateVar("frm_default_user_title", $default_user_title);
		$this->setTemplateVar("allow_userlevel", $allow_userlevel);
		$this->setTemplateVar("allow_grouplevel", $allow_grouplevel);
		if ($allow_userlevel)
			$this->setTemplateVar("frm_default_user_level", $default_user_level);
		if ($allow_grouplevel)
			$this->setTemplateVar("frm_default_group_level", $default_group_level);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			Config::set("require-login", $require_login);
			Config::set("enable-registration", $enable_registration);
			Config::set("account-activation", $account_activation);
			Config::set("activation-message", $activation_message);
			Config::set("default-user-title", $default_user_title);
			if ($allow_userlevel)
				Config::set("default-user-level", $default_user_level);
			if ($allow_grouplevel)
				Config::set("default-group-level", $default_group_level);

			$this->addMessage("Ustawienia zostały zapisane.");
		}
	}

	protected function _users() {

		if (!$this->session()->checkPerm("users-list"))
			$this->denyAccess();

		$db = Database::singletone()->db();
		$sth = $db->query("SELECT COUNT(*) FROM phph_users");
		$users = $sth->fetchColumn(0);
		$sth = null;

		$sth = $db->prepare(
			"SELECT u.*, " .
			"(SELECT ui.ip FROM phph_user_ip ui WHERE ui.user_id = u.user_id ORDER BY ui.last_visit DESC LIMIT 0, 1) AS user_ip " .
			"FROM phph_users u ORDER BY user_id ASC LIMIT :p, :c");
		$sth->bindValue(":p", $this->startItem());
		$sth->bindValue(":c", $this->count());
		$sth->execute();
		$rows = $sth->fetchAll();
		$sth = null;

		foreach ($rows as &$row) {
			$row['allow_edit'] = ($this->session()->uid() == $row['user_id'] || $this->session()->checkPermAndLevel('edit-users', $row['user_id']));
			$row['allow_perms'] = ($this->session()->uid() != $row['user_id'] && $this->session()->checkPermAndLevel('change-users-permissions', $row['user_id']));
			$row['allow_delete'] = ($this->session()->uid() != $row['user_id'] && $this->session()->checkPermAndLevel('remove-users', $row['user_id']));
		}

		$pages = $this->pager($users);
		$this->setTemplateVar('pager', $pages);

		$this->setTemplateVar("users", $rows);
	}

	protected function _edit_user() {

		$user_id = Utils::pg('uid', 0);

		if (!empty($user_id)) {
			if ($this->session()->uid() != $user_id && !$this->session()->checkPermAndLevel("edit-users", $user_id))
				$this->denyAccess();
			$allow_userlevel = $this->session()->checkPermAndLevel('change-users-level', $user_id);
		} else {
			if (!$this->session()->checkPerm('create-users'))
				$this->denyAccess();
			$allow_userlevel = $this->session()->checkPerm('change-users-level');
		}

		$allow_superuser = $this->session()->isAdmin();

		$user = new User(Utils::pg("uid", 0));

		$user_login = Utils::p("user_login", $user->dbdata('user_login'));
		$user_name = Utils::p("user_name", $user->dbdata('user_name'));
		$user_email = Utils::p("user_email", $user->dbdata('user_email'));
		$user_jid = Utils::p("user_jid", $user->dbdata('user_jid'));
		$user_www = Utils::p("user_www", $user->dbdata('user_www'));
		$user_title = Utils::p("user_title", $user->dbdata('user_title', Config::get('default-user-title')));
		$user_from = Utils::p("user_from", $user->dbdata('user_from'));
		$user_pass1 = Utils::p("user_pass");
		$user_pass2 = Utils::p("user_pass2");
		if ($allow_userlevel)
			$user_level = Utils::p("user_level", $user->dbdata('user_level', Config::get('default-user-level')));
		if ($allow_superuser)
			$user_admin = Utils::p("user_admin", $user->dbdata('user_admin'));

		$this->setTemplateVar('user_id', $user_id);
		$this->setTemplateVar('frm_user_login', $user_login);
		$this->setTemplateVar('frm_user_name', $user_name);
		$this->setTemplateVar('frm_user_email', $user_email);
		$this->setTemplateVar('frm_user_jid', $user_jid);
		$this->setTemplateVar('frm_user_www', $user_www);
		$this->setTemplateVar('frm_user_title', $user_title);
		$this->setTemplateVar('frm_user_from', $user_from);
		$this->setTemplateVar('allow_userlevel', $allow_userlevel);
		$this->setTemplateVar('allow_superuser', $allow_superuser);
		if ($allow_userlevel)
			$this->setTemplateVar('frm_user_level', $user_level);
		if ($allow_superuser)
			$this->setTemplateVar('frm_user_admin', $user_admin);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user->setDBData('user_login', $user_login);
			$user->setDBData('user_pass1', $user_pass1);
			$user->setDBData('user_pass2', $user_pass2);
			$user->setDBData('user_name', $user_name);
			$user->setDBData('user_email', $user_email);
			$user->setDBData('user_jid', $user_jid);
			$user->setDBData('user_www', $user_www);
			$user->setDBData('user_title', $user_title);
			$user->setDBData('user_from', $user_from);
			if ($allow_userlevel) {
				if (!$this->session()->checkLevelVal($user_level))
					throw new Exception(_T("Brak uprawnień do ustawiania poziomu użytkownika tej wielkości."));
				$user->setDBData('user_level', $user_level);
			}
			if ($allow_superuser)
				$user->setDBData('user_admin', $user_admin);
			$user->save();
			$user_id = $user->uid();
			$this->setTemplateVar('user_id', $user_id);
			$this->finishAction(_T("Dane użytkownika zostały zapisane."));
		}

	}

	protected function _edit_perms() {

		$user_id = Utils::pg('uid');
		$group_id = Utils::pg('gid');

		if (!empty($user_id)) {
			if ($this->session()->uid() == $user_id || !$this->session()->checkPermAndLevel('change-users-permissions', $user_id))
				$this->denyAccess();
			$this->setTemplateVar('user_id', $user_id);
			$obj = new User($user_id);
			$this->setTemplateVar('obj_name', $obj->dbdata("user_login"));
		} else if (!empty($group_id)) {
			if (!$this->session()->checkPermAndLevelVal('change-groups-permissions', $group_id))
				$this->denyAccess();
			$this->setTemplateVar('group_id', $group_id);
			$obj = new Group($group_id);
			$this->setTemplateVar('obj_name', $obj->dbdata("group_name"));
		}

		$admin_panel = Utils::p('admin_panel', $obj->getPerm('admin-panel'));
		$site_config = Utils::p('site_config', $obj->getPerm('site-config'));
		$mass_message = Utils::p('mass_message', $obj->getPerm('mass-message'));
		$users_and_groups_config = Utils::p('users_and_groups_config', $obj->getPerm('users-and-groups-config'));
		$users_list = Utils::p('users_list', $obj->getPerm('users-list'));
		$create_users = Utils::p('create_users', $obj->getPerm('create-users'));
		$edit_users = Utils::p('edit_users', $obj->getPerm('edit-users'));
		$remove_users = Utils::p('remove_users', $obj->getPerm('remove-users'));
		$change_users_permissions = Utils::p('change_users_permissions', $obj->getPerm('change-users-permissions'));
		$change_users_level = Utils::p('change_users_level', $obj->getPerm('change-users-level'));
		$groups_list = Utils::p('groups_list', $obj->getPerm('groups-list'));
		$create_groups = Utils::p('create_groups', $obj->getPerm('create-groups'));
		$edit_groups = Utils::p('edit_groups', $obj->getPerm('edit-groups'));
		$remove_groups = Utils::p('remove_groups', $obj->getPerm('remove-groups'));
		$change_groups_permissions = Utils::p('change_groups_permissions', $obj->getPerm('change-groups-permissions'));
		$change_groups_level = Utils::p('change_groups_level', $obj->getPerm('change-groups-level'));
		$view_group_members = Utils::p('view_group_members', $obj->getPerm('view-group-members'));
		$add_group_members = Utils::p('add_group_members', $obj->getPerm('add-group-members'));
		$remove_group_members = Utils::p('remove_group_members', $obj->getPerm('remove-group-members'));
		$gallery_config = Utils::p('gallery_config', $obj->getPerm('gallery-config'));
		$categories_list = Utils::p('categories_list', $obj->getPerm('categories-list'));
		$create_categories = Utils::p('create_categories', $obj->getPerm('create-categories'));
		$edit_categories = Utils::p('edit_categories', $obj->getPerm('edit-categories'));
		$remove_categories = Utils::p('remove_categories', $obj->getPerm('remove-categories'));
		$approve_photos = Utils::p('approve_photos', $obj->getPerm('approve-photos'));


		$this->setTemplateVar('frm_admin_panel', $admin_panel);
		$this->setTemplateVar('frm_site_config', $site_config);
		$this->setTemplateVar('frm_mass_message', $mass_message);
		$this->setTemplateVar('frm_users_and_groups_config', $users_and_groups_config);
		$this->setTemplateVar('frm_users_list', $users_list);
		$this->setTemplateVar('frm_edit_users', $edit_users);
		$this->setTemplateVar('frm_create_users', $create_users);
		$this->setTemplateVar('frm_remove_users', $remove_users);
		$this->setTemplateVar('frm_change_users_permissions', $change_users_permissions);
		$this->setTemplateVar('frm_change_users_level', $change_users_level);
		$this->setTemplateVar('frm_groups_list', $groups_list);
		$this->setTemplateVar('frm_edit_groups', $edit_groups);
		$this->setTemplateVar('frm_create_groups', $create_groups);
		$this->setTemplateVar('frm_remove_groups', $remove_groups);
		$this->setTemplateVar('frm_change_groups_permissions', $change_groups_permissions);
		$this->setTemplateVar('frm_change_groups_level', $change_groups_level);
		$this->setTemplateVar('frm_add_group_members', $add_group_members);
		$this->setTemplateVar('frm_view_group_members', $view_group_members);
		$this->setTemplateVar('frm_remove_group_members', $remove_group_members);
		$this->setTemplateVar('frm_gallery_config', $gallery_config);
		$this->setTemplateVar('frm_categories_list', $categories_list);
		$this->setTemplateVar('frm_edit_categories', $edit_categories);
		$this->setTemplateVar('frm_create_categories', $create_categories);
		$this->setTemplateVar('frm_remove_categories', $remove_categories);
		$this->setTemplateVar('frm_approve_photos', $approve_photos);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$obj->setPerm('admin-panel', $admin_panel);
			$obj->setPerm('site-config', $site_config);
			$obj->setPerm('mass-message', $mass_message);
			$obj->setPerm('users-and-groups-config', $users_and_groups_config);
			$obj->setPerm('users-list', $users_list);
			$obj->setPerm('create-users', $create_users);
			$obj->setPerm('edit-users', $edit_users);
			$obj->setPerm('remove-users', $remove_users);
			$obj->setPerm('change-users-permissions', $change_users_permissions);
			$obj->setPerm('change-users-level', $change_users_level);
			$obj->setPerm('groups-list', $groups_list);
			$obj->setPerm('create-groups', $create_groups);
			$obj->setPerm('edit-groups', $edit_groups);
			$obj->setPerm('remove-groups', $remove_groups);
			$obj->setPerm('change-groups-permissions', $change_groups_permissions);
			$obj->setPerm('change-groups-level', $change_groups_level);
			$obj->setPerm('view-group-members', $view_group_members);
			$obj->setPerm('add-group-members', $add_group_members);
			$obj->setPerm('remove-group-members', $remove_group_members);
			$obj->setPerm('gallery-config', $gallery_config);
			$obj->setPerm('categories-list', $categories_list);
			$obj->setPerm('create-categories', $create_categories);
			$obj->setPerm('edit-categories', $edit_categories);
			$obj->setPerm('remove-categories', $remove_categories);
			$obj->setPerm('approve-photos', $approve_photos);

			$this->finishAction(_T("Uprawnienia zostały zmodyfikowane."));
		}
	}

	protected function _groups() {

		if (!$this->session()->checkPerm("groups-list"))
			$this->denyAccess();

		$this->addScript("js/functions.js");
		$this->addScript("js/behaviour.js");
		$this->addScript("js/advajax.js");
		$this->addScript("js/ajax.js");
		$this->addScript("js/admin/groups.js");

		$db = Database::singletone()->db();
		$sth = $db->query("SELECT COUNT(*) FROM phph_groups");
		$groups = $sth->fetchColumn(0);
		$sth = null;

		$sth = $db->prepare(
			"SELECT g.*, " .
			"(SELECT COUNT(*) FROM phph_group_members gm WHERE gm.group_id = g.group_id) AS user_count, " .
			"c.user_login AS creator_login ".
			"FROM phph_groups g ".
			"LEFT OUTER JOIN phph_users c ON g.group_creator = c.user_id ".
			"ORDER BY group_id ASC LIMIT :p, :c");
		$sth->bindValue(":p", $this->startItem());
		$sth->bindValue(":c", $this->count());
		$sth->execute();
		$rows = $sth->fetchAll();
		$sth = null;

		foreach ($rows as &$row) {
			$row['allow_edit'] = $this->session()->checkPermAndLevelVal('edit-groups', $row['group_level']);
			$row['allow_perms'] = $this->session()->checkPermAndLevelVal('change-groups-permissions', $row['group_level']);
			$row['allow_delete'] = $this->session()->checkPermAndLevelVal('remove-groups', $row['group_level']);
			$row['allow_members'] = ($this->session()->checkPermAndLevelVal('view-group-members', $row['group_level']) || $this->session()->user()->isMember($row['group_id']));
			if (!$row['allow_members'])
				$row['user_count'] = '-';
		}

		$pages = $this->pager($groups);
		$this->setTemplateVar('pager', $pages);

		$this->setTemplateVar("groups", $rows);
	}

	protected function _edit_group() {

		$group_id = Utils::pg('gid', 0);

		if (!empty($group_id)) {
			if (!$this->session()->checkPermAndLevelVal("edit-groups", Group::getGroupLevel($group_id)))
				$this->denyAccess();
			$allow_grouplevel = $this->session()->checkPermAndLevelVal('change-groups-level', Group::getGroupLevel($group_id));
		} else {
			if (!$this->session()->checkPerm('create-groups'))
				$this->denyAccess();
			$allow_grouplevel = $this->session()->checkPerm('change-groups-level');
		}

		$group = new Group($group_id);

		$group_name = Utils::p("group_name", $group->dbdata('group_name'));
		$group_description = Utils::p("group_description", $group->dbdata('group_description'));
		if ($allow_grouplevel)
			$group_level = Utils::p("group_level", $group->dbdata('group_level', Config::get('default-group-level')));

		$this->setTemplateVar('group_id', $group_id);
		$this->setTemplateVar('frm_group_name', $group_name);
		$this->setTemplateVar('frm_group_description', $group_description);
		$this->setTemplateVar('allow_grouplevel', $allow_grouplevel);
		if ($allow_grouplevel)
			$this->setTemplateVar('frm_group_level', $group_level);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$group->setDBData('group_name', $group_name);
			$group->setDBData('group_description', $group_description);
			if ($allow_grouplevel) {
				if (!$this->session()->checkLevelVal($group_level))
					throw new Exception(_T("Brak uprawnień do ustawiania poziomu grupy tej wielkości."));
				$group->setDBData('group_level', $group_level);
			}
			$group->save();
			$group_id = $group->gid();
			$this->setTemplateVar('group_id', $group_id);
			$this->finishAction(_T("Dane grupy zostały zapisane."));
		}

	}

	protected function _group_members() {

		if (!$this->session()->checkPerm("groups-list"))
			$this->denyAccess();

		$db = Database::singletone()->db();
		$sth = $db->query("SELECT COUNT(*) FROM phph_groups");
		$groups = $sth->fetchColumn(0);
		$sth = null;

		$sth = $db->prepare(
			"SELECT g.*, " .
			"(SELECT COUNT(*) FROM phph_group_members gm WHERE gm.group_id = g.group_id) AS user_count, " .
			"c.user_login AS creator_login ".
			"FROM phph_groups g ".
			"LEFT OUTER JOIN phph_users c ON g.group_creator = c.user_id ".
			"ORDER BY group_id ASC LIMIT :p, :c");
		$sth->bindValue(":p", $this->startItem());
		$sth->bindValue(":c", $this->count());
		$sth->execute();
		$rows = $sth->fetchAll();
		$sth = null;

		foreach ($rows as &$row) {
			$row['allow_edit'] = $this->session()->checkPermAndLevelVal('edit-groups', $row['group_level']);
			$row['allow_perms'] = $this->session()->checkPermAndLevelVal('change-groups-permissions', $row['group_level']);
			$row['allow_delete'] = $this->session()->checkPermAndLevelVal('remove-groups', $row['group_level']);
			$row['allow_members'] = ($this->session()->checkPermAndLevelVal('view-group-members', $row['group_level']) || $this->session()->user()->isMember($row['group_id']));
			if (!$row['allow_members'])
				$row['user_count'] = '-';
		}

		$pages = $this->pager($groups);
		$this->setTemplateVar('pager', $pages);

		$this->setTemplateVar("groups", $rows);
	}

	protected function _galcfg() {

		if (!$this->session()->checkPerm("gallery-config"))
			$this->denyAccess();

		$max_file_size = Utils::p("max_file_size", Config::get("max-file-size", 150));
		$max_width = Utils::p("max_width", Config::get("max-width", 640));
		$max_height = Utils::p("max_height", Config::get("max-height", 600));
		$auto_approve = Utils::p("auto_approve", Config::get("auto-approve", 0));
		$moderator_note = Utils::p("moderator_note", Config::get("default-moderator-note", ''));
		$cache_lifetime = Utils::p("cache_lifetime", Config::get("cache-lifetime", 7));
		$send_approve_notify = Utils::p("send_approve_notify", Config::get("send-approve-notify", 0));
		$approve_notify = Utils::p("approve_notify", Config::get("approve-notify", ''));
		$send_reject_notify = Utils::p("send_reject_notify", Config::get("send-reject-notify", 0));
		$reject_notify = Utils::p("reject_notify", Config::get("reject-notify", ''));

		$this->setTemplateVar("frm_max_file_size", $max_file_size);
		$this->setTemplateVar("frm_max_width", $max_width);
		$this->setTemplateVar("frm_max_height", $max_height);
		$this->setTemplateVar("frm_auto_approve", $auto_approve);
		$this->setTemplateVar("frm_moderator_note", $moderator_note);
		$this->setTemplateVar("frm_cache_lifetime", $cache_lifetime);
		$this->setTemplateVar("frm_send_approve_notify", $send_approve_notify);
		$this->setTemplateVar("frm_approve_notify", $approve_notify);
		$this->setTemplateVar("frm_send_reject_notify", $send_reject_notify);
		$this->setTemplateVar("frm_reject_notify", $reject_notify);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			Config::set("max-file-size", $max_file_size);
			Config::set("max-width", $max_width);
			Config::set("max-height", $max_height);
			Config::set("auto-approve", $auto_approve);
			Config::set("default-moderator-note", $moderator_note);
			Config::set("cache-lifetime", $cache_lifetime);
			Config::set("send-approve-notify", $send_approve_notify);
			Config::set("approve-notify", $approve_notify);
			Config::set("send-reject-notify", $send_reject_notify);
			Config::set("reject-notify", $reject_notify);

			$this->addMessage("Ustawienia zostały zapisane.");
		}
	}

	protected function _categories() {

		if (!$this->session()->checkPerm("categories-list"))
			$this->denyAccess();

		$this->addScript("js/functions.js");
		$this->addScript("js/behaviour.js");
		$this->addScript("js/advajax.js");
		$this->addScript("js/ajax.js");
		$this->addScript("js/domLib.js");
		$this->addScript("js/domTT.js");
		$this->addScript("js/admin/ctree.js");
		$this->addScript("js/admin/categories.js");

		$this->addCSS('css/admin/tabs.css');
		$this->addCSS('css/admin/preview.css');
		$this->addCSS('css/admin/ctree.css');

		$db = Database::singletone()->db();
	}

	protected function _edit_category() {

		$category_id = Utils::pg('cid', 0);

		if (!empty($category_id)) {
			if (!$this->session()->checkPerm('edit-categories'))
				$this->denyAccess();
		} else {
			if (!$this->session()->checkPerm('create-categories'))
				$this->denyAccess();
		}

		$this->addScript("js/functions.js");
		$this->addScript("js/behaviour.js");
		$this->addScript("js/advajax.js");
		$this->addScript("js/ajax.js");
		$this->addScript("js/admin/ctree.js");
		$this->addScript("js/admin/edit-category.js");

		$this->addCSS('css/admin/ctree.css');

		$category = new Category($category_id);

		$category_name = Utils::p("category_name", $category->dbdata('category_name'));
		$category_description = Utils::p("category_description", $category->dbdata('category_description'));
		$category_parent = Utils::p("category_parent", $category->dbdata('category_parent', null));

		$this->setTemplateVar('category_id', $category_id);
		$this->setTemplateVar('frm_category_name', $category_name);
		$this->setTemplateVar('frm_category_description', $category_description);
		$this->setTemplateVar('frm_category_parent', $category_parent);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$category->setDBData('category_name', $category_name);
			$category->setDBData('category_description', $category_description);
			$category->setDBData('category_parent', $category_parent);
			$category->save();
			$category_id = $category->cid();
			$this->setTemplateVar('category_id', $category_id);
			$this->finishAction(_T("Kategoria została zapisana."));
		}
	}

	private function getPhotosWhereSql($addwhere = true) {

		$uid = Utils::pg('uid', 0);
		$cid = Utils::pg('cid', 0);
		$scid = Utils::pg('scid', 0);
		$approved = Utils::pg('approved', '');
		$rejected = Utils::pg('rejected', '');
		$waiting = Utils::pg('waiting', '');
		$user_login = Utils::pg('user_login', '');

		if (!empty($user_login)) {
			$db = Database::singletone()->db();
			$sth = $db->prepare('SELECT user_id FROM phph_users WHERE user_login = :login');
			$sth->bindParam(':login', $user_login);
			$sth->execute();
			$row = $sth->fetch();
			if ($row) {
				$uid = $row['user_id'];
			}
		}

		$sql = '';

		if (!is_numeric($uid))
			$uid = 0;
		if (!is_numeric($cid))
			$cid = 0;

		if (!empty($approved) || !empty($rejected) || !empty($waiting)) {
			$ssql = ' (0=1 ';
			if (!empty($approved))
				$ssql .= " OR pm.moderation_mode = 'approve'";
			if (!empty($rejected))
				$ssql .= " OR pm.moderation_mode = 'reject'";
			if (!empty($waiting))
				$ssql .= " OR p.moderation_id IS NULL";
			$ssql .= ') ';
			$sql .= (empty($sql) ? "" : " AND ") . $ssql;
		}

		if ($uid > 0)
			$sql .= (empty($sql) ? "" : " AND ") . " p.user_id = $uid";
		if ($cid > 0) {
			if (!empty($scid)) {
				$cids = array();
				$cids = Category::getSubCategoriesCIDs($cid, true);
				$cids[] = $cid;
				$scids = implode(', ', $cids);
				$sql .= (empty($sql) ? "" : " AND ") .
					"p.photo_id IN (SELECT c.photo_id FROM phph_photos_categories c WHERE c.category_id IN ($scids))";
			} else {
				$sql .= (empty($sql) ? "" : " AND ") .
					"p.photo_id IN (SELECT c.photo_id FROM phph_photos_categories c WHERE c.category_id = $cid)";
			}
		}
		if ($addwhere) {
			if (empty($sql))
				$sql = 'WHERE 1=1 ';
			else
				$sql = 'WHERE ' . $sql;
		}
		return $sql;
	}

	protected function _photos() {

		if (!$this->session()->checkPerm("photos-list"))
			$this->denyAccess();

		$this->addScript("js/functions.js");
		$this->addScript("js/behaviour.js");
		$this->addScript("js/advajax.js");
		$this->addScript("js/ajax.js");
		$this->addScript("js/tabs.js");
		$this->addScript("js/ac.js");
		$this->addScript("js/admin/ctree.js");
		$this->addScript("js/admin/photos.js");

		$this->addCSS('css/admin/ctree.css');
		$this->addCSS('css/admin/tabs.css');
		$this->addCSS('css/admin/ac.css');
		$this->addCSS('css/admin/preview.css');
		$this->addCSS('css/admin/photos.css');

		$db = Database::singletone()->db();

		$this->setDefaultCount(16);

		$wq = $this->getPhotosWhereSql();

		$sth = $db->query("SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id " . $wq);
		$photos = $sth->fetchColumn(0);
		$sth = null;

		$sth = $db->prepare('SELECT p.photo_id FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id ' . $wq . ' ORDER BY photo_added DESC LIMIT :p, :c');
		$sth->bindValue(":p", $this->startItem());
		$sth->bindValue(":c", $this->count());
		$sth->execute();
		$rows = $sth->fetchAll();
		$sth = null;

		$aphotos = array();
		foreach ($rows as &$row) {
			$photo = new Photo($row['photo_id']);
			$ph = $photo->fullData();
			$ph['file'] = $photo->get(100, 100);
			$aphotos[] = $ph;
		}
		$this->setTemplateVar('photos', $aphotos);

		$pages = $this->pager($photos);
		$this->setTemplateVar('pager', $pages);

		$stats = array();

		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p');
		$stats['total']['total'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id ' . $wq);
		$stats['selected']['total'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id '.
				  "WHERE pm.moderation_mode = 'approve'");
		$stats['total']['approved'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id ' .
				  $wq .
			  	  " AND pm.moderation_mode = 'approve'");
		$stats['selected']['approved'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id '.
				  "WHERE pm.moderation_mode = 'reject'");
		$stats['total']['rejected'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id ' .
				  $wq .
			  	  " AND pm.moderation_mode = 'reject'");
		$stats['selected']['rejected'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p WHERE p.moderation_id IS NULL');
		$stats['total']['waiting'] = $sth->fetchColumn(0);
		$sth = null;
		$sth = $db->query('SELECT COUNT(*) FROM phph_photos p LEFT OUTER JOIN phph_photos_moderation pm ON p.moderation_id = pm.moderation_id ' . $wq . ' AND p.moderation_id IS NULL');
		$stats['selected']['waiting'] = $sth->fetchColumn(0);
		$sth = null;

		//$sth = $db

		$this->setTemplateVar('stats', $stats);

		//$this->setTemplateVar("users", $rows);

	}

}

?>
