<?php
// $Id$

require_once("includes/Utils.php");
require_once("includes/Session.php");
require_once("includes/PhphSmarty.php");

class Engine {

	protected $_url;
	protected $_ref;
	protected $_page;
	protected $_count;
	protected $_action;
	protected $_session;
	protected $_smarty;
	protected $_template;
	protected $_action_fn;
	protected $_templates = array();
	protected $_actions = array();
	protected $_db;
	protected $_supported = array();
	protected $_valid = false;
	protected $_status_code = 200;
	protected $_main_template;
	protected $_template_vars = array();
	static $_time_start = 0;

	function __construct($action) {

		if (!$this->supported($action)) {
			$this->_valid = false;
			$this->_status_code = 404;
			return;
		}

		$this->_template_vars = array();

		$this->_url = Config::get("site_url");
		//$this->_ref = Utils::secureHeaderData($_SERVER['HTTP_REFERER']);
		$this->_ref = Utils::secureHeaderData(Utils::pg("ref"));
		$this->_page = Utils::pg("p", 0);
		$this->_count = Utils::pg("c", 20);
		$this->_action = $action;
		$this->_main_template = "index.tpl";

		$this->_session = Session::singletone();

		$this->_smarty = new PhphSmarty($this->_action);

		$this->setTemplateVar('ref', $this->_ref);
		$this->setTemplateVar('base_url', $this->_url);
		$this->setTemplateVar('ajax_http_method', Config::get('ajax-http-method', 'POST'));
		$this->setTemplateVar('self', Utils::selfURL());
		$this->setTemplateVar('action', $this->_action);
		$this->setTemplateVar('page', $this->_page);
		$this->setTemplateVar('count', $this->_count);
		$this->setTemplateVar('is_superuser', $this->session()->isAdmin());
		$this->_templates = array();	// action => template, default: action => action.tpl
		$this->_actions = array();	// action => function, default: action => $this->_action()

		$this->_db = Database::singletone()->db();

		if (!empty($this->_templates[$this->_action]))
			$this->_template = $this->_templates[$this->_action];
		else
			$this->_template = $this->_action . ".tpl";

		if (!empty($this->_actions[$this->_action]))
			$this->_action_fn = $this->_actions[$this->_action];
		else
			$this->_action_fn = '$this->_' . $this->_action;

		$this->_smarty->register_function('url', 'smarty_url');
		$this->_smarty->register_function('full_url', 'smarty_full_url');
		$this->_smarty->register_function('decode_ip', 'smarty_decode_ip');

		$this->_valid = true;
/*
		$this->_templates = array(
			'index' => 'index.tpl',
			'view' => 'view.tpl',
			'categories' => 'categories.tpl',
			'category' => 'category.tpl',
			'user' => 'user.tpl',
			'register' => 'register-form.tpl',
			'registered' => 'registered.tpl',
			'reg-disabled' => 'reg-disabled.tpl',
			'activate' => 'activation.tpl',
			'login' => 'login.tpl'
		);
*/
	}

	function setTemplateVar($name, $val) {
		$this->_template_vars[$name] = $val;
	}

	function addMessage($body, $title = "", $class = "normal", $trace_str = null, $trace = null) {
		$msg = array();
		$msg['body'] = $body;
		$msg['title'] = $title;
		$msg['class'] = $class;
		if (!empty($trace_str) && Config::get("debug_trace", 0)) {
			$msg['trace_available'] = 1;
			$msg['trace_str'] = $trace_str;
			$msg['trace'] = $trace;

		}
		$_SESSION['messages'][] = $msg;
	}

	function valid() {
		return $this->_valid;
	}

	function statusCode() {
		return $this->_status_code;
	}

	function supported($action) {
		return array_search($action, $this->_supported) !== FALSE;
	}

	function smarty() {
		return $this->_smarty;
	}

	function session() {
		return $this->_session;
	}

	function page() {
		return intval($this->_page);
	}

	function startItem() {
		return $this->page() * $this->count();
	}

	function count() {
		return intval($this->_count);
	}

	function url($action, $attrs = array(), $script = "index.php") {
		return Utils::url($action, $attrs, $script);
	}


	function pager($url, $total) {
		$n_pages = ceil($total / $this->_count);

		$pages = array();

		for ($i = 0; $i < $n_pages; $i++) {
			$pages[] = array(
				'index' => $i,
				'page' => $i + 1,
				'url' => $url . "&amp;p=$i&amp;c=$this->_count",
				'current' => $this->_page == $i
			);
		}

		return $pages;
	}

	function call() {

		try {

			if (!empty($this->_action_fn))
				eval($this->_action_fn . "();");

		} catch (Exception $e) {
			$this->addMessage($e->getMessage(), "", "error", $e->getTraceAsString(), $e->getTrace());
		}

		$this->setTemplateVar("template", $this->_template);
		$this->setTemplateVar("base_service_url", $this->url("service", array(), "service.php"));

	}

	function output($time_start) {
		Utils::negotiateContentType();
		if (ereg("MSIE", $_SERVER['HTTP_USER_AGENT'])) {
			$this->setTemplateVar("is_ie", 1);
		}

		if ($this->_session->logged()) {
			$this->setTemplateVar("logged_in", 1);
			$this->setTemplateVar("logged_user_login", $this->_session->getUser()->dbdata("user_login"));
			$this->setTemplateVar("logged_user_name", $this->_session->getUser()->dbdata("user_name"));
		} else {
			$this->setTemplateVar("logged_in", 0);
		}

		$this->setTemplateVar("datetime_format", $this->session()->getUserSetting("datetime_format", "%Y-%m-%d %H:%M:%S"));
		$this->setTemplateVar("time_format", $this->session()->getUserSetting("time_format", "%H:%M:%S"));
		$this->setTemplateVar("date_format", $this->session()->getUserSetting("date_format", "%Y-%m-%d"));
		$this->setTemplateVar('queries', Database::singletone()->db()->count());
		foreach($this->_template_vars as $key => $val)
			$this->smarty()->assign($key, $val);

		$this->smarty()->assign("time_generated", sprintf("%.3f", microtime(true) - $time_start));
		if (isset($_SESSION['messages'])) {
			$this->smarty()->assign("messages", $_SESSION['messages']);
			$this->smarty()->assign("messages_count", count($_SESSION['messages']));
		} else {
			$this->smarty()->assign("messages_count", 0);
		}
		self::$_time_start = $time_start;
		ob_start('ob_statistics');
		$this->_smarty->display($this->_main_template);
		ob_flush();
		$_SESSION['messages'] = array();
	}

	function baseURL() {
		return $this->_url;
	}
}

function smarty_url($params, &$smarty) {
	$action = $params['action'];
	unset($params['action']);
	return Utils::url($action, $params);
}

function smarty_full_url($params, &$smarty) {
	return Config::get("site_url") . $params['path'];
}

function smarty_decode_ip($params, &$smarty) {
	return Utils::decodeIP($params['ip']);
}

function ob_statistics($content) {
	$content = str_replace('<queries>', Database::singletone()->db()->count(), $content);
	$content = str_replace('<time_generated>', sprintf("%.3f", microtime(true) - Engine::$_time_start), $content);
	return $content;
}
