<?php
// $Id$

require_once("includes/Session.php");
require_once("includes/PhphSmarty.php");
require_once("includes/Engine.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");


class Admin extends Engine {

	function __construct($action) {
		$this->_supported = array("admin", "adm-sitecfg", "adm-usercfg", "adm-users");
		parent::__construct($action);
		if (!$this->session()->checkPerm("admin_panel"))
			$this->_valid = false;
		if ($this->valid()) {
			$this->_main_template = "admin/index.tpl";
			$this->smarty()->assign("admin_panel", 1);
		}

		$this->_action_fn = str_replace("_adm-", "_", $this->_action_fn);
		$this->_template = str_replace("adm-", "", $this->_template);
	}

	function call() {
		if ($this->_session->requireLogin())
			exit();

		parent::call();
	}

	protected function _admin() {
	}

	protected function _sitecfg() {
		$site_url = Utils::p("site_url", Config::get("site_url"));
		$site_title = Utils::p("site_title", Config::get("site_title"));
		$cookie_domain = Utils::p("cookie_domain", Config::get("cookie_domain"));
		$cookie_name = Utils::p("cookie_name", Config::get("cookie_name", "phphoto"));
		$cookie_path = Utils::p("cookie_path", Config::get("cookie_path", "/"));
		$session_cookie_name = Utils::p("session_cookie_name", Config::get("session_cookie_name", "sid"));
		$session_lifetime = Utils::p("session_lifetime", Config::get("session_lifetime", 3600));
		$email_from = Utils::p("email_from", Config::get("email_from"));
		$email_user = Utils::p("email_user", Config::get("email_user"));
		$debug_trace = Utils::p("debug_trace", Config::get("debug_trace", 0));
		$datetime_format = Utils::p("datetime_format", Config::get("datetime_format", "%Y-%m-%d %H:%M:%S"));
		$date_format = Utils::p("date_format", Config::get("date_format", "%Y-%m-%d"));
		$time_format = Utils::p("time_format", Config::get("time_format", "%H:%M:%S"));

		$this->setTemplateVar("frm_site_url", $site_url);
		$this->setTemplateVar("frm_site_title", $site_title);
		$this->setTemplateVar("frm_cookie_domain", $cookie_domain);
		$this->setTemplateVar("frm_cookie_name", $cookie_name);
		$this->setTemplateVar("frm_cookie_path", $cookie_path);
		$this->setTemplateVar("frm_session_cookie_name", $session_cookie_name);
		$this->setTemplateVar("frm_session_lifetime", $session_lifetime);
		$this->setTemplateVar("frm_email_user", $email_user);
		$this->setTemplateVar("frm_email_from", $email_from);
		$this->setTemplateVar("frm_debug_trace", $debug_trace);
		$this->setTemplateVar("frm_datetime_format", $datetime_format);
		$this->setTemplateVar("frm_time_format", $time_format);
		$this->setTemplateVar("frm_date_format", $date_format);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			Config::set("site_url", $site_url);
			Config::set("site_title", $site_title);
			Config::set("cookie_domain", $cookie_domain);
			Config::set("cookie_name", $cookie_name);
			Config::set("cookie_path", $cookie_path);
			Config::set("session_cookie_name", $session_cookie_name);
			Config::set("session_lifetime", $session_lifetime);
			Config::set("email_from", $email_from);
			Config::set("email_user", $email_user);
			Config::set("debug_trace", $debug_trace);
			Config::set("datetime_format", $datetime_format);
			Config::set("date_format", $date_format);
			Config::set("time_format", $time_format);

			$this->addMessage("Ustawienia zostały zapisane.");
		}
	}

	protected function _usercfg() {
		$require_login = Utils::p("require_login", Config::get("require_login", 0));
		$enable_registration = Utils::p("enable_registration", Config::get("enable_registration", 1));
		$account_activation = Utils::p("account_activation", Config::get("account_activation", 1));
		$activation_message = Utils::p("activation_message", Config::get("activation_message"));

		$this->setTemplateVar("frm_require_login", $require_login);
		$this->setTemplateVar("frm_enable_registration", $enable_registration);
		$this->setTemplateVar("frm_account_activation", $account_activation);
		$this->setTemplateVar("frm_activation_message", $activation_message);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			Config::set("require_login", $require_login);
			Config::set("enable_registration", $enable_registration);
			Config::set("account_activation", $account_activation);
			Config::set("activation_message", $activation_message);

			$this->addMessage("Ustawienia zostały zapisane.");
		}
	}

	protected function _users() {

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
			$row['allow_edit'] = ($this->session()->uid() == $row['user_id'] || $this->session()->checkPermAndLevel('edit_users', $row['user_id']));
			$row['allow_perms'] = ($this->session()->uid() != $row['user_id'] && $this->session()->checkPermAndLevel('change_users_permissions', $row['user_id']));
			$row['allow_delete'] = ($this->session()->uid() != $row['user_id'] && $this->session()->checkPermAndLevel('delete_users', $row['user_id']));
		}

		$pages = $this->pager($this->url('adm-users'), $users);
		$this->setTemplateVar('pager', $pages);

		$this->setTemplateVar("users", $rows);
	}
}

?>
