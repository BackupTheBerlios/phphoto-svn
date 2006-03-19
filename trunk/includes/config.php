<?php
// $Id$

require_once("DB/DataObject.php");
require_once("includes/db.php");

class Config {

	var $_config;

	function Config() {
		$this->_config = DB_DataObject::Factory('phph_config');
		if (PEAR::isError($this->_config)) {
			die($this->_config->getMessage());
		}
	}

	static function getStatic($name, $def = "") {

		$config = DB_DataObject::Factory('phph_config');
		if (PEAR::isError($config)) {
			die($config->getMessage());
		}

		$r = $config->get($name);
		if (PEAR::isError($r))
			return $def;
		if ($r == 0)
			return $def;
		return $config->config_value;
	}

	static function get($name, $def = "") {
		return Config::getStatic($name, $def);
	}

	static function set($name, $val) {

		$config = DB_DataObject::Factory('phph_config');
		if (PEAR::isError($config)) {
			return $config;
		}

		$r = $config->get($name);
		if (PEAR::isError($r))
			return $config;
		$config->config_value = $val;
		if ($r == 0) {
			return $config->insert();
		} else {
			return $config->update();
		}
	}
};

?>
