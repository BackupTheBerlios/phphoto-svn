<?php
// $Id$

require_once("config/config.php");


class Database {
	
	private static $_database = null;
	private $_db = null;

	private function __construct() {

		global $cfg_db_dsn, $cfg_db_user, $cfg_db_pass;

		$this->_db = new PDO($cfg_db_dsn, $cfg_db_user, $cfg_db_pass);
		$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public static function create() {
		if (self::$_database == null)
			self::$_database = new Database();
	}

	public static function singletone() {
		self::create();
		return self::$_database;
	}

	public function db() {
		return $this->_db;
	}
}

?>
