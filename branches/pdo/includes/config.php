<?php
// $Id$

require_once("includes/db.php");

class Config {

	static function getStatic($name, $def = "") {
		$db = Database::singletone()->db();
		
		$sth = $db->prepare("SELECT config_value FROM phph_config WHERE config_name = :config_name");
		$sth->bindParam(":config_name", $name, PDO::PARAM_STR);
		$sth->execute();
		if (!$row = $sth->fetch())
			return $def;
		return $row['config_value'];
	}

	static function get($name, $def = "") {
		return Config::getStatic($name, $def);
	}

	static function set($name, $val) {
		$db = Database::singletone()->db();
		$sth = $db->prepare("SELECT config_value FROM phph_config WHERE config_name = :config_name");
		$sth->bindParam(":config_name", $name, PDO::PARAM_STR);
		$sth->execute();
		if (!$sth->fetch()) {
			$sth = $db->prepare("INSERT INTO phph_config (config_value, config_name) VALUES (:config_value, :config_name)");
		} else {
			$q = $db->prepare("UPDATE phph_config SET config_value = :config_value, config_name = :config_name");
		}
		$sth->bindParam(":config_name", $name, PDO::PARAM_STR);
		$sth->bindParam(":config_value", $val, PDO::PARAM_STR);
		$sth->execute();
	}
};

?>
