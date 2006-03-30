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

	function __construct($action) {
	
		if (!$this->supported($action)) {
			$this->_valid = false;
			return;
		}

		$this->_url = Config::get("site_url");
		$this->_ref = Utils::secureHeaderData(Utils::pg("ref"));
		$this->_page = Utils::pg("p", 0);
		$this->_count = Utils::pg("c", 20);
		$this->_action = $action;
		
		$this->_session = Session::singletone();
		
		$this->_smarty = new PhphSmarty($this->_action);
		$this->_smarty->assign('ref', $this->_ref);
		$this->_smarty->assign('self', Utils::selfURL());
		$this->_smarty->assign('action', $this->_action);
		$this->_smarty->assign('page', $this->_page);
		$this->_smarty->assign('count', $this->_count);
		
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
	
	function valid() {
		return $this->_valid;
	}

	function supported($action) {
		return array_search($action, $this->_supported) !== FALSE;
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
			$this->_smarty->assign('error', 1);
			$this->_smarty->assign('error_title', $e->getMessage());
		}
		
		$this->_smarty->assign("template", $this->_template);
		$this->_smarty->assign("base_service_url", $this->url("service", array(), "service.php"));

	}

	function output() {
		Utils::negotiateContentType();
		ob_start();
		$this->_smarty->display("index.tpl");
		ob_flush();
	}
	
	function baseURL() {
		return $this->_url;
	}
}

function smarty_url($params, &$smarty) {
	return Utils::url($params['action']);
}

function smarty_full_url($params, &$smarty) {
	return Config::get("site_url") . $params['path'];
}
