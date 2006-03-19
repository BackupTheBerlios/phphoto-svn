<?php
// $Id$

require_once('Smarty/Smarty.class.php');
require_once('config.php');

class Phph_Smarty extends Smarty {

	function __construct($action) {

		$this->Smarty();
		$this->template_dir = dirname(__FILE__) . "/../smarty/templates";
		$this->compile_dir = dirname(__FILE__) . "/../smarty/templates_c";
		$this->config_dir = dirname(__FILE__) . "/../smarty/configs";
		$this->cache_dir = dirname(__FILE__) . "/../smarty/cache";
		$this->caching = false;

		$this->assign('app_name', Config::get('site_title'));
		$this->assign('action', $action);
	}
}

?>
