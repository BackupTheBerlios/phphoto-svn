<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Session.php");

class Config {

	private static $_cache = array();
	private static $_user_cache = array();

	static function getStatic($name, $def = "") {

		if (array_key_exists($name, self::$_cache)) {
			return self::$_cache[$name];
		} else {
			$db = Database::singletone()->db();

			$sth = $db->prepare("SELECT config_value FROM phph_config WHERE config_name = :config_name");
			$sth->bindParam(":config_name", $name, PDO::PARAM_STR);
			$sth->execute();
			if (!$row = $sth->fetch())
				return $def;

			self::$_cache[$name] = $row['config_value'];
			return $row['config_value'];
		}
	}

	static function get($name, $def = "") {
		return Config::getStatic($name, $def);
	}

	static function getUser($uid, $name, $def = "", $glob = true) {

		if ($uid > 0) {
			if (array_key_exists($uid, self::$_user_cache) && array_key_exists($name, self::$_user_cache[$uid])) {
				return self::$_user_cache[$uid][$name];
			} else {
				$db = Database::singletone()->db();
				$sth = $db->prepare("SELECT COUNT(*) FROM phph_user_settings WHERE user_id = :user_id AND setting_name = :setting_name");
				$sth->bindParam(":user_id", $uid);
				$sth->bindParam(":setting_name", $name);
				$sth->execute();
				$r = $sth->fetchColumn(0);
				$sth = null;
				if ($r > 0) {
					$sth = $db->prepare("SELECT setting_value FROM phph_user_settings WHERE user_id = :user_id AND setting_name = :setting_name");
					$sth->bindParam(":user_id", $uid);
					$sth->bindParam(":setting_name", $name);
					$sth->execute();
					$val = $sth->fetchColumn(0);
					self::$_user_cache[$uid][$name] = $val;
					return $val;
				}
			}
		}
		if ($glob)
			return self::get($name, $def);
		else
			return $def;
	}

	static function set($name, $val) {
		$db = Database::singletone()->db();
		$sth = $db->prepare("SELECT COUNT(*) AS cnt FROM phph_config WHERE config_name = :config_name");
		$sth->bindParam(":config_name", $name, PDO::PARAM_STR);
		$sth->execute();
		$r = $sth->fetchColumn(0);
		$sth = null;
		if ($r == 0) {
			$sth = $db->prepare("INSERT INTO phph_config (config_value, config_name) VALUES (:config_value, :config_name)");
		} else {
			$sth = $db->prepare("UPDATE phph_config SET config_value = :config_value WHERE config_name = :config_name");
		}
		$sth->bindParam(":config_name", $name, PDO::PARAM_STR);
		$sth->bindParam(":config_value", $val, PDO::PARAM_STR);
		$sth->execute();
		self::$_cache[$name] = $val;
	}

	static function setUser($uid, $name, $val) {

		$db = Database::singletone()->db();

		if ($uid > 0) {
			$sth = $db->prepare("SELECT COUNT(*) FROM phph_user_settings WHERE user_id = :user_id AND setting_name = :setting_name");
			$sth->bindParam(":user_id", $uid);
			$sth->bindParam(":setting_name", $name);
			$sth->execute();
			$r = $sth->fetchColumn(0);
			$sth = null;

			if ($r > 0)
				$sth = $db->prepare("UPDATE phph_user_settings SET setting_value = :setting_value WHERE setting_name = :setting_name AND user_id = :user_id");
			else
				$sth = $db->prepare("INSERT INTO phph_user_settings (user_id. setting_name, setting_value) VALUES (:user_id, :setting_name, :setting_value)");
			$sth->bindParam(":user_id", $uid);
			$sth->bindParam(":setting_name", $name);
			$sth->bindParam(":setting_value", $val);
			$sth->execute();
		}
	}
};

?>
